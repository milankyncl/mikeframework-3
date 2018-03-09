<?php


namespace Postmix\Structure\Mvc;


use Postmix\Application;
use Postmix\Database\AdapterInterface;
use Postmix\Database\QueryBuilder;
use Postmix\Exception\Database\MissingColumnException;
use Postmix\Exception\Database\MissingPrimaryKeyException;
use Postmix\Exception\Model\UnexpectedConditionException;

class Model {

	private static $sourceTableName;

	private static $connection;

	const COLUMN_CREATED_AT = 'created_at';

	const COLUMN_UPDATED_AT = 'updated_at';

	const COLUMN_DELETED_AT = 'deleted_at';

	/**
	 * Model constructor.
	 */

	public function __construct(array $data = []) {

		if(!empty($data)) {

			$this->prefillData($data);
		}
	}

	/**
	 * Prefill data with input array
	 *
	 * @param array $data
	 */

	private function prefillData(array $data) {

		/**
		 * Loop input data
		 */

		foreach($data as $field => $value) {

			/**
			 * Set all fields
			 */

			$this->{$field} = $value;
		}
	}

	/**
	 * Fetch all records
	 *
	 * @param array $conditions
	 *
	 * @return Model[]|null
	 * @throws \Postmix\Exception
	 */

	public static function fetchAll($conditions = []) {

		$connection = self::getConnection();

		$resultSet = [];

		/**
		 * Recognize deleted by DELETED_AT column
		 */

		if(isset($conditions['deleted']) && $conditions['deleted'])
			$conditions[] =  self::COLUMN_DELETED_AT . ' != NULL';
		else
			$conditions[] = self::COLUMN_DELETED_AT . ' = NULL';

		unset($conditions['deleted']);

		/**
		 * Select records
		 */

		foreach($connection->select(self::getTableName(), $conditions) as $item) {

			$modelClass = get_called_class();

			$model = new $modelClass($item);

			$resultSet[] = $model;
		}

		return $resultSet;
	}

	/**
	 * Fetch one record
	 *
	 * @param array $conditions
	 *
	 * @return bool|Model
	 * @throws UnexpectedConditionException
	 * @throws \Postmix\Exception
	 */

	public static function fetchOne($conditions = []) {

		$connection = self::getConnection();

		/**
		 * Create query builder
		 */

		$builder = new QueryBuilder($conditions);

		if(isset($conditions[0])) {

			/**
			 * Recognize deleted by DELETED_AT column
			 */

			if(isset($conditions['deleted']) && $conditions['deleted'])
				$builder->andWhere(self::COLUMN_DELETED_AT . ' != NULL');
			else
				$builder->andWhere(self::COLUMN_DELETED_AT . ' = NULL');

			unset($conditions['deleted']);

		}

		/**
		 * Limiting is not allowed here
		 */

		if(isset($conditions['limit']))
			throw new UnexpectedConditionException('`limit` condition can\'t be set when fetching one record.');

		$conditions['limit'] = 1;

		/**
		 * Select record
		 */

		$fetchedData = $connection->select(self::getTableName(), $conditions);

		if(!empty($fetchedData)) {

			$modelClass = get_called_class();

			$model = new $modelClass($fetchedData[0]);

			$result = $model;

		}

		return $result;
	}

	/**
	 * Save
	 * ----
	 *
	 * Save model state and date
	 *
	 * @return bool
	 * @throws \Postmix\Exception
	 */

	public function save() {

		$connection = self::getConnection();

		$columns = $connection->getTableColumns(self::getTableName());

		$values = [];
		$primary = false;

		foreach($columns as $field => $column) {

			if($column['primary'] != true) {

				if(isset($this->{$field}) && !is_null($this->{$field})) {

					$values[$field] = $this->{$field};

				} else {

					$values[$field] = NULL;
				}

			} else {

				$primary = $field;
			}
		}

		/**
		 * DateTime columns
		 */

		if(isset($columns[self::COLUMN_UPDATED_AT]))
			$values[self::COLUMN_UPDATED_AT] = date('Y-m-d H:i:s');

		/**
		 * Save record
		 */

		if($primary != false && isset($this->{$primary})) {

			/**
			 * Update existing record
			 */

			if($connection->update(self::getTableName(), $values, [
				$primary => $this->{$primary}
			]))
				return false;

		} else {

			if(isset($columns[self::COLUMN_CREATED_AT]))
				$values[self::COLUMN_CREATED_AT] = date('Y-m-d H:i:s');

			/**
			 * Create new if primary field doesn't exist
			 */

			if(!$connection->insert(self::getTableName(), $values))
				return false;
		}

		return true;
	}

	public function delete($permanently = false) {

		$connection = self::getConnection();

		$columns = $connection->getTableColumns(self::getTableName());

		if(!$permanently) {

			if(!isset($columns[self::COLUMN_DELETED_AT]))
				throw new MissingColumnException('Column `' . self::COLUMN_DELETED_AT . '` is missing for impermanent removing records.');

			/**
			 * Set column DELETED_AT to actual DateTime and save it
			 */

			$this->{self::COLUMN_DELETED_AT} = date('Y-m-d H:i:s');

			return $this->save();

		} else {

			foreach($columns as $field => $column) {

				if($column['primary'])
					$primary = $field;

			}

			if($primary != false && isset($this->{$primary})) {

				if(!$connection->delete(self::getTableName(), [
					$primary => $this->{$primary}
				]))
					return false;

			} else
				throw new MissingPrimaryKeyException('Can\'t delete model when primary key is missing in model.');

		}

		return true;
	}

	/**
	 * Get connection
	 * --------------
	 * Get database connection instance
	 *
	 * @return AdapterInterface
	 *
	 * @throws \Postmix\Exception
	 */

	private static function getConnection() {

		if(!isset(self::$connection)) {

			$injector = Application::getStaticInjector();

			/** @var AdapterInterface $connection */

			self::$connection = $injector->get('database');

		}

		return self::$connection;
	}

	/**
	 * Get table name
	 * --------------
	 * Get source table name for database quering
	 *
	 * @return string
	 */

	private static function getTableName() {

		return isset(self::$sourceTableName) ? self::$sourceTableName : substr(strrchr(get_called_class(), "\\"), 1);
	}

	/**
	 * @return string
	 */

	public function getSourceName() {

		return self::getTableName();
	}

}