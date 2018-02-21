<?php


namespace Postmix\Injector;

use Postmix\Injector;


class Service {

	/** @var Injector */

	protected $injector;

	/**
	 * Set dependency injector
	 *
	 * @param Injector $injector
	 */

	public function setInjector(Injector $injector) {

		$this->injector = $injector;
	}

	/**
	 * Get dependency from Injector's container
	 *
	 * @param $name
	 */

	public function __get($name) {

		return $this->injector->get($name);
	}

}