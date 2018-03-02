<?php


namespace Postmix\Structure\Mvc;

use Postmix\Exception;
use Postmix\Exception\FileNotFoundException;
use Postmix\Helper\LinkGenerator;
use Postmix\Injector\Service;

/**
 * Class View
 * @package Postmix\Structure\Mvc
 */

class View extends Service {

	/** @var string Views directory path */

	private $viewsDirectory;

	/** @var string Layout directory path */

	private $layoutDirectory;

	/** @var string Layout name */

	private $layout = 'main';

	/** @var bool */

	private $disabled = false;

	private $view;

	private $variables = [];

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

	public function render($controller, $action) {

		if(!isset($this->viewsDirectory) || !isset($this->layoutDirectory))
			throw new Exception('View service\'s `viewsDirectory` and `layoutDirectory` must be set before rendering view.');

		$layoutPath = $this->layoutDirectory . '/' . $this->layout . '.php';

		if(!file_exists($layoutPath))
			throw new FileNotFoundException('Layout `' . $this->layout .'` doesn\'t exist in `' . $this->layoutDirectory .'` path.');

		$this->view = lcfirst($controller) . '/' . $action;

		ob_start();

		require $layoutPath;

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	private function content() {

		$viewPath = $this->viewsDirectory . '/' . $this->view . '.php';

		if(!file_exists($viewPath))
			throw new FileNotFoundException('View `' . $this->view .'` doesn\'t exist in `' . $this->viewsDirectory .'` path.');

		require $viewPath;
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

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	/**
	 * Checks if view is disabled or not
	 *
	 * @return bool
	 */

	public function isDisabled() {

		return $this->disabled;
	}

	public function __set($name, $value) {

		$this->variables[$name] = $value;
	}

	public function __get($name) {

		return isset($this->variables[$name]) ? $this->variables[$name] : null;
	}

	/**
	 * View functions
	 */

	private function link($code) {

		/** @var LinkGenerator $linkGenerator */

		$linkGenerator = $this->injector->get('linkGenerator');

		return $linkGenerator->createFromCode($code);
	}
}