<?php
class examples_examplesController extends _system_defaultController {


	function indexAction() {

		global $cfg;

		$menu = _factory('examples_model')->get_menu();
		$rs['menu'] = $menu;
		$rs['root'] = _factory('examples_model')->path;
		$_REQUEST['in'] = $_REQUEST['in'] ? $_REQUEST['in'] : str_replace($rs['root'], '', $menu[0]['content']);
		return array('data' => array('rs' => $rs));
	}
	function bladeAction(){
		if (_X_VENDOR_PSR !== true) {
			die('Please enabled vendor psr @ package/loader.php');
		}
		$blade = new examples_blade(_X_MODULE . '/examples/view/blade/');
		$blade->run('sub/bar', null, array('controller'=>__CLASS__."::".__FUNCTION__));
		exit;
	}
}
