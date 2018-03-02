<?php


namespace Postmix\Helper\LinkGenerator;

use Postmix\Application;
use Postmix\Exception;
use Postmix\Structure\Mvc\Controller;
use Postmix\Structure\Router;

/**
 * Class Link
 * @package Postmix\Helper\LinkGenerator
 */

class Link {

	private $module;

	private $controller;

	private $action;

	private $params = [];

	/**
	 * Link constructor.
	 *
	 * @param array $linkArgs
	 *
	 * @throws Exception
	 * @throws Exception\NotFoundException
	 */

	public function __construct(array $linkArgs) {

		if(!isset($linkArgs['module']) || !isset($linkArgs['controller']) || !isset($linkArgs['action']))
			throw new Exception('Invalid number of arguments for creating link directly.');

		/**
		 * Check for existing link
		 */

		if(!Controller::existsWithAction($linkArgs['module'], $linkArgs['controller'], $linkArgs['action']))
			throw new Exception\NotFoundException('Invalid link code for `' . $linkArgs['module'] . '@' . $linkArgs['controller'] .'@' . $linkArgs['action'] . '`');

		/**
		 * Set values now
		 */

		$this->module = $linkArgs['module'];
		$this->controller = $linkArgs['controller'];
		$this->action = $linkArgs['action'];

		/**
		 * Unset from haystack
		 */

		unset($linkArgs['module']);
		unset($linkArgs['controller']);
		unset($linkArgs['action']);

		/**
		 * Set others as a link params
		 */

		$this->params = $linkArgs;

	}

	/**
	 * Magic method for generating links from object
	 *
	 * @return string
	 * @throws Exception
	 */

	public function __toString() {

		$injector = Application::getStaticInjector();

		/** @var Router $router */

		$router = $injector->get('router');

		return '/' . (($this->module !=  $router->getDefaultModule()) ? $this->module . '/' : '') . (($this->controller != $router->getDefaultController()) ? $this->controller . '/' : '') . (($this->action != $router->getDefaultAction()) ? $this->action : '');
	}

}