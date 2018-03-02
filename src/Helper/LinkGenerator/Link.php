<?php


namespace Postmix\Helper\LinkGenerator;

use Postmix\Exception;
use Postmix\Structure\Mvc\Controller;

/**
 * Class Link
 * @package Postmix\Helper\LinkGenerator
 */

class Link {

	private $module;

	private $controller;

	private $action;

	private $params = [];

	/**
	 * Link constructor.
	 *
	 * @param array $linkArgs
	 *
	 * @throws Exception
	 * @throws Exception\NotFoundException
	 */

	public function __construct(array $linkArgs) {

		if(!isset($linkArgs['module']) || !isset($linkArgs['controller']) || !isset($linkArgs['action']))
			throw new Exception('Invalid number of arguments for creating link directly.');

		/**
		 * Check for existing link
		 */

		if(!Controller::existsWithAction($linkArgs['module'], $linkArgs['controller'], $linkArgs['action']))
			throw new Exception\NotFoundException('Invalid link array input.');

		/**
		 * Set values now
		 */

		$this->module = $linkArgs['module'];
		$this->controller = $linkArgs['controller'];
		$this->controller = $linkArgs['action'];

		/**
		 * Unset from haystack
		 */

		unset($linkArgs['module']);
		unset($linkArgs['controller']);
		unset($linkArgs['action']);

		/**
		 * Set others as a link params
		 */

		$this->params = $linkArgs;

	}

}