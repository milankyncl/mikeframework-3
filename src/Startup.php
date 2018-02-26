<?php

namespace Postmix;

use Dotenv\Dotenv;
use Postmix\Database\Adapter;
use Postmix\Core\Autoloader;
use Postmix\Core\Debugger;
use Postmix\Http\Request;
use Postmix\Http\Response;
use Postmix\Structure\Mvc\View;
use Postmix\Structure\Router;

/**
 * Class Startup
 * @package Postmix
 */

class Startup {

	/**
	 * @var array Configuration
	 */

	private $configuration;

	/**
	 * @var string Application directory path
	 */

	private $appDirectory;

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

	}

	/**
	 * Create dependency injector
	 *
	 *
	 */

	private function createDependencyInjector() {

		$injector = new Injector;

		/**
		 * Database service
		 */

		if(isset($this->configuration['database'])) {

			$injector->add(function() {

				if(!isset($this->configuration['database']['adapter']))
					throw new Exception('Database adapter must be specified.');

				if(!is_null(Adapter::ADAPTERS[$this->configuration['database']['adapter']])) {

					$adapterClass = Adapter::ADAPTERS[$this->configuration['database']['adapter']];

					$adapter = new $adapterClass(
						$this->configuration['database']['connection']['database'],
						$this->configuration['database']['connection']['host'],
						$this->configuration['database']['connection']['user'],
						$this->configuration['database']['connection']['password']
						);

					return $adapter;
				}

				return null;

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

		//

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

	public function getResponse() {

		$application = new Application($this->appDirectory);

		$application->setModules($this->modules);

		$application->setInjector($this->injector);

		return $application->handle();
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