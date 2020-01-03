<?php

/**
 * default api controller
 *
 */
class sitemin_api_userController extends sitemin_indexController {


	/*
	 *	list api users
	 */
	function indexAction(){
		$rs = _factory('sitemin_api_model_user')->gets($this->q);
		$rs['tpl'] = '/sitemin/api/view/_api_user.phtml';
		$rs['_token'] = defaultHelper::page_hash_set('api,user,list');
		$rs['TITLE'] = 'API USER';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

	/**
	 * reset api user password
	 */
	function passwordAction(){
		$q = $this->q;
		$_token = defaultHelper::page_hash_get('api,user,list');
		if($q['_token'] == $_token && $q['id']){
			_factory('sitemin_api_model_user')->setpassword($q);
		}
		die('done');
	}

	/**
	 *change user status
	 */
	function status_changeAction() {
		$q = $this->q;
		$_token = defaultHelper::page_hash_get('api,user,list');
		if($q['_token'] == $_token && $q['id']){
			$r =  $q['status'] == 'active' ? _factory('sitemin_api_model_user')->active($q) : _factory('sitemin_api_model_user')->suspend($q);
			die( "ok");
		}
		echo "error..";
	}

	/**
	 * user edit page
	 */
	function editAction() {

		$q = $this->q;
		$q['_return'] = $q['_return'] ? $q['_return'] : defaultHelper::return_url();
		$_token = defaultHelper::page_hash_get('api,user,edit');

		$rs = array();
		if($q['_token'] == $_token &&  $q['save'] && !($err = _factory('sitemin_api_model_user')->save($q))) return defaultHelper::return_url(true);

		$q['_msg'] = $err ? implode(',', $err) : null;

		if((int)$q['id']){
			$rs = _factory('sitemin_api_model_user')->get(array('id'=>$q['id']));
		}

		$rs = xpAS::merge($rs, $q);

		$rs['_token'] = defaultHelper::page_hash_set('api,user,edit');

		$rs['status_options'] = _factory('sitemin_api_model_user')->status;
		$rs['tpl'] = '/sitemin/api/view/_api_user_edit.phtml';
		$rs['TITLE'] = 'API USER EDIT';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

	/**
	 * check duplicate api user login name
	 */
	function idcheckAction(){
		$_token = defaultHelper::page_hash_get('api,user,edit');
		if($this->q['_token'] !== $_token) die("errora");
		die( _factory('sitemin_api_model_user')->idcheck($this->q) ? 'error' : 'ok' );
	}

	/**
	 *search user login name
	 */
	function searchAction($q){
		$q = $_REQUEST;

		$r = _factory('sitemin_api_model_user')->search($q['term'], $q['type']);
		$r = xpAS::get($r, "rows,*,{$q['type']}");
		die(json_encode($r));
	}

}
