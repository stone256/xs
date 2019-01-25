<?php
class testing_indexController extends _system_defaultController {

	function indexAction(){
	
	  echo   "<h1>testing_model_test over write by testing_model_testa</h1>"; 
	    _factory('testing_model_test', 1, 2,8)->t();
	    
	    die("<hr>");
		$data['rs']['controller'] = _rp(__FILE__);
		/** create database
		global $cfg;
		$cfg['db'] = array( //for testing database
				'user'	=> 'data',
				'host'		=>'localhost',
				'password'	=> 'datadatadata',
			//'database'	=> 'data_p',
		);
		);
		$db = "data_ttt";
		$r = xpPdo::conn()->db_create($db);
		$r = xpPdo::conn()->db_select($db);
		**/

		return array('data'=>$data);
	}

	function myipAction() {
		$more = $_REQUEST['more'];
		$myip = xpAS::get_client_ip();
		$rs['data'] = array('myip'=>$myip, 'server'=>$_SERVER, 'cookie'=>$_COOKIE);
		if($more){
			print_r($rs['data']);
			exit;
		}
		if(!$u['id']) die($myip);
	}

}
