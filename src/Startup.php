<?php

namespace Postmix;

/**
 * Class Startup
 * @package Postmix
 */

class Startup {

	protected $appDirectory;

	/**
	 * Startup constructor.
	 */

	public function __construct() {


	}

	/**
	 * Set application core directory path for later use
	 *
	 * @param $appDirectory
	 */

	public function setAppDirectory($appDirectory) {

		$this->appDirectory = $appDirectory;
	}

}