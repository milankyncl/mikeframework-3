<?php

namespace Postmix\Database\Adapter;

use Postmix\Database\Adapter;
use Postmix\Exception;
use Postmix\Exception\Database\UnknownTableException;
use Postmix\Exception\Database\MissingColumnValueException;
use Postmix\Exception\Database\UnknownColumnException;

/**
 * Class MySQL
 *
 * @package Postmix\Core\Database\Adapter
 */

class MySQL extends Adapter {

	private $tableColumns = [];

	/**
	 * MySQL PDO Adapter constructor.
	 *
	 * @param string $database
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 */

	public function __construct($database, $host = '127.0.0.1', $username = 'root', $password = null) {

		$connection = new \PDO('mysql:dbname=' . $database . ';host=' . $host, $username, $password);

		$this->connection = $connection;

	}

	/**
	 * Select
	 * ------
	 * Select data from table
	 *
	 * @param $table
	 * @param $conditions
	 * @param string $columns
	 *
	 * @return array
	 * @throws UnknownTableException
	 */

	public function select($table, $conditions, $columns = '*') {

		$this->describeTable($table);

		// Create select query statement

		$statement = 'SELECT ' . $columns . ' FROM `' . $table . '`';

		$whereSet = false;
		$parameters = [];

		foreach($conditions as $criterium => $condition) {

			if(!in_array($criterium, [ 'order', 'limit' ])) {

				if(!$whereSet) {

					$statement .= ' WHERE';
					$whereSet = true;
				}

				$statement .= ' `' . $criterium . '` = :' . $criterium;

				$parameters[$criterium] = $condition;
			}
		}

		if(isset($conditions['order'])) {

			// TODO: Order condition
		}

		if(isset($conditions['limit'])) {

			// TODO: Limit condition
		}

		return $this->prepareQuery($statement, $parameters)->fetchAll();
	}

	/**
	 * Insert data into table
	 * ---------------------
	 * Create insert statement, prepare query and bind values into
	 *
	 * @param $tableName string - Table name
	 * @param $data array - Data array
	 *
	 * @return int
	 */

	public function insert($tableName, array $data) {

		// Describe table

		$this->describeTable($tableName);

		$data = $this->checkInput($tableName, $data);

		// Get query for statement

		$statement = 'INSERT INTO `' . $tableName . '` VALUES(';

		$i = 0;

		foreach($this->tableColumns[$tableName] as $columnName => $column) {

			$i++;

			$statement .= ':' . $columnName;

			if(count($this->tableColumns[$tableName])  != $i)
				$statement .= ',';
		}

		$statement .= ')';

		/**
		 * Create query and execute the INSERT statemenet
		 */

		$this->prepareQuery($statement, null, $data)->execute();

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

		$missing = [];

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

	private function prepareQuery($statement, $params = [], $values = []) {

		$query = $this->connection->prepare($statement);

		$query->setFetchMode(\PDO::FETCH_ASSOC);

		/**
		 * Bind parameters
		 */

		if(!is_null($params)) {

			foreach($params as $key => $value) {

				if(is_int($key))
					$query->bindParam($key, $value);
				else
					$query->bindParam(':' . $key, $value);
			}

		}

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
}