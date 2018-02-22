<?php

namespace Postmix\Core\Database\Adapter;

use Postmix\Core\Database\Adapter;

/**
 * Class MySQL
 *
 * @package Postmix\Core\Database\Adapter
 */

class MySQL extends Adapter {

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

}