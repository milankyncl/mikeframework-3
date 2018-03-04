<?php


namespace Postmix\Structure\Mvc;


use Postmix\Application;

class Model {

	/**
	 * Model constructor.
	 */

	public function __construct() {}

	public static function fetchOne() {
	}

	private static function prepareQuery() {

		$injector = Application::getStaticInjector();

		/** @var PDO $connection */

		$connection = $injector->get('database');
	}

}