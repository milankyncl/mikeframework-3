<?php


namespace Postmix\Structure;

use Postmix\Injector\Service;

/**
 * Class Router
 * @package Postmix\Structure
 */

class Router extends Service {

	private $module = 'Web';

	private $controller = 'Index';

	private $action = 'index';

	private $parameters = [];

	private $url;

	/**
	 * Router constructor.
	 */

	public function __construct() {

		$this->getUrlParameters();
	}

	/**
	 * Get URL parameters
	 */

	private function getUrlParameters() {

		$url = isset($_GET['_url']) ? '/' . $_GET['_url'] : '/';

		foreach(explode('/', $url) as $param) {

			if($param != '') {

				$this->parameters[] = str_replace('-', '', preg_replace_callback('/-[a-z]/', function ($matches) {

					return  strtoupper($matches[0]);

				}, lcfirst($param)));
			}
		}

		$this->url = $url;
	}

	public function handle() {

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

}