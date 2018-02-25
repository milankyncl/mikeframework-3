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
	 * Create
	 */

	public function add($definition, $name = null) {

		if(is_callable($definition))
			$class = $definition();

		else if(is_object($definition))
			$class = $definition;

		else
			$class = new $definition();

		if(!is_null($name) && class_exists($name))
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

	/**
	 * Magic method helper for getting dependencies
	 *
	 * @param $name
	 *
	 * @return mixed|null
	 */

	public function __get( $name ) {

		return $this->get($name);
	}

	/**
	 * Magic method for removing dependencies
	 *
	 * @param $name
	 */

	public function __unset( $name ) {

		$this->remove($name);
	}
}