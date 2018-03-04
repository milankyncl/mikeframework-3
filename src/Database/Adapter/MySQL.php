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
	 *
	 * @param $table_name string - Table name
	 * @param $data array - Data array
	 *
	 * @return boolean
	 */

	public function insert($table_name, $data) {

		$statement = '';

		// $this->connection->prepare();
	}

	public function prepareQuery($table, $statement, $bindings = []) {

		if(!isset($this->tableColumns[$table])) {

			$describeQuery = $this->connection->query('DESCRIBE ' . $table);

			if(!$describeQuery)
				throw new Exception\UnknownTableException('Unknown database table `' . $table . '`');

			$describeQuery->setFetchMode(\PDO::FETCH_ASSOC);

			$columnsFetch = $describeQuery->fetchAll();

			$columns = [];

			foreach($columnsFetch as $column) {

				if($column['Key'] == 'PRI')
					$columns['primary'] = $column['Field'];
				else
					$columns[] = $column['Field'];
			}

			$this->tableColumns[$table] = $columns;
		}

		$columns = $this->tableColumns[$table];

		print_r($columns);

		exit;
	}
}