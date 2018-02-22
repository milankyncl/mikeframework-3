<?php


namespace Postmix;


class Application {

	private $module;

	private $controller;

	private $action;

	private $content;

	private $dependencyInjector;

	public function handle() {

		$url = isset($_GET['_handle']) ? $_GET['_handle'] : null;

		$explodedUrl = explode(DIRECTORY_SEPARATOR, $url);

		foreach($explodedUrl as $key => $urlPart) {

			//
		}

		/**
		 * Default modules
		 */

		$this->module = 'Web';
		$this->controller = 'Index';
		$this->action = 'index';
	}

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