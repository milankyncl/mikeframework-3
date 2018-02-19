<?php

namespace Postmix;

use Dotenv\Dotenv;
use Postmix\Core\Autoloader;

/**
 * Class Startup
 * @package Postmix
 */

class Startup {

	private $configuration;

	private $appDirectory;

	/**
	 * Startup constructor.
	 *
	 * Set application core directory path for later use
	 */

	public function __construct($appDirectory) {

		$this->appDirectory = $appDirectory;

		(new Dotenv($appDirectory . '/../'))->load();

		$this->loadConfigurations();
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

				$configuration = require $this->appDirectory . '/config/' . $file;

				if(!isset($this->configuration))
					$this->configuration = $configuration;
				else
					$this->configuration = array_merge($this->configuration, $configuration);

			}
		}
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

}