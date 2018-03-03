<?php


namespace Postmix\Http\Response;


class Headers {

	/** @var array Raw headers */

	private $headers = [];

	/**
	 * Send raw headers
	 */

	public function send() {

		foreach($this->headers as $header)
			header($header);

	}

	/**
	 * Set header
	 *
	 * @param $header
	 * @param $value
	 */

	public function set($header, $value) {

		$this->headers[] = $header . ': ' . $value;
	}

	/**
	 * Set rew header
	 *
	 * @param $header
	 */

	public function setRaw($header) {

		$this->headers[] = $header;
	}
}