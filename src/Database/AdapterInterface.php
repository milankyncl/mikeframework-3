<?php

namespace Postmix\Database;

/**
 * Class Adapter
 * @package Postmix\Database
 */

interface AdapterInterface {

	public function getTableColumns($tableName);

}