<?php


namespace Postmix;

/**
 * Class Application
 *
 * @package Postmix
 */

class Application {

	private $module;

	private $controller;

	private $action;

	private $content;

	private $dependencyInjector;


	public function setModules($modules) {

		$this->modules = $modules;
	}

	/**
	 * @param Injector $injector
	 */

	public function setDependencyInjector(Injector $injector) {

		$this->dependencyInjector = $injector;
	}

	public function getContent() {

		$controllerClass = $this->module . '\\Controllers\\' . $this->controller . 'Controller';

		$controller = new $controllerClass();

		$response = $controller->{$this->action . 'Action'}();

		return $this->content;
	}

}