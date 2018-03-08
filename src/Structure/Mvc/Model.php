<?php


namespace Postmix\Structure\Mvc;


use Postmix\Application;
use Postmix\Database\AdapterInterface;
use Postmix\Exception\Model\UnexpectedConditionException;

class Model {

	private static $sourceTableName;

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
	 * Fetch all
	 *
	 * @param array $conditions
	 *
	 * @return Model[]|null
	 * @throws \Postmix\Exception
	 */

	public static function fetchAll($conditions = []) {

		$connection = self::getConnection();

		$resultSet = [];

		foreach($connection->select(self::getTable(), $conditions) as $item) {

			$modelClass = get_called_class();

			$model = new $modelClass($item);

			$resultSet[] = $model;
		}

		return $resultSet;
	}

	public static function fetchOne($conditions = []) {

		$connection = self::getConnection();

		$result = false;

		if(isset($conditions['limit']))
			throw new UnexpectedConditionException('`limit` condition can\'t be set when fetching one record.');

		$conditions['limit'] = 1;

		$fetchedData = $connection->select(self::getTable(), $conditions);

		if(!empty($fetchedData))
			$result = $fetchedData[0];

		return $result;
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

		$injector = Application::getStaticInjector();

		/** @var AdapterInterface $connection */

		$connection = $injector->get('database');

		return $connection;
	}

	/**
	 * Get table
	 * --------
	 * Get source table name for database quering
	 *
	 * @return string
	 */

	private static function getTable() {

		return isset(self::$sourceTableName) ? self::$sourceTableName : substr(strrchr(get_called_class(), "\\"), 1);
	}

}