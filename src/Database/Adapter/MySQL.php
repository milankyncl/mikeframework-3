<?php

namespace Postmix\Database\Adapter;

use Postmix\Database\Adapter;
use Postmix\Exception;

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
	 * Insert data into table
	 * ---------------------
	 * Create insert statement, prepare query and bind values into
	 *
	 * @param $table_name string - Table name
	 * @param $data array - Data array
	 *
	 * @return boolean
	 */

	public function insert($table_name, array $data) {

		// Describe table

		$this->describeTable($table_name);

		$this->checkInput($table_name, $data);

		// Get query for statement

		$statement = 'INSERT INTO ' . $table_name . ' VALUES(';

		$i = 0;

		foreach($this->tableColumns[$table_name] as $column) {

			$i++;

			$statement .= '?';

			if(count($this->tableColumns[$table_name]) - 1 != $i)
				$statement .= ',';
		}

		$statement .= ')';

		print_r($this->prepareQuery($statement, $data));
	}

	private function checkInput($table_name, array $data) {

		foreach($this->tableColumns[$table_name] as $column) {


		}
	}

	/**
	 * Describe table
	 * --------------
	 * Get table columns and check for existing column
	 *
	 * @param $table
	 *
	 * @throws Exception\UnknownTableException
	 */

	private function describeTable($table) {

		if(!isset($this->tableColumns[$table])) {

			$describeQuery = $this->connection->query('DESCRIBE ' . $table);

			if(!$describeQuery)
				throw new Exception\UnknownTableException('Unknown database table `' . $table . '`');

			$describeQuery->setFetchMode(\PDO::FETCH_ASSOC);

			$columnsFetch = $describeQuery->fetchAll();

			$columns = [];

			echo '<pre>';

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

			print_r($columns);

			exit;

			$this->tableColumns[$table] = $columns;
		}

	}

	/**
	 * Get table foreign keys and their references
	 *
	 * @param $table_name
	 *
	 * @return array
	 */

	private function getTableReferences($table_name) {

		/**
		 * References SQL
		 */

		$referencesSql = 'SELECT concat(table_name, ".", column_name) as "foreign_key", ';
		$referencesSql .= 'concat(referenced_table_name, ".", referenced_column_name) as "reference" ';
		$referencesSql .= 'FROM information_schema.key_column_usage ';
		$referencesSql .= 'WHERE referenced_table_name IS NOT NULL AND table_name = "' . $table_name . '"';

		/**
		 * References query
		 */

		$referencesQuery = $this->connection->query($referencesSql);
		$referencesQuery->setFetchMode(\PDO::FETCH_ASSOC);

		return $referencesQuery->fetchAll();
	}

	private function prepareQuery($statement, $bindings = []) {

		$query = $this->connection->prepare($statement);

		foreach($bindings as $key => $value)
			$query->bindValue($key, $value);

		return $query;
	}
}