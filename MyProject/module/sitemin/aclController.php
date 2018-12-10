<?php

class sitemin_aclController extends sitemin_indexController {

//	function listAction(){
//		$rs = _factory('sitemin_model_user')->gets();
//		$rs['tpl'] = '_acl.phtml';
//		$rs['_token'] = defaultHelper::page_hash_set('sitemin,acl,list');
//		return array('view'=>'/sitemin/view/user/index.phtml','data' => array('rs' => $rs));
//	}

	function menutreeitemmove2Action(){
//return;
		$q = $this->q;
		$_token = defaultHelper::page_hash_get('sitemin,acl,menu,list');
		if($q['_token'] == $_token){
			$msg = _factory('sitemin_model_acl_menu')->move($q);
		}else{
			$msg[] = 'Session expired!';
			$msg[] = 'Please refresh browser, and try again';
		}
		$r = array('status'=>$msg ? 'failed' : 'success', 'msg'=>$msg,);
		echo json_encode($r);
	}

	function menutreeitemmove1Action(){
//return;
		$q = $this->q;
		$q['parent_id'] = $q['new_id'];
		$_token = defaultHelper::page_hash_get('sitemin,acl,menu,list');
		if($q['_token'] == $_token){
			$msg = _factory('sitemin_model_acl_menu')->move($q);
		}else{
			$msg[] = 'Session expired!';
			$msg[] = 'Please refresh browser, and try again';
		}
		$r = array('status'=>$msg ? 'failed' : 'success', 'msg'=>$msg,);
		echo json_encode($r);
	}


	function menutreeitemdeleteAction(){
		$q = $this->q;
		$_token = defaultHelper::page_hash_get('sitemin,acl,menu,list');
		if($q['_token'] == $_token && $q['id']){
			$msg = _factory('sitemin_model_acl_menu')->delete($q['id']);
		}else{
			$msg[] = 'Session expired!';
			$msg[] = 'Please refresh browser, and try again';
		}
		$r = array('status'=>$msg ? 'failed' : 'success', 'msg'=>$msg,);
		echo json_encode($r);

	}

	function menutreeitemsaveAction(){
		$q = $this->q;
		$_token = defaultHelper::page_hash_get('sitemin,acl,menu,list');
		if($q['_token'] == $_token){
			$msg = _factory('sitemin_model_acl_menu')->save($q);
		}else{
			$msg[] = 'Session expired!';
			$msg[] = 'Please refresh browser, and try again';
		}
		$r = array('status'=>$msg ? 'failed' : 'success', 'msg'=>$msg,);
		echo json_encode($r);
	}

	function menutreeAction(){
		//$rs['data'] =  $this->sitemin_menu; //_factory('sitemin_model_acl_menu')->tree();
		$rs['data'] =  _factory('sitemin_model_acl_menu')->tree();
		$rs['router'] = defaultHelper::data_get('sitemin,user,router');// $this->sitemin_router ;//_factory('sitemin_model_acl_router')->gets();
		$rs['tpl'] = 'acl/_menutree.phtml';
		$rs['_token'] = defaultHelper::page_hash_set('sitemin,acl,menu,list');
		$rs['TITLE'] = 'SITEMIN MENU TREE';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

	function routerchangeAction(){
		$_token = defaultHelper::page_hash_get('sitemin,acl,router,listt');
		if($q['_token'] != $_token) die('err');
		$r = _factory('sitemin_model_acl_router')->change($this->q);

		// relogin
		$useremail =  defaultHelper::data_get('sitemin,user,email');
		$v = xpCaptcha::generate(array('length'=>6, 'dot'=>array('x'=>8,'y'=>8)), false);
		$vcode = $v['name'];
		$brr = array(
					 'email'=>$useremail,
					 'vcode'=>$vcode,
					 'password'=>md5(_X_SERVER_KEY),
					 );
		_factory('sitemin_loginController')->_login($brr);
		echo (int)$r;
	}

	function routersearchAction(){
	 	$rs = _factory('sitemin_model_acl_router')->search($this->q);
	 	//$rs = xpAS::get($rs, '*,router');
	 	echo json_encode($rs);
	}

	function routerAction(){
		$ds = _factory('sitemin_model_acl_router')->gets('clean old');
		ksort($ds);
		$rs['data'] = $ds;
		$rs['roles'] = xpas::get(_factory('sitemin_model_acl_role')->gets(), 'data,*,name');
		$rs['tpl'] = 'acl/_router.phtml';
		$rs['_return'] = defaultHelper::return_url();
		$rs['_token'] = defaultHelper::page_hash_set('sitemin,acl,router,list');
		$rs['TITLE'] = 'SITEMIN ROUTER';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

	function roleAction(){
		$rs = _factory('sitemin_model_acl_role')->gets();
		$rs['tpl'] = 'acl/_role.phtml';
		$rs['_token'] = defaultHelper::page_hash_set('sitemin,acl,role,list');
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

	function roledeleteAction(){
		$q = $this->q;
		defaultHelper::return_url();
		$_token = defaultHelper::page_hash_get('sitemin,acl,role,list');
		if($q['_token'] == $_token && $q['id']){
			_factory('sitemin_model_acl_role')->delete($q);
		}
		return defaultHelper::return_url(true);
	}


	function roleeditAction() {

		$q = $this->q;
		$q['_return'] = $q['_return'] ? $q['_return'] : defaultHelper::return_url();
		$_token = defaultHelper::page_hash_get('sitemin,alc,role,edit');
		$rs = array();
		if($q['_token'] == $_token &&  $q['save'] && !($err = _factory('sitemin_model_acl_role')->save($q))) return defaultHelper::return_url(true);

		$q['_msg'] = $err ? implode(',', $err) : null;
		if((int)$q['id']){
			$rs = _factory('sitemin_model_acl_role')->get(array('id'=>$q['id']));
		}
		$rs = xpAS::merge($rs, $q);
		$rs['_token'] = defaultHelper::page_hash_set('sitemin,alc,role,edit');
		$rs['tpl'] = 'acl/_roleedit.phtml';
		$rs['TITLE'] = 'SITEMIN ROLE EDIT';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}

	function rolesearchAction(){
		$q= $this->q;
		$r = _factory('sitemin_model_acl_role')->search($q);
		$r = xpAS::get($r, '*,name');
		die(json_encode($r));
	}

}
