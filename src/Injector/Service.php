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
	 * Get dependency injector instance
	 *
	 * @return null|Injector
	 */

	public function getInjector() {

		return isset($this->injector) ? $this->injector : null;
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