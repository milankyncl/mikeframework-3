<?php


namespace Postmix\Structure;

use Postmix\Exception\NotFoundException;
use Postmix\Injector\Service;

/**
 * Class Router
 * @package Postmix\Structure
 */

class Router extends Service {

	private $defaultModule = 'Web';

	private $defaultController = 'Index';

	private $defaultAction = 'index';

	private $module;

	private $controller;

	private $action;

	private $urlParts = [];

	private $parameters = [];

	private $url;

	/**
	 * Router constructor.
	 */

	public function __construct() {

		$this->getUrlParts();
	}

	/**
	 * Get URL parameters
	 */

	private function getUrLParts() {

		$url = isset($_GET['_url']) ? '/' . $_GET['_url'] : '/';

		foreach(explode('/', $url) as $param) {

			if($param != '') {

				$this->urlParts[] = str_replace('-', '', preg_replace_callback('/-[a-z]/', function ($matches) {

					return  strtoupper($matches[0]);

				}, lcfirst($param)));
			}
		}

		$this->url = $url;
	}

	public function handle() {

		/**
		 * Zjištění zda existuje module
		 */

		$numberOfParts = count($this->urlParts);

		$this->action = $this->defaultAction;
		$this->controller = $this->defaultController;
		$this->module = $this->defaultModule;

		/**
		 * Now resolve URL parts
		 */

		if($numberOfParts == 1) {

			/**
			 * Check if only part is module, controller, or action
			 */

			if($this->moduleExists($this->urlParts[0])) {

				$this->module = $this->urlParts[0];

			} else if($this->controllerExists($this->defaultModule, $this->urlParts[0])) {

				$this->controller = $this->urlParts[0];

			} else {

				if($this->actionExists($this->module, $this->controller, $this->urlParts[0]))
					$this->action = $this->urlParts[0];

				else {

					$this->parameters[] = $this->urlParts[0];

					if($this->actionExists($this->module, $this->controller, $this->defaultAction))
						$this->action = $this->defaultAction;

					else
						return false;
				}

			}

		} else if($numberOfParts == 2) {

			/**
			 * Check if 2 url parts are module-controller, controller - action
			 */

			if($this->moduleExists($this->urlParts[0])) {

				$this->module = $this->urlParts[0];

				if($this->controllerExists($this->urlParts[0], $this->urlParts[1])) {

					$this->controller = $this->urlParts[1];

					if(!$this->actionExists($this->module, $this->controller, 'index'))
						return false;

				} else {

					if($this->actionExists($this->module, $this->defaultController, $this->urlParts[1]))
						$this->action = $this->urlParts[1];

					else {

						$this->parameters[] = $this->urlParts[1];

						if($this->actionExists($this->module, $this->defaultController, $this->defaultAction))
							$this->action = $this->defaultAction;

						else
							return false;
					}
				}

			} else if($this->controllerExists($this->defaultModule, $this->urlParts[0])) {

				$this->controller = $this->urlParts[0];
				$this->action = $this->action = $this->urlParts[1];

			} else {

				$this->parameters[1] = $this->urlParts[1];

				if($this->actionExists($this->defaultModule, $this->defaultController, $this->urlParts[0]))
					$this->action = $this->urlParts[0];

				else {

					$this->parameters[0] = $this->urlParts[0];

					if($this->actionExists($this->defaultModule, $this->defaultController, $this->defaultAction))
						$this->action = $this->defaultAction;

					else
						return false;

				}

			}

		} else if($numberOfParts >= 3) {

			/**
			 * Check if 3 url parts are module-controller-action, controller-action-param
			 */

			if($this->moduleExists($this->urlParts[0])) {

				$this->module = $this->urlParts[0];

				if($this->controllerExists($this->urlParts[0], $this->urlParts[1])) {

					$this->controller = $this->urlParts[1];

					if($this->actionExists($this->module, $this->controller, $this->urlParts[2]))
						$this->action = $this->urlParts[2];

					else {

						$this->parameters[] = $this->urlParts[2];

						if($this->actionExists($this->module, $this->controller, $this->defaultAction))
							$this->action = $this->defaultAction;

						else
							return false;

					}

				} else {

					$this->parameters[] = $this->urlParts[1];
				}

			} else {

				if($this->controllerExists($this->defaultModule, $this->urlParts[0])) {

					$this->controller = $this->urlParts[0];
					$this->action = $this->urlParts[1];
					$this->parameters[] = $this->urlParts[2];

				}
			}

			for($i = 3; $i < $numberOfParts; $i++) {

				$this->parameters = $this->urlParts[$i];
			}
		}

		return true;
	}

	/**
	 * Returns rewriten URI
	 *
	 * @return string
	 */

	public function getRewriteUri() {

		return $this->url;
	}

	/**
	 * Get route controller
	 *
	 * @return string
	 */

	public function getController() {

		return $this->controller;
	}

	/**
	 * Get route action
	 *
	 * @return string
	 */


	public function getAction() {

		return $this->action;
	}

	/**
	 * Get route module
	 *
	 * @return string
	 */

	public function getModule() {

		return $this->module;
	}

	/**
	 * Set module
	 */

	public function setModule($module) {

		$this->module = $module;
	}

	/**
	 * Get parameters
	 */

	public function getParameters() {

		return $this->parameters;
	}

	/**
	 * Set controller
	 */

	public function setController($controller) {

		$this->controller = $controller;
	}

	/**
	 * Set action
	 */

	public function setAction($action) {

		$this->action = $action;
	}

	/**
	 * Module exists
	 */

	private function moduleExists($moduleName) {

		return is_dir($this->configuration->system->appDirectory . '/modules/' . ucfirst($moduleName) . 'Module');
	}

	/**
	 * Controller exists in module
	 */

	private function controllerExists($moduleName, $controllerName) {

		return is_file($this->configuration->system->appDirectory . '/modules/' . ucfirst($moduleName) . 'Module/' . ucfirst($controllerName) . 'Controller.php');
	}

	/**
	 * Checks if action exists
	 *
	 * @param $moduleName
	 * @param $controllerName
	 * @param $actionName
	 *
	 * @return bool
	 */

	private function actionExists($moduleName, $controllerName, $actionName) {

		$controllerNamespace = '\\' . ucfirst($moduleName) . '\\Controllers\\' . ucfirst($controllerName) . 'Controller';

		$controller = new $controllerNamespace();

		if(method_exists($controller, $actionName . 'Action')) {

			$method = new \ReflectionMethod($controller, $actionName . 'Action');

			$parameters = $method->getParameters();

			if(count($this->parameters) <= count($parameters)) {

				for($i = 0; $i < count($parameters) - count($this->parameters); $i++)
					$this->parameters[] = false;

				return true;
			}
		}

		return false;
	}
}