<?php


namespace Postmix\Http;

use Postmix\Injector\Service;

/**
 * Class Response
 * @package Postmix\Http
 */

class Response extends Service {

	private $sent = false;

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

	public function setContentType($type, $encoding) {


	}

	/**
	 * Check if response was already sent, or not
	 *
	 * @return bool
	 */

	public function isSent() {

		return $this->sent;
	}

	/**
	 * Send headers
	 */

	public function send() {

		$this->sent = true;
	}

}