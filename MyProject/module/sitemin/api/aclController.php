<?php

/**
 * default api controller
 *
 */
class sitemin_api_aclController extends sitemin_indexController {

	/**
	 *list all api's ACL
	 */
	function indexAction(){
		$q = $_REQUEST;
		$rs = _factory('sitemin_api_model_acl')->gets($q);
		$rs['tpl'] = '/sitemin/api/view/_api_acl.phtml';
		$rs['_token'] = defaultHelper::page_hash_set('api,acl');
		$rs['TITLE'] = 'API ACL';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

	/**
	 *edit user ACL
	 **/
	function editAction(){
		$q = $_REQUEST;

		$ret = '/api/acl';
		//caller url

		if(!$q['uid']) xpAS::go($ret);

		$rs['_return'] = $q['_return'] ? $q['_return'] : defaultHelper::return_url();

		//csrf token
		$_token = defaultHelper::page_hash_get('api,acl,edit');
		if($q['_token'] == $_token &&  $q['save'] && !($err = _factory('sitemin_api_model_acl')->saves($q))) return defaultHelper::return_url(true);

		$q['_msg'] = $err ? implode(',', $err) : null;


		$rs['gateway'] = _factory('sitemin_api_model_api')->get_gateway();
		$rs['user'] = _factory('sitemin_api_model_acl')->get($q['uid']);

		$rs['user']['url'] = preg_split('/\,/', $rs['user']['url']);

		$rs['_token'] = defaultHelper::page_hash_set('api,acl,edit');
		$rs['tpl'] = '/sitemin/api/view/_api_acl_edit.phtml';
		$rs['TITLE'] = 'API ACL EDIT';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}


}
