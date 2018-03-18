<?php

namespace Postmix\Database\Adapter;

use Postmix\Database\Adapter;
use Postmix\Database\QueryBuilder;
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

	private static $queries = [];

	/**
	 * MySQL PDO Adapter constructor.
	 *
	 * @param string $database
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 */

	public function __construct($database, $host = '127.0.0.1', $username = 'root', $password = null, $charset = 'utf8') {

		$connection = new \PDO( 'mysql:dbname=' . $database . ';host=' . $host . ';charset=' . $charset, $username, $password );

		$connection->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );

		$this->connection = $connection;

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

	public function prepareQuery($statement, $values = []) {

		self::$queries[] = $statement;

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

		self::$queries[] = $query;

		return $this->connection->query($query, $fetchMode);

	}

	public function getLastInsertId() {

		return $this->connection->lastInsertId();
	}

	public static function getLastQueries() {

		return self::$queries;
	}
}