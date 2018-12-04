<?php
class sitemin_varController extends sitemin_indexController {

	function indexAction(){
		$q = $this->q;

		$token_name = 'sitemin,var';

		$_token = defaultHelper::page_hash_get($token_name);
		if($q['cmd'] && $q['_token']){
			switch($q['cmd']){
				case 'list':
					$r = _factory('sitemin_model_var')->gets();
					die(json_encode(array_values($r)));
					break;
				case "save":
					$r = _factory('sitemin_model_var')->save($q);
					die(json_encode($r));
					break;
			}
		}
		$rs['_token'] = defaultHelper::page_hash_set($token_name);
		$rs['tpl'] = '_var.phtml';
		$rs['TITLE'] = 'SITEMIN VAR';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

}
