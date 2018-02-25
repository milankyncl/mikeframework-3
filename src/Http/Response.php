<?php


namespace Postmix\Http;

use Postmix\Injector\Service;

/**
 * Class Response
 * @package Postmix\Http
 */

class Response extends Service {

	/** @var string */

	private $content = null;

	/**
	 * Set response content
	 *
	 * @param $content string
	 */

	public function setContent($content) {

		$this->content = $content;
	}

	/**
	 * Return response content
	 *
	 * @return null|string
	 */

	public function getContent() {

		return $this->content;
	}

	/**
	 * Send headers
	 */

	public function sendHeaders() {


	}

}