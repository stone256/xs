<?php
/**
 * _X_CRON_SYSTEN = true to allow system command.
 */
define('_X_CRON_SYSTEN', true);

class sitemin_cronController extends sitemin_indexController {

	function indexAction(){
		$q = $this->q;

		$token_name = 'sitemin,cron';

		$_token = defaultHelper::page_hash_get($token_name);
		if($q['cmd'] && $q['_token']){
			switch($q['cmd']){
				case 'list':
					$r = _factory('sitemin_model_crontab')->gets();
					die(json_encode($r));
					break;
				case 'status_toggle':
					$r = _factory('sitemin_model_crontab')->status_toggle($q);
					die(json_encode($r));
					break;
				case "save":
					$r = _factory('sitemin_model_crontab')->save($q);
					die(json_encode($r));
					break;
			}
		}
		$rs['_token'] = defaultHelper::page_hash_set($token_name);
		$rs['tpl'] = '_cron.phtml';
		$rs['TITLE'] = 'SITEMIN CRON';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

	function runAction(){

		_factory('sitemin_model_crontab')->trigger();
	}
}
