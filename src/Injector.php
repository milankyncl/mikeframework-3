<?php


namespace Postmix;

/**
 * Class Injector
 * @package Postmix
 */

class Injector {

	/**
	 * @var $container mixed[] Dependency container
	 */

	private $container;

	/**
	 * Injector constructor.
	 */

	public function __construct() {

		/**
		 * Create internal services here
		 */

	}

	/**
	 * Create
	 */

	public function add($definition, $name = null) {

		if(is_callable($definition))
			$class = $definition();
		else
			$class = $definition;

		if(!is_null($name))
			$name = get_class($class);

		$this->container[$name] = $class;
	}

	/**
	 * Get dependency from container
	 */

	public function get($name) {

		return (isset($this->container[$name])) ? $this->container[$name] : null;
	}

	/**
	 * Remove service from dependency container
	 */

	public function remove($name) {

		if(isset($this->container[$name]))
			unset($this->container[$name]);
	}
}