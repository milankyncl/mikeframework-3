<?php


namespace Postmix\Structure\Mvc;

use Postmix\Exception\FileNotFoundException;
use Postmix\Injector\Service;

/**
 * Class View
 * @package Postmix\Structure\Mvc
 */

class View extends Service {

	/** @var string */

	private $content;

	/** @var string Views directory path */

	private $viewsDirectory;

	/** @var string Layout directory path */

	private $layoutDirectory;

	/**
	 * Clear buffer output
	 */

	public function clear() {

		if(ob_get_level())
			ob_end_clean();
	}

	/**
	 * Render content of view
	 *
	 * @param $file
	 */

	public function render() {



	}

	/**
	 * Set views directory path
	 *
	 * @param $path
	 *
	 * @throws FileNotFoundException
	 */

	public function setViewsDirectory($path) {

		if(!is_dir($path))
			throw new FileNotFoundException($path);

		$this->viewsDirectory = $path;
	}

	/**
	 * Set layout directory path
	 *
	 * @param $path
	 *
	 * @throws FileNotFoundException
	 */

	public function setLayoutDirectory($path) {

		if(!is_dir($path))
			throw new FileNotFoundException($path);

		$this->layoutDirectory = $path;
	}

	/**
	 * Render function helper
	 *
	 * @param $file
	 */

	private function renderHelper($file) {

		ob_start();

		require $file;

		return ob_end_clean();

	}

	private function content() {

		if(isset($this->viewContent))
			return $this->viewContent;
	}

}