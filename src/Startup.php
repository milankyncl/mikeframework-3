<?php

namespace Postmix;

use Dotenv\Dotenv;
use Postmix\Core\Autoloader;
use Postmix\Core\Database\Adapter;

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

	/** @var Injector */

	private $dependencyInjector;

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
	}

	/**
	 * Create dependency injector
	 *
	 *
	 */

	private function createDependencyInjector() {

		$dependencyInjector = new Injector;

		/**
		 * Database service
		 */

		if(isset($this->configuration['database'])) {

			$dependencyInjector->add(function() {

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


			}, 'database');
		}

		$this->dependencyInjector = $dependencyInjector;
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

		return $autoloader;
	}

	public function createApplication() {

		$application = new Application();

		return $application;
	}

}