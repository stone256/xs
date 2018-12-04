<?php
class sitemin_userController extends sitemin_indexController {

	function profileAction(){
		$q = $_REQUEST;
		switch($q['cmd']){
			case 'brief':
				$r = _factory('sitemin_model_user')->brief();
				if(!$r['email']) {
					$r['username'] = base64_decode($r['username']);
					$r['email'] = "Login from other platform";
				}
				$rs = array($r);
				die(json_encode($rs));
		}
	}

	function messageAction(){
		$q = $_REQUEST;
		switch($q['cmd']){
			case 'lead':
				$rm = _factory('sitemin_model_message')->lead();
				die(json_encode($rm));
			case 'list':
				$rm = _factory('sitemin_model_message')->lists();
				$rm = $rm ? $rm : array(array('From'=>'', 'Message'=>'', 'Date'=>'', 'Viewed'=>''));
				die(json_encode($rm));
			case 'delete':
					$rm = _factory('sitemin_model_message')->delete($q);
					die('ok');
		}

	}

	function listAction() {
		$q = $_REQUEST;
		$rs = _factory('sitemin_model_user')->gets($q);
		$rs['tpl'] = 'user/_user.phtml';
		$rs['_token'] = defaultHelper::page_hash_set('sitemin,user,list');
		$rs['TITLE'] = 'SITEMIN USER LIST';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

	function suspendAction() {
		$q = $this->q;
		defaultHelper::return_url();
		$_token = defaultHelper::page_hash_get('sitemin,user,list');
		if($q['_token'] == $_token && $q['id']){
			_factory('sitemin_model_user')->suspend($q);
		}
		return defaultHelper::return_url(true);

	}
	function activeAction() {
		$q = $this->q;
		defaultHelper::return_url();
		$_token = defaultHelper::page_hash_get('sitemin,user,list');
		if($q['_token'] == $_token && $q['id']){
			_factory('sitemin_model_user')->active($q);
		}
		return defaultHelper::return_url(true);
	}
	function passwordAction(){
		$q = $this->q;

		defaultHelper::return_url();
		$_token = defaultHelper::page_hash_get('sitemin,user,list');
		if($q['_token'] == $_token && $q['id']){
			_factory('sitemin_model_user')->setpassword($q);
		}
		die('done');
	}


	function editAction() {

		$q = $this->q;
		$q['_return'] = $q['_return'] ? $q['_return'] : defaultHelper::return_url();
		$_token = defaultHelper::page_hash_get('sitemin,user,edit');
		$rs = array();
		if($q['_token'] == $_token &&  $q['save'] && !($err = _factory('sitemin_model_user')->save($q))) return defaultHelper::return_url(true);

		$q['_msg'] = $err ? implode(',', $err) : null;
		if((int)$q['id']){
			$rs = _factory('sitemin_model_user')->get(array('id'=>$q['id']));
		}
		$rs = xpAS::merge($rs, $q);
		$rs['_token'] = defaultHelper::page_hash_set('sitemin,user,edit');

		$rs['status_options'] = _factory('sitemin_model_user')->status;
		$rs['tpl'] = 'user/_edit.phtml';
		$rs['TITLE'] = 'SITEMIN USER EDIT';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}


	function searchAction($q){
		$q = $_REQUEST;
		switch(true){
			case isset($q['name']) :
				$r = _factory('sitemin_model_user')->search_name($q);
				$cl = 'username';
				break;
			case isset($q['email']) :
				$r = _factory('sitemin_model_user')->search_email($q);
				$cl = 'email';
				break;
		}

		$r = xpAS::get($r, "rows,*,$cl");

		die(json_encode($r));
	}

}
