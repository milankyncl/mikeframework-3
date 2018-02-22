<?php


namespace Postmix\Structure;

use Postmix\Injector\Service;

/**
 * Class Router
 * @package Postmix\Structure
 */

class Router extends Service {

	protected $module;

	protected $controller;

	protected $action;

	private $modules;

	private $defaultModule = 'Web';

	public static $urlMap;

	public static $urlParams = [];

	private static $origUrlParam;

	private static $usedDirs;

	/**
	 * UrlResolve
	 *
	 * Získání view a zjištění modulu, controlleru, action
	 *
	 * Nahrazení pomlčky za větší písmeno
	 * -----------------------------------------------------
	 * str_replace('-','',preg_replace_callback('/-[a-z]/',
	 * function ($matches) {
	 * return  strtoupper($matches[0]);
	 * }, lcfirst($folder)));
	 * -----------------------------------------------------
	 */

	public function UrlResolve(){

		if(isset($_GET['_url']))
			$page = '/' . $_GET['_url'];
		else
			$page = '/';

		$map = [];
		foreach(explode('/', $page) as $arg){
			if($arg != ''){
				$map[] = str_replace('-','',preg_replace_callback('/-[a-z]/', function ($matches) {
					return  strtoupper($matches[0]);
				}, lcfirst($arg)));
			}
		}

		if(!empty($map)){

		}else{
			$this->module = $this->defaultModule;
			if(isset($this->modules[$this->defaultModule]['defaultController'])){
				$this->controller = $this->modules[$this->defaultModule]['defaultController'];
			} else {
				$this->controller = 'index';
			}

			if(isset($this->modules[$this->defaultModule]['defaultAction'])){
				$this->action = $this->modules[$this->defaultModule]['defaultAction'];
			} else {
				$this->action = 'index';
			}
		}

	}

	/**
	 * Resolve conficts affected by mixing module, controller, action and action arguments
	 */

	public function UrlResolveConflicts(){
		$used_dirs = 0;
		if($this->module != 'web') $used_dirs += 1;
		if($this->action != 'index') $used_dirs += 1;
		if($this->controller != 'index') $used_dirs += 1;
		$params = array();
		self::$usedDirs = $used_dirs;
		for($i = $used_dirs; $i < count(self::$urlMap); $i++){
			$params[] = self::$urlMap[$i];
		}
		self::$urlParams = $params;
	}


	/**
	 * Set application modules
	 *
	 * @param Array $modules
	 */

	public function registerModules( $modules ){
		$registered = [];

		foreach($modules as $name => $module){
			$registered[$name] = $module;
			if(isset($module['defaultModule']) && $module['defaultModule'] == true){
				$this->defaultModule = $name;
			}
		}

		/**
		 * Set the first one if deafultModel wasn't set
		 */
		if(!isset($this->defaultModule)) $this->defaultModule = key($registered);

		$this->modules = $registered;
	}

	/**
	 * Returns rewrited URI
	 *
	 * @return string
	 */

	public function getRewriteUri(){
		return str_replace(APP_PATH, '', $_SERVER["REQUEST_URI"]);
	}

	/**
	 *
	 */

	private function hasAction($module, $controller, $action){
		require_once(APP_PATH . '/modules/' . $module . 'Module/controllers/' . $controller . 'Controller.php');
		$rc = new \ReflectionClass($controller . 'Controller');
		if($rc->hasMethod($action . 'Action')){
			return true;
		}
		return false;
	}

	/**
	 * Check if module, controller, action is equal to router
	 *
	 * @param $module
	 * @param bool $controller
	 * @param bool $action
	 *
	 * @return bool
	 */

	public function is($module, $controller = false, $action = false){
		if($module and $controller and $action){
			if($module == $this->module and $controller == $this->controller and $action == $this->action)
				return true;
		} else if($module and $controller){
			if($module == $this->module and $controller == $this->controller)
				return true;
		} else {
			if($module == $this->module)
				return true;
		}
		return false;
	}

	/**
	 * @return string
	 */

	public function getController(){
		return $this->controller;
	}

	/**
	 * @return string
	 */


	public function getAction(){
		return $this->action;
	}

	/**
	 * @return string
	 */

	public function getModule(){
		return $this->module;
	}

}