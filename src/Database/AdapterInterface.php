<?php

namespace Postmix\Database;

/**
 * Class Adapter
 * @package Postmix\Database
 */

interface AdapterInterface {

	public function select($tableName, array $conditions = []);

	public function update($tableName, array $data, array $conditions = []);

	public function insert($tableName, array $data);

	public function delete($tableName, array $conditions = []);

	public function getTableColumns($tableName);

}