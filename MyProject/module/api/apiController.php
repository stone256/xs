<?php

/**
 * default api controller
 *
 */
class api_apiController extends sitemin_indexController {



	/**
	 *list all api's gateway urls
	 */
	function listAction(){
		$existing_api = _factory('api_model_api')->get_gateway();
		$rs['api'] = $existing_api;
		$rs['_token'] = defaultHelper::page_hash_set('api,list');
		$rs['tpl'] = '/api/view/_api_list.phtml';
		$rs['TITLE'] = 'API LIST';
		return array('view'=>'/sitemin/view/index.phtml', 'data' => array('rs' => $rs));
	}

	/**
	 * search api list for ajax autocompleting
	 */
	function searchAction(){
		$q = $_REQUEST;
		$r = _factory('api_model_api')->search($q['term'], $q['type']);
		$r = xpAS::get($r, "rows,*,{$q['type']}");
		die(json_encode($r));
	}

}
