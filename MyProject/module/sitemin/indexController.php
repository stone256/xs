<?php

/**
 * default sitemin controller
 *
 */
class sitemin_indexController extends _system_defaultController {
	var $sitemin_menu;
	var $q;
	var $router;
	function __construct(){
		$this->q = $_REQUEST;
		session_start();
		$_p = _url();
		$u = defaultHelper::data_get('sitemin,user');
		if(!($router = $u['router'])){
			//defaultHelper::data_get('sitemin,user,router');
			$menu = _factory('sitemin_model_acl_menu')->allowed($u['userrole']);
			defaultHelper::data_set('sitemin,user,menu', $menu);
			$router = _factory('sitemin_model_acl_router')->allowed($u['userrole']);
			defaultHelper::data_set('sitemin,user,router', $router);
			$this->router = $router;
		}
		if(_X_CLI_CALL !== true && !$router[$_p]) xpAS::go('/sitemin/login');
		$this->sitemin_menu = _factory('sitemin_model_acl_menu')->tree();
		if(_factory('sitemin_model_var')->get('sitemin/log') && $_p != '/sitemin/keepalive') _factory('sitemin_model_log')->insert();
	}

	function statusAction(){
		$q = $this->q;
		$token_name = 'sitemin,status';
		//$_token = defaultHelper::page_hash_get($token_name);
		switch($q['cmd']){
			case 'users':
			    $r = _factory('sitemin_model_user')->gets();
				echo $r['page']['total'];
				die();
			case 'login24':
				echo count(_factory('sitemin_model_log')->login24());
				die();
            case 'last24':
                $rs = _factory('sitemin_model_log')->last24();
                die(json_encode($rs));
			case 'top10url':
				$rs = _factory('sitemin_model_log')->top10url();
				die(json_encode($rs));
			case 'top5url':
				$rs = _factory('sitemin_model_log')->top5url();
				die(json_encode($rs));
		}
		die();
	}


	function keepaliveAction(){
		//do nothing just to be called and keep session alive
	}



}
