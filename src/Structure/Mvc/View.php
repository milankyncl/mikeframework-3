<?php


namespace Postmix\Structure\Mvc;

use Postmix\Exception;
use Postmix\Exception\FileNotFoundException;
use Postmix\Exception\View\ViewRenderException;
use Postmix\Helper\LinkGenerator;
use Postmix\Injector\Service;
use Postmix\Structure\Configuration;

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

	private $cacheDirectory;

	public function afterInject() {

		/** @var Configuration $configuration */

		$configuration = $this->getInjector()->get('configuration');

		$this->cacheDirectory = $configuration->system->baseDirectory . $configuration->system->tempDirectory . '/cache';
	}

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

		/**
		 * View functions
		 */

		if(!isset($this->viewsDirectory) || !isset($this->layoutDirectory))
			throw new Exception('View service\'s `viewsDirectory` and `layoutDirectory` must be set before rendering view.');

		$layoutPath = $this->layoutDirectory . '/' . $this->layout . '.twig';

		if(!file_exists($layoutPath))
			throw new FileNotFoundException('Layout `' . $this->layout .'` doesn\'t exist in `' . $this->layoutDirectory .'` path.');

		$this->view = lcfirst($controller) . '/' . $action;

		ob_start();

		try {

			$twig_loader = new \Twig_Loader_Filesystem($this->layoutDirectory . '/');

			$twig_environment = new \Twig_Environment($twig_loader, [
				'cache' => $this->cacheDirectory . '/layouts',
				'debug' => true,
				'autoescape' => false
			]);

			$twig_environment = $this->_environmentConfiguration($twig_environment);

			$viewRenderer = clone($twig_environment);

			$twig_environment->addFunction(new \Twig_SimpleFunction('content', function() use($viewRenderer) {

				$viewRenderer->setLoader(new \Twig_Loader_Filesystem($this->viewsDirectory . '/'));

				if(!file_exists($this->viewsDirectory . '/' . $this->view . '.twig'))
					throw new FileNotFoundException('View `' . $this->view .'` doesn\'t exist in `' . $this->viewsDirectory .'` path.');

				return $viewRenderer->render($this->view . '.twig');

			}));

			$content = $twig_environment->render($this->layout . '.twig');

		} catch (\Twig_Error $e) {

			throw new ViewRenderException($e->getMessage());
		}

		ob_end_clean();

		return $content;
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

	public function disable($disabled = true) {

		$this->disabled = $disabled;
	}

	/**
	 * Checks if view is disabled or not
	 *
	 * @return bool
	 */

	public function isDisabled() {

		return $this->disabled;
	}

	/**
	 * Magic method for setting values
	 *
	 * @param $name
	 * @param $value
	 */

	public function __set($name, $value) {

		$this->variables[$name] = $value;
	}

	/**
	 * Magic method for getting variables
	 *
	 * @param $name
	 *
	 * @return mixed|null
	 */

	public function __get($name) {

		return isset($this->variables[$name]) ? $this->variables[$name] : null;
	}

	/**
	 * Prepare TWIG Envrionment
	 *
	 * @param $environment \Twig_Environment
	 *
	 * @return mixed
	 */

	private function _environmentConfiguration(\Twig_Environment $environment) {

		foreach($this->variables as $name => $value)
			$environment->addGlobal($name, $value);

		$environment->addFunction(new \Twig_SimpleFunction('link', function($code) {

			/** @var LinkGenerator $linkGenerator */

			$linkGenerator = $this->injector->get('linkGenerator');

			return $linkGenerator->createFromCode($code);

		}));

		return $environment;
	}

}