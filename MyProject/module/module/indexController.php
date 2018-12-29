<?php

/**
 * depend on sitemin module
 */

class module_indexController extends sitemin_indexController {
       function controlAction() {

	   	$q = $this->q;
		$token_name = 'module,control';
		$_token = defaultHelper::page_hash_get($token_name);
		if($q['cmd'] && $q['_token']==$_token){
		    switch($q['cmd']){
			    case 'list':
				    $r = _factory('module_model')->gets();
				    die(json_encode($r));
			    case "status":
				    $r = _factory('module_model')->status($q);
				    die(json_encode($r));
		    }
		}
		$rs['_token'] = defaultHelper::page_hash_set($token_name);
		$rs['tpl'] = '/module/view/_control.phtml';
		$rs['TITLE'] = 'MODULE CONTROL';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
       }
}
