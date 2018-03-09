<?php

namespace Postmix;

use Dotenv\Dotenv;
use Postmix\Database\Adapter\PDO;
use Postmix\Core\Autoloader;
use Postmix\Core\Debugger;
use Postmix\Exception\NoRouteFoundException;
use Postmix\Exception\NotFoundException;
use Postmix\Helper\LinkGenerator;
use Postmix\Http\Request;
use Postmix\Http\Response;
use Postmix\Structure\Mvc\View;
use Postmix\Structure\Router;
use Postmix\Structure\Configuration;

/**
 * Class Startup
 * @package Postmix
 */

class Startup {

	/** @var array Configuration */

	private $configuration;

	/** @var string Application directory path */

	private $appDirectory;

	/** @var string Public base directory path */

	private $baseDirectory;

	/** @var string[] Existing modules */

	private $modules;

	/** @var Injector */

	private $injector;

	/**
	 * Startup constructor.
	 *
	 * Set application core directory path for later use
	 */

	public function __construct($appDirectory) {

		$this->appDirectory = $appDirectory;

		(new Dotenv($appDirectory . '/../'))->load();

		$this->baseDirectory = $appDirectory . '/../';

		$this->loadConfigurations();

		$this->createDependencyInjector();

		$this->loadServices();
	}

	/**
	 * Load configuration files
	 */

	private function loadConfigurations() {

		if(!is_dir($this->appDirectory . '/config'))
			throw new Exception('Config folder must be present in application directory.');

		/**
		 * Scan for all configurations file and require them
		 */

		$scan = scandir($this->appDirectory . '/config');

		foreach($scan as $file) {

			if(is_file($this->appDirectory . '/config/' . $file)) {

				$fileInfo = pathinfo($this->appDirectory . '/config/' . $file);

				// Accepts only .php files
				if(strtolower($fileInfo['extension']) == 'php') {

					$configuration = [
						strtolower($fileInfo['filename']) => require $this->appDirectory . '/config/' . $file
					];

					if(!isset($this->configuration))
						$this->configuration = $configuration;
					else
						$this->configuration = array_merge($this->configuration, $configuration);

				}
			}
		}

		/**
		 * Error Handler
		 */

		if($this->configuration['system']['debug'] == true) {

			$debugger = new Debugger();

			$debugger->listen();
		}

		/**
		 *
		 */

		$this->configuration['system']['appDirectory'] = $this->appDirectory;
		$this->configuration['system']['baseDirectory'] = $this->baseDirectory;
	}

	/**
	 * Create dependency injector
	 */

	private function createDependencyInjector() {

		$injector = new Injector;

		/**
		 * Configuration
		 */

		$injector->add(function() {

			return new Configuration($this->configuration);

		}, 'configuration');

		/**
		 * Database service
		 */

		if(isset($this->configuration['database'])) {

			$injector->add(function() {

				$adapter = new PDO(
					$this->configuration['database']['connection']['database'],
					$this->configuration['database']['connection']['host'],
					$this->configuration['database']['connection']['user'],
					$this->configuration['database']['connection']['password']
				);

				return $adapter;

			}, 'database');
		}

		/**
		 * Request instance
		 */

		$injector->add(Request::class, 'request');

		/**
		 * Router instance
		 */

		$injector->add(Router::class, 'router');

		/**
		 * Response instance
		 */

		$injector->add(Response::class, 'response');

		/**
		 * Link generator
		 */

		$injector->add(LinkGenerator::class, 'linkGenerator');

		/**
		 * Response instance
		 */

		$injector->add(View::class, 'view');

		/**
		 * Save dependency injector for later use
		 */

		$this->injector = $injector;
	}

	/**
	 * Load external services
	 */

	public function loadServices() {

		// TODO: Load external services from configuration

	}

	/**
	 * Create autoloader
	 *
	 * @return Autoloader
	 */

	public function createAutoloader() {

		$autoloader = new Autoloader();

		/**
		 * Register modules
		 */

		$this->resolveModules();

		foreach($this->modules as $module) {

			$autoloader->registerNamespaces([
				$module . '\Controllers' => $this->appDirectory . '/modules/' . $module . 'Module/controllers',
				$module . '\Library' => $this->appDirectory . '/modules/' . $module . 'Module/library'
			]);
		}

		return $autoloader;
	}

	/**
	 * Get response from application after handling request
	 *
	 * @return mixed|Response
	 * @throws Exception\UnexpectedReturnTypeException
	 * @throws NotFoundException
	 */

	public function getResponse() {

		$application = new Application($this->appDirectory);

		$application->setModules($this->modules);

		$application->setInjector($this->injector);

		if(!$this->configuration['system']['debug']) {

			try {

				return $application->handle();

			} catch(NoRouteFoundException $exception) {

				$error_handler = $this->injector->configuration->system->error_handler;

				$this->injector->router->setAction('notFoundException');
				$this->injector->router->setController('Error');

				if(!is_null($error_handler)) {

					if(strpos($error_handler, '@') != -1) {

						$parts = explode('@', $error_handler);

						$this->injector->router->setModule($parts[0]);
						$this->injector->router->setController($parts[1]);
					}
				}

			} catch(\Exception $exception) {

				// TODO: Zalogování chyby

				$this->injector->router->setAction('uncaughtException');
				$this->injector->router->setController('Error');
			}

			try {

				return $application->handle();

			} catch(\Exception $e) {

				/**
				 * Určitě zalogovat neodchytitelnou chybu
				 */

				$response = $this->injector->get('response');

				$response->setCode(500);
				$response->send();

				return $response;
			}

		} else {

			return $application->handle();
		}
	}

	/**
	 * Resolve existing modules
	 */

	private function resolveModules() {

		$modulesScan = scandir($this->appDirectory . '/modules/');

		foreach($modulesScan as $scanItem) {

			if(is_dir($this->appDirectory . '/modules/' . $scanItem) && strpos($scanItem, 'Module') != false) {

				$this->modules[] = str_replace('Module', '', $scanItem);
			}
		}
	}

}