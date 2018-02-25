<?php


namespace Postmix;

use Postmix\Http\Response;

/**
 * Class Application
 *
 * @package Postmix
 */

class Application {

	/** @var string */

	private $content;

	/** @var Injector */

	private $injector;


	public function setModules($modules) {

		$this->modules = $modules;
	}

	/**
	 * @param Injector $injector
	 */

	public function setInjector(Injector $injector) {

		$this->injector = $injector;
	}

	/**
	 * Handle request
	 */

	public function handle() {

		$module = $this->injector->router->getModule();
		$controller = $this->injector->router->getController();
		$action = $this->injector->router->getAction();

		ob_start();

		$controllerClass = $module . '\\Controllers\\' . $controller . 'Controller';

		$controller = new $controllerClass();

		$response = $controller->{$action . 'Action'}();

		if(!is_null($response)) {

			if($response instanceof Response) {

				$response->send();

			} else
				throw new Exception('Action can return only instance of ' . Response::class . ' class.');
		}

	}

	/**
	 * Get application content
	 *
	 * @return mixed
	 */

	public function getContent() {

		return $this->content;
	}

}