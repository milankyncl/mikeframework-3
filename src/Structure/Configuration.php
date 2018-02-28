<?php


namespace Postmix\Structure;


class Configuration extends \ArrayObject {

	private $configuration = [];

	/**
	 * Configuration constructor.
	 *
	 * @param array $configuration
	 */

	public function __construct(array $configuration) {

		$this->configuration = $configuration;
	}

	/**
	 * Magic method for accessing fields of configuration
	 *
	 * @param $name
	 *
	 * @return mixed|null
	 */

	public function __get($name) {

		if(!isset($this->configuration[$name]))
			return null;

		$configuration = $this->configuration[$name];

		if(is_scalar($configuration)) {

			return $configuration;

		} else {

			return (new self($configuration));
		}
	}

	/**
	 * Magic method for settings fields of configuration
	 *
	 * @param $name
	 * @param $value
	 */

	public function __set($name, $value) {

		$this->configuration[$name] = $value;
	}
}