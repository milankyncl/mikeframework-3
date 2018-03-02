<?php


namespace Postmix\Helper;

use Exception\InvalidLinkCodeFormat;
use Postmix\Helper\LinkGenerator\Link;
use Postmix\Injector\Service;

/**
 * Class Link
 * @package Helper
 */

class LinkGenerator extends Service {

	/**
	 * Create link from code
	 *
	 * @param $code array|string
	 */

	public function createFromCode($code) {

		$linkArray = self::parseCode($code);

		return new Link($linkArray);
	}

	/**
	 * Parse code into array
	 *
	 * @param $code
	 *
	 * @throws InvalidLinkCodeFormat
	 *
	 * @return array
	 */

	private function parseCode($code) {

		$result = [];

		if(strpos($code, '@')) {

			$parts = explode('@', $code);

			if(count($parts) == 2) {

				$result['module'] = $parts[0];
				$result['controller'] = $parts[1];

			} else if(count($parts) == 3) {

				$result['module'] = $parts[0];
				$result['controller'] = $parts[1];
				$result['action'] = $parts[2];

			} else {

				throw new InvalidLinkCodeFormat('Code link can be created only from max. 3 code parts.');
			}

		} else {

			throw new InvalidLinkCodeFormat('Code link must include at least one @ delimiter.');
		}

		/**
		 * Return link array now
		 */

		if(!isset($result['action']))
			$result['action'] = $this->injector->router->getDefaultAction();

		if(!isset($result['controller']))
			$result['controller'] = $this->injector->router->getDefaultController();

		if(!isset($result['module']))
			$result['controller'] = $this->injector->router->getDefaultModule();

		return $result;

	}
}