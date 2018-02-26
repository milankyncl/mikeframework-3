<?php


namespace Postmix;

use Postmix\Http\Response;
use Postmix\Structure\Mvc\Controller;
use Postmix\Exception\UnexpectedReturnTypeException;

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

		$controllerClass = $module . '\\Controllers\\' . $controller . 'Controller';

		/** @var Controller $controller */

		$controller = new $controllerClass();

		$controller->setInjector($this->injector);

		$response = $controller->{$action . 'Action'}();

		/**
		 * Clean output after controller action execution
		 */

		if(ob_get_level())
			ob_end_clean();

		if(!is_null($response)) {

			if(!$response instanceof Response)
				throw new UnexpectedReturnTypeException('Action can return only instance of ' . Response::class . ' class.');

		} else {

			$response = $this->injector->get('response');
		}

		/**
		 * Send Http response
		 */

		if(!$response->isSent())
			$response->send();

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