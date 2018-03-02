<?php

namespace Postmix\Structure\Mvc;

use Postmix\Injector\Service;

/**
 * Class Controller
 *
 * @package Postmix\Structure\Mvc
 */

class Controller extends Service {

	/**
	 * Static function for checking controller existance
	 *
	 * @param $module
	 * @param $controller
	 * @param $action
	 * @param array $params
	 *
	 * @return bool
	 */

	public static function existsWithAction($module, $controller, $action, $params = []) {

		$controllerNamespace = '\\' . ucfirst($module) . '\\Controllers\\' . ucfirst($controller) . 'Controller';

		$controller = new $controllerNamespace();

		if(method_exists($controller, $action . 'Action')) {

			return true;
			/*
			$method = new \ReflectionMethod($controller, $action . 'Action');

			$parameters = $method->getParameters();

			if(count($this->parameters) <= count($parameters)) {

				for($i = 0; $i < count($parameters) - count($this->parameters); $i++)
					$this->parameters[] = false;

				return true;
			}
			*/
		}

		return false;
	}

	/**
	 * Magic method for generating link from Object
	 *
	 * @return string
	 */

	public function __toString() {

		return 'x';
	}

}