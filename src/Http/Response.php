<?php


namespace Postmix\Http;

use Postmix\Http\Response\Headers;
use Postmix\Info;
use Postmix\Injector\Service;

/**
 * Class Response
 * @package Postmix\Http
 */

class Response extends Service {

	/** @var int Status code */

	private $code = 200;

	/** @var bool  */

	private $sent = false;

	/** @var string */

	private $content = null;

	/** @var Headers */

	private $headers;

	/**
	 * Response constructor.
	 */

	public function __construct() {

		$this->headers = new Headers();
	}

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
	 * Set content type
	 *
	 * @param $type
	 * @param string $encoding
	 */

	public function setContentType($type, $encoding = 'utf-8') {

		$this->headers->set('Content-Type', $type . '; charset=' . $encoding);
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
	 * Set response code
	 *
	 * @param $code
	 */

	public function setCode($code) {

		$this->code = $code;
	}

	/**
	 * Send headers
	 */

	public function send() {

		/**
		 * Send all headers
		 */

		$this->headers->set('X-Powered-By', 'Postmix Framework ' . Info::FRAMEWORK_VERSION);

		$this->headers->send();

		/**
		 * Set response code
		 */

		http_response_code($this->code);

		/**
		 * Set response as sent
		 */

		$this->sent = true;
	}

}