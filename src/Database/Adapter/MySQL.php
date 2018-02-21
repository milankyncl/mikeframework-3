<?php

namespace Postmix\Database\Adapter;

use Postmix\Database\Adapter;


class MySQL extends Adapter {

	/**
	 * MySQL PDO Adapter constructor.
	 *
	 * @param $database
	 * @param string $host
	 * @param string $username
	 * @param null $password
	 */

	public function __construct($database, $host = '127.0.0.1', $username = 'root', $password = null) {

		$connection = new \PDO('mysql:dbname=' . $database . ';host=' . $host, $username, $password);

		$this->connection = $connection;

	}

}