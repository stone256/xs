<?php
/**
 * This is only core file of the framework.
 * @author peter wang<stone256@hotmail.com>
 * @copyright MIT
 *
 * framework x application class
 *
 */
//load  config and setting (global and *local)

//load system config
require_once (__DIR__. '/../config/.system.php');

//load framework global config
require_once (_X_CONFIG . '/general.php');

//loading local setting
include _X_CONFIG . '/local.php';

//package's auto loader  + psr auto loader
require_once (_X_PACKAGE. '/loader.php');

//load default class
include_once (__DIR__ . '/defaultLoader.php');
include_once (__DIR__ . '/defaultHelper.php');
include_once (__DIR__ . '/defaultController.php');

//routing to controllers
class app {
	var $profiler_data;
	var $model_path;
	var $routers = array();

	static $_request_url = '';
	static $_url ='';
	static $_mapper ='';
	static $_controller ='';
	static $_overwrite=[];
	var $update_scripts=array();

	static $models = array();

	//init
	function __construct() {
		$this->helper = new defaultHelper();
		//load routers
		//1>system routers (only routers are inclued in system routers)
		global $routers, $modules;
		$this->routers = (array)$routers;
		//load customer routers, looking into each enabled modules for router.php
		foreach ($modules as $km => $vm) {
			$routers=array();
			//load router.php
			$router_file = _X_MODULE . $vm . DS .'.router.php';
			if (file_exists($router_file)) {
				include_once ($router_file);
				$this->routers += (array)$routers;
			}
			//load modules update scritps, if any
			$us = glob(_X_MODULE . $vm. DS . '.setup.*.php');
			sort($us);
			$this->update_scripts = $this->update_scripts + $us;
		}
		$routers = $this->routers;
		    
	}
	//when 404 happened
	function at_404($v){
		$missing = $v;
		include _X_404_PAGE !== '_X_404_PAGE' ? _X_404_PAGE : _X_LAYOUT.'/_404.phtml';
		die();

	}

	/**
	 * start x application
	 */
	function run() {
		//get requested url path
        	self::$_request_url = $this->_get_url();

		//change url to custom one , if has one
		list(self::$_url, self::$_mapper) = $this->_get_custom_router();
		//find controller
		$router = $this->find_controller(self::$_url, self::$_mapper);
		//additional params
		$_REQUEST = xpAS::merge($router['query'], $_REQUEST);



		self::$_controller = $router['controller'];
		//load controller file
		include_once ($router['file']);

		//init control
		$controller = new $router['controller']($router);
		if (!method_exists($controller, $action = $router['action'])) $this->at_404("missing action : {$controller}->{$action}");
		include_once (_X_CONFIG . DS .'init.php');

		//start modules update scripts, if any.
		foreach ((array)$this->update_scripts as $k=>$v){
			include_once $v;
			rename($v , $v.".done");
		}

		if (!is_array($ret = $controller->$action($router))) die($ret); //none standard controller return ! not to handle, directly output
		$name = preg_replace('/Action$/', '', $action);
		$_v = $ret['view'] ? $ret['view'] : $name . '.phtml';

		switch (true) {
				//FOR _SYSTEM

			case $ret['view'] {0} . $ret['view'] {1} == DS .'_':
				$view = _X_ROOT . $_v;
			break;
				//FOR ALL OTHER

			case $ret['view'] {0} == DS :
				$view = _X_MODULE . $_v;
			break;
				//FOR DEFAULT WITH PATH, IT USE CONTROLLER POSITION AS GUIDE

			default:
				$view = dirname($router['file']) . DS .'view' . DS . xpAS::preg_get($router['controller'], '|_([a-zA-Z0-9]*?)Controller|', 1) . DS . $_v;
		}
		$viewData = $ret['data'];
		$viewData['_controller'] = $router;
		//loading viewer
		if (!file_exists($view)) $this->at_404("missing view: " . str_replace(_X_ROOT, '', $view));
		if (preg_match('/tpl.html/', $view)) {
			$tpl = new xpTpl(array('file' => $view));
			$tpl->sets($viewData);
			$tpl->html(true, 0);
		} else {
			extract($viewData);
			include_once ($view);
		}
		//echo (int)((microtime(1)-$_SERVER['REQUEST_TIME_FLOAT'])*1000)."ms<br/>";
		//ended/stopped here

	}
	/**
	 * find mapped controllers
	 *
	 * 	url start with "/_ ..." : mappping for system/special/internal calls	, from framework's root folder not module's folder
	 *
	 *  controller map
	 * @param string $url
	 * @return array 	array('file'=>'../mywebsite/www/module/xp/testing/date', 'controller'=>'testingController', 'action'=>'dateAction', 'query'=>[..])
	 */
	function find_controller($url, $controller) {
		$xpath = preg_match('/^\/\_/', $controller) ? _X_ROOT : _X_MODULE;
		$_u = explode('@', $controller);
		$controller_file = $xpath . $_u[0] . 'Controller.php';
		$controller_class = str_replace('/', '_', substr($_u[0], 1)) . 'Controller';
		$action = $_u[1] . 'Action';
		if (file_exists($controller_file)) {
			$_v = substr(self::$_request_url, strlen($url));
			$_v = preg_replace('/(^\/|\/$)/', '', $_v);
			$_r = array('url'=>$url, 'file' => $controller_file, 'controller' => $controller_class, 'action' => $action, 'query' => $_v? xpAS::dissolve($_v, '=', '/'): array(),);
			return $_r;
		}
		$this->at_404("missing controller:{$url}-{$controller}");
	}
	/**
	 * find custom router
	 *
	 * @param string $url
	 * @return custom url if has one
	 */
	function _get_custom_router() {
		$_u = preg_split('/\/+/', self::$_request_url);
		while (count($_u)) {
			$_p = str_replace('//', '/', '/' . implode('/', $_u));
			if ($this->routers[$_p]) return array($_p, $this->routers[$_p]);
			array_pop($_u);
		}
        	$this->at_404("missing controller: router mapping");
	}
	/**
	 * get current request uri
	 *
	 * @return return uri
	 */
	function _get_url(){
		$u = $_SERVER['REQUEST_URI'];

		//in case of none encode http:// [ // ] - preg_quote, does not qoute / -
		$q = preg_quote($_SERVER['QUERY_STRING']);
		$q = str_replace('/', chr(92).'/', $q);

		$u = preg_replace('/\?'.$q.'$/', '', $u);
		$u = STR_REPLACE(_X_OFFSET, '', $u);
		return $u;
	}
}
