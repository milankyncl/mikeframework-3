<?php

namespace Postmix\Database\Adapter;

use Postmix\Database\Adapter;
use Postmix\Exception\Database\UnknownTableException;
use Postmix\Exception\Database\MissingColumnValueException;
use Postmix\Exception\Database\UnknownColumnException;
use Postmix\Exception\Model\UnexpectedConditionException;

/**
 * Class PDO
 *
 * @package Postmix\Core\Database\Adapter
 */

class PDO extends Adapter {

	private $tableColumns = [];

	/**
	 * MySQL PDO Adapter constructor.
	 *
	 * @param string $database
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 */

	public function __construct($database, $host = '127.0.0.1', $username = 'root', $password = null, $charset = 'utf8') {

		$connection = new \PDO('mysql:dbname=' . $database . ';host=' . $host . ';charset=' . $charset, $username, $password);

		$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		$this->connection = $connection;

	}

	/**
	 * Delete
	 * ------
	 * Delete data in table
	 *
	 * @param string $tableName
	 * @param array $conditions
	 *
	 * @return \PDOStatement|bool
	 * @throws UnknownTableException
	 */

	public function delete($tableName, array $conditions = []) {

		$this->describeTable($tableName);

		// Create select query statement

		$whereSet = false;

		$statement = 'DELETE FROM `' . $tableName . '`';

		$parameters = [];

		/**
		 * Conditions
		 */

		$i = 0;

		foreach($conditions as $criterium => $condition) {

			if(!in_array($criterium, [ 'order', 'limit' ])) {

				if(!$whereSet) {

					$statement .= ' WHERE';
					$whereSet = true;
				}

				$statement .= ' `' . $criterium . '` = ?';

				$parameters[++$i] = $condition;
			}
		}

		/**
		 * Prepare query and fetch matching records
		 */

		$query = $this->prepareQuery($statement, $parameters);

		if(!$query->execute())
			return false;

		return $query;
	}

	/**
	 * Update
	 * ------
	 * Update data in table
	 *
	 * @param string $tableName
	 * @param array $data
	 * @param array $conditions
	 *
	 * @return \PDOStatement|bool
	 * @throws UnknownTableException
	 */

	public function update($tableName, array $data, array $conditions = []) {

		$this->describeTable($tableName);


		// Create select query statement

		$whereSet = false;

		$statement = 'UPDATE `' . $tableName . '`';

		$setSet = false;
		$values = [];
		$i = 1;

		/**
		 * Data
		 */

		foreach($data as $field => $value) {

			if(!$setSet) {

				$statement .= ' SET';
				$setSet = true;
			}

			$statement .= ' `' . $field . '` = ?';

			if($i != count($data))
				$statement .= ',';

			$values[$i] = $value;

			$i++;
		}

		/**
		 * Conditions
		 */

		foreach($conditions as $criterium => $condition) {

			if(!in_array($criterium, [ 'order', 'limit' ])) {

				if(!$whereSet) {

					$statement .= ' WHERE';
					$whereSet = true;
				}

				$statement .= ' `' . $criterium . '` = ?';

				$values[$i] = $condition;

				$i++;
			}
		}

		/**
		 * Prepare query and fetch matching records
		 */

		$query = $this->prepareQuery($statement, $values);

		if(!$query->execute())
			return false;

		return $query;
	}

	/**
	 * Select
	 * ------
	 * Select data from table
	 *
	 * @param $tableName
	 * @param $conditions
	 * @param string $columns
	 *
	 * @return array|bool
	 * @throws UnknownTableException
	 */

	public function select($tableName, array $conditions = [], $columns = '*') {


		$this->describeTable($tableName);

		// Create select query statement

		$statement = 'SELECT ' . $columns . ' FROM `' . $tableName . '`';

		$whereSet = false;
		$parameters = [];

		$i = 1;

		foreach($conditions as $criterium => $condition) {

			if(!is_scalar($condition))
				throw new UnexpectedConditionException('Condition `' . $criterium . '` is not scalar.');

			if(!$whereSet) {

				$statement .= ' WHERE';
				$whereSet = true;
			}

			if(is_scalar($criterium)) {

				if(!in_array($criterium, [ 'order', 'limit', 'bind' ])) {

					$statement .= ' `' . $criterium . '` = ?';

					$parameters[$i] = $condition;

					$i++;
				}

			} else {

				/**
				 * String conditions
				 */

				$statement .= ' ' . $condition;

			}
		}

		if(isset($conditions['order'])) {

		}

		if(isset($conditions['order'])) {

			$statement .= ' ORDER BY ' . $conditions['order'];
		}

		if(isset($conditions['limit'])) {

			$statement .= ' LIMIT ' . $conditions['limit'];
		}

		/**
		 * Prepare query and fetch matching records
		 */

		$query =  $this->prepareQuery($statement, $parameters);

		if(!$query->execute())
			return false;

		return $query->fetchAll();
	}

	/**
	 * Insert data into table
	 * ---------------------
	 * Create insert statement, prepare query and bind values into
	 *
	 * @param $tableName string - Table name
	 * @param $data array - Data array
	 *
	 * @return int|bool
	 */

	public function insert($tableName, array $data) {

		// Describe table

		$this->describeTable($tableName);

		$data = $this->checkInput($tableName, $data);

		// Get query for statement

		$statement = 'INSERT INTO `' . $tableName . '` VALUES(';

		$values = [];
		$i = 1; $c = 0;

		foreach($this->tableColumns[$tableName] as $columnName => $column) {

			$c++;

			if(isset($data[$columnName])) {

				$values[$i++] = $data[$columnName];

				if($column)
					$statement .= '?';

			} else {

				$statement .= 'NULL';
			}

			if(count($this->tableColumns[$tableName]) != $c)
				$statement .= ',';
		}

		$statement .= ')';

		/**
		 * Create query and execute the INSERT statement
		 */

		$query = $this->prepareQuery($statement, $values);

		if(!$query->execute())
			return false;

		return $this->connection->lastInsertId();
	}

	/**
	 * Checking data input
	 * -------------------
	 * Check input data for missing or overlapping values
	 *
	 * @param string $tableName
	 * @param array $data
	 *
	 * @throws UnknownColumnException
	 */

	private function checkInput($tableName, array $data) {

		/**
		 * Loop data input, look for missing columns in table columns
		 */

		foreach($data as $dataField => $dataValue) {

			if(!isset($this->tableColumns[$tableName][$dataField]))
				throw new UnknownColumnException('Column `' . $dataField .'` doesn\'t exist in table `' . $tableName . '`');

		}

		/**
		 * Loop table structure
		 */

		foreach($this->tableColumns[$tableName] as $field => $column) {

			if(!isset($data[$field]) && !$column['null'] && !$column['primary'])
				throw new MissingColumnValueException('Value for `' . $field . '` column is missing, column value can\'t be NULL.');

			if($column['primary'] || (!isset($data[$field]) && $column['null']))
				$data[$field] = NULL;
		}

		/**
		 * Return data back
		 */

		return $data;
	}

	/**
	 * Describe table
	 * --------------
	 * Get table columns and check for existing column
	 *
	 * @param $table
	 *
	 * @throws UnknownTableException
	 */

	private function describeTable($table) {

		if(!isset($this->tableColumns[$table])) {

			$describeQuery = $this->connection->query('DESCRIBE ' . $table);

			if(!$describeQuery)
				throw new UnknownTableException('Unknown database table `' . $table . '`');

			$describeQuery->setFetchMode(\PDO::FETCH_ASSOC);

			$columnsFetch = $describeQuery->fetchAll();

			$columns = [];

			foreach($columnsFetch as $column) {

				$columns[$column['Field']] = [
					'primary' => ($column['Key'] == 'PRI'),
					'null' => ($column['Null'] == 'YES')
				];
			}

			/**
			 * Get foreign keys
			 */

			foreach($this->getTableReferences($table) as $foreignKey) {

				// Prepare foreign key

				$foreignKeyExplode = explode('.', $foreignKey['foreign_key']);
				$foreignColumnName = $foreignKeyExplode[1];

				// Prepare reference

				$referenceExplode = explode('.', $foreignKey['reference']);
				$referenceTableName = $referenceExplode[0];
				$referenceColumnName = $referenceExplode[1];

				// Now save info

				$columns[$foreignColumnName]['foreignKeys'][] = [
					'table' => $referenceTableName,
					'column' => $referenceColumnName
				];
			}

			$this->tableColumns[$table] = $columns;
		}

	}

	/**
	 * Get table columns
	 * -----------------
	 *
	 * Get described table columns
	 *
	 * @param $tableName
	 *
	 * @return bool|mixed
	 */

	public function getTableColumns($tableName) {

		// Is this really indeed?
		// Tables should be described when fetching datas, or reaching them before, but whatever
		$this->describeTable($tableName);

		if(isset($this->tableColumns[$tableName]))
			return $this->tableColumns[$tableName];

		return false;
	}

	/**
	 * Get table references
	 * -------------------
	 * Get table foreign keys and their references
	 *
	 * @param $tableName
	 *
	 * @return array
	 */

	private function getTableReferences($tableName) {

		/**
		 * References SQL
		 */

		$referencesSql = 'SELECT concat(table_name, ".", column_name) as "foreign_key", ';
		$referencesSql .= 'concat(referenced_table_name, ".", referenced_column_name) as "reference" ';
		$referencesSql .= 'FROM information_schema.key_column_usage ';
		$referencesSql .= 'WHERE referenced_table_name IS NOT NULL AND table_name = "' . $tableName . '"';

		/**
		 * References query
		 */

		$referencesQuery = $this->connection->query($referencesSql);
		$referencesQuery->setFetchMode(\PDO::FETCH_ASSOC);

		return $referencesQuery->fetchAll();
	}

	/**
	 * Prepare query
	 * ------------
	 * Prepare query for execution, bind values
	 *
	 * @param $statement
	 * @param array $bindings
	 *
	 * @return \PDOStatement
	 */

	private function prepareQuery($statement, $values = []) {

		$query = $this->connection->prepare($statement);

		$query->setFetchMode(\PDO::FETCH_ASSOC);

		/**
		 * Bind values
		 */

		if(!is_null($values)) {

			foreach($values as $key => $value) {

				if(is_int($key))
					$query->bindValue($key, $value);
				else
					$query->bindValue(':' . $key, $value);
			}

		}

		return $query;
	}

	public function execute($query, $fetchMode = \PDO::FETCH_ASSOC) {

		return $this->connection->query($query, $fetchMode);

	}
}