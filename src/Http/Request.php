<?php


namespace Postmix\Http;

use Postmix\Injector\Service;

/**
 * Class Request
 * @package Postmix\Http
 */

class Request extends Service {

	/**
	 * Get _REQUEST value
	 *
	 * @param null|string $key
	 *
	 * @return mixed
	 */

	public function get($key = null) {

		return $this->getHelper($_REQUEST, $key);
	}

	/**
	 * Get _POST value
	 *
	 * @param null|string $key
	 *
	 * @return mixed
	 */

	public function getPost($key = null) {

		return $this->getHelper($_POST, $key);
	}

	/**
	 * Helper for getting data from haystack
	 *
	 * @param $haystack
	 * @param null|string $key
	 *
	 * @return null|mixed
	 */

	private function getHelper($haystack, $key = null) {

		if(!is_null($key))
			return isset($haystack[$key]) ? $haystack[$key] : null;

		return $haystack;

	}

}