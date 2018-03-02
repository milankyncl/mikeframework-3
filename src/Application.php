<?php


namespace Postmix;

use Postmix\Exception\NoRouteFoundException;
use Postmix\Http\Response;
use Postmix\Structure\Mvc\Controller;
use Postmix\Exception\UnexpectedReturnTypeException;
use Postmix\Structure\Mvc\View;
use Postmix\Structure\Router;

/**
 * Class Application
 *
 * @package Postmix
 */

class Application {

	/** @var Injector */

	private $injector;

	/** @var string */

	private $appDirectory;

	/**
	 * Application constructor.
	 *
	 * @param $appDirectory
	 */

	public function __construct($appDirectory) {

		$this->appDirectory = $appDirectory;
	}

	/**
	 * Set application modules
	 *
	 * @param $modules
	 */

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
	 * Handle request and return the response
	 */

	public function handle() {

		/** @var Router $router */

		$router = $this->injector->get('router');

		if(!$router->handledBefore() && !$router->handle())
			throw new NoRouteFoundException('No route for request found.', 404);


		/**
		 * Get module, controller and action
		 */

		$module = $router->getModule();
		$controller = $router->getController();
		$action = $router->getAction();

		/** @var View $view */

		$view = $this->injector->get('view');

		$view->setViewsDirectory($this->appDirectory . '/modules/' . $module . 'Module/views');

		$view->setLayoutDirectory($this->appDirectory . '/modules/' . $module . 'Module/layouts');

		/**
		 * Now execute route
		 */

		$controllerClass = $module . '\\Controllers\\' . $controller . 'Controller';

		/** @var Controller $controllerObject */

		$controllerObject = new $controllerClass();

		$controllerObject->setInjector($this->injector);

		/**
		 * Call the action now
		 */

		$response = call_user_func_array(array($controllerObject, $action . 'Action'), $router->getParameters());

		/**
		 * Check for send response
		 *
		 * @var $response Response|mixed
		 */

		if(!is_null($response)) {

			if(!$response instanceof Response)
				throw new UnexpectedReturnTypeException('Action can return only instance of ' . Response::class . ' class.');

		} else
			$response = $this->injector->get('response');

		/**
		 * Render template, if view is not disabled
		 */

		if(!$view->isDisabled()) {

			$view->clear();

			$response->setContent($view->render($controller, $action));
		}

		/**
		 * Send Http response
		 */

		if(!$response->isSent())
			$response->send();

		return $response;
	}

}