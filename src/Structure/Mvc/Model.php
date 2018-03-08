<?php


namespace Postmix\Structure\Mvc;


use Postmix\Application;
use Postmix\Database\Adapter;
use Postmix\Database\AdapterInterface;

class Model {

	/**
	 * Model constructor.
	 */

	public function __construct(array $data = []) {

		if(!empty($data)) {

			$this->prefillData($data);
		}
	}

	/**
	 * Prefill data with input array
	 *
	 * @param array $data
	 */

	private function prefillData(array $data) {

		$variables = get_class_vars(get_called_class());

		/**
		 * Loop input data
		 */

		foreach($data as $field => $value) {

			/**
			 * Set all fields
			 */

			if(key_exists($field, $variables))
				$this->{$field} = $value;
		}
	}

	public static function fetchAll($conditions) {

		$connection = self::getConnection();

		$connection->
	}

	/**
	 * Get connection
	 * --------------
	 * Get database connection instance
	 *
	 * @return AdapterInterface
	 *
	 * @throws \Postmix\Exception
	 */

	private static function getConnection() {

		$injector = Application::getStaticInjector();

		/** @var AdapterInterface $connection */

		$connection = $injector->get('database');

		return $connection;
	}

}