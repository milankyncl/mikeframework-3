<?php

namespace Postmix\Database;

use Postmix\Exception;
use Postmix\Database\Adapter\MySQL;

/**
 * Class Adapter
 * @package Postmix\Database
 */

abstract class Adapter {

	/** @var \PDO */

	protected $connection;

	/**
	 * Get database connection
	 *
	 * @return mixed
	 * @throws Exception
	 */

	protected function getConnection() {

		if(!isset($this->connection))
			throw new Exception('Database connection wasn\'t created yet.');

		return $this->connection;
	}

	/**
	 * Adapter names
	 */

	const ADAPTERS = [

		'pdo_mysql' => MySQL::class
	];


}