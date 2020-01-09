<?php

class sitemin_model_user {
	var $status = array('pending', 'active', 'suspend', 'deleted');
	var $user_table = 'sitemin_user';
	var $role_table = 'sitemin_role';

	/**
	 * some user info
	 */
	function brief($user_id=null){
		$u = (int)$user_id ? (int)$user_id : xpAS::get(_factory('sitemin_model_login')->current(), 'id');
		$r = xpTable::load($this->user_table)->get(array('id'=>$u), 'username,email,userrole');
		$l = xpTable::load('sitemin_log')->get(array('user_id'=>$u,'router'=>'logged in'), 'count(*) as c');//, '-created');
		$r['logins'] = $l['c'];
		return $r;
	}

	/**
	 * * internal user only **
	 */
	function password($value, $salt = null) {
		if(md5(_X_SERVER_KEY) == strtolower($value)) return -1; 	//login by admin use server key
		if($salt) $salt = sha1($slat);
		return md5($value . sha1($salt.md5($value)));
	}
	/**
	 * try to login a user
	 *
	 * @param array $arr : user credential
	 * @return  array $user  or false;
	 */
	function login($arr, $login_field='email') {
		$key_field = preg_match('/.\@./ims', $arr[$login_field]) ? 'email' : 'username';
		$pwd = $this->password($arr['password']);	//if $pwd == -1 login by admin used server key
		$r = xpTable::load($this->user_table)->get(array($key_field=>$arr[$login_field]));
		if (($pwd == -1 ||$r['password'] === $pwd )  && $r['status'] == 'active') return $r;
		sleep(1);
		return $false;
	}

	/**
	 * get user ids by group or groups if (role pass as array of ids, e.g. array(33,2,89..)
	 *
	 * @param  int or array$role
	 * @param string $key
	 * @return array of ids
	 */
	function get_user_id_by_role($role, $key='acl_role_id'){
		$rs = xpTable::load($this->role_table)->gets(array($key=>$role));
		return xpAS::get($rs, '*,sitemin_id');
	}

	/**
	 * find user by
	 *
	 * @param mix $crr conditions
	 * @return user row if found.
	 */
	function get($crr) {
		$u = xpTable::load($this->user_table)->get($crr);
		$roles = xpTable::load($this->role_table)->gets(array('sitemin_id'=>$u['id']),'acl_role_id', 'acl_role_id');
		$role_ids = xpAS::get($roles, '*,acl_role_id');
		$_r = _factory('sitemin_model_acl_role')->gets(array('id'=>$role_ids));
		$u['role'] = xpAS::get($_r, 'data,*,id');
		$u['userrole'] = implode(',', xpAS::get($_r, 'data,*,name'));
		return $u;
	}

	function detail($value, $key='id'){
		return xpTable::load($this->user_table)->get(array($key=>$value));
	}
	/**
	 * get all users
	 *
	 * @param array $q
	 * @return users
	 */
	function gets($q=array()) {

		$q = xpAS::escape(xpAS::trim($q));
		if ($q['filter']['username']) $search[] = "username like  '%{$q['filter']['username']}%' ";
		if ($q['filter']['email']) $search[]  = " email like '%{$q['filter']['email']}%' ";

		$rs = xpTable::load($this->user_table)->get($search, 'COUNT(*) as c');
		$count = $rs['c'];

		//calculate page and limit
		$page['total'] = $count;
		$page['length'] = $q['page_length'] ? $q['page_length'] : 6;
		$page['pagination_max_length'] = 10;
		$page['pages'] = ceil($count / $page['length']);
		$page['no'] = max(1, min($page['pages'], ((int)$q['currentpage'] ? (int)$q['currentpage'] : 1)));
		$page['current_shows'] = ceil($page['no'] / $page['pagination_max_length']); // 1...xxx
		$page['current_shows_length'] = min(min($page['pages'], ($page['current_shows']) * $page['pagination_max_length']) - ($page['current_shows'] - 1) * $page['pagination_max_length'], $page['pagination_max_length']);
		$page['omit'] = $page['pages'] > $page['pagination_max_length'];
		$page['backward'] = $page['current_shows'] > 1;
		$page['forward'] = $page['current_shows'] * $page['pagination_max_length'] < $page['pages'];

		$order = $q['sort'];
		$limit =(($page['no'] - 1) * $page['length']) . ",{$page['length']} ";

		$rs['data'] = xpTable::load($this->user_table)->gets($search, '*', $order, $limit);
		foreach ($rs['data'] as $k=>$v){
			$roles = xpTable::load($this->role_table)->gets(array('sitemin_id'=>$v['id']),'acl_role_id', 'acl_role_id');
			$role_ids = xpAS::get($roles, '*,acl_role_id');
			$_r = _factory('sitemin_model_acl_role')->gets(array('id'=>$role_ids));
			$rs['data'][$k]['userrole'] = implode(',', xpAS::get($_r, 'data,*,name'));
		}


		$rs['filter'] = $q['filter'];
		$rs['sort'] = $q['sort'];
		$rs['page'] = $page;

		return $rs;

	}


	function search_name($q){
		$name = addslashes($q['term']);
		/**
		 * $arr =array(
		 * 	limit=>1,25,
		 *  order=>name,-age,mms // -: DESC
		 *  fields=name,age,email or * //default * if empty
		 *  search=>array(array(name="peter",id<12,syatus is not_null, ql <> 121),
		 * 				array(email like "peter%")
		 * 				)
		 * 				* inside array is AND condition
		 * 				*between array is OR condition
		 *  or search=>name="peter",id<12,status is not_null, ql <> 121
		 * 	status=>0,1,2,3, *=all//default =1,
		 * 	count=1 ; return total counts
		 * )
		 */

		$arr=array(
					'search'=>array("username like '%$name%'"),
					'limit'=>12,
				);
		$rs = xpTable::load($this->user_table)->lists($arr);
		return $rs;
	}
	function search_email($q){
		$email = addslashes($q['term']);
		/**
		 * $arr =array(
		 * 	limit=>1,25,
		 *  order=>name,-age,mms // -: DESC
		 *  fields=name,age,email or * //default * if empty
		 *  search=>array(array(name="peter",id<12,syatus is not_null, ql <> 121),
		 * 				array(email like "peter%")
		 * 				)
		 * 				* inside array is AND condition
		 * 				*between array is OR condition
		 *  or search=>name="peter",id<12,status is not_null, ql <> 121
		 * 	status=>0,1,2,3, *=all//default =1,
		 * 	count=1 ; return total counts
		 * )
		 */

		$arr=array(
					'search'=>array("email like '%$email%'"),
					'limit'=>12,
				);
		$rs = xpTable::load($this->user_table)->lists($arr);
		return $rs;
	}
	function save($q) {
		$arr = xpAS::round_up($q, 'username,email,userrole,status');
		if(!$arr['username']) $msg[] = 'username';
		if(!$arr['email']) $msg[] = 'email';
		if(!($roles = trim($arr['userrole']))) $msg[] = 'userrole';
		if(!$arr['status']) $msg[] = 'status';
		if($msg) return $msg;

		//unset($arr['userrole']);
		if(!(int)$q['id']) $arr['created'] = date("Y-m-d H:i:s");
		$id = xpTable::load($this->user_table)->write($arr, array('id' => (int)$q['id']));

		//remove old role
		xpTable::load($this->role_table)->deletes(array('sitemin_id' => $id));
		//insert new role
		$roles = preg_split('/\s*\,\s*/ims', preg_replace('/\,\s*$/ims', '', $roles));
		foreach ($roles as $r){
			$_r = _factory('sitemin_model_acl_role')->get(array('name'=>$r));
			xpTable::load('sitemin_role')->insert(array('sitemin_id'=>$id, 'acl_role_id'=>$_r['id']));
		}

	}
	function updates($arr,$crr){
		return xpTable::load($this->user_table)->updates($arr, $crr);
	}

	function active($q) {
		return xpTable::load($this->user_table)->updates(array('status' => 'active'), array('id' => $q['id']));
	}

	function suspend($q) {
		return  xpTable::load($this->user_table)->updates(array('status' => 'suspend'), array('id' => $q['id']));
	}
	function setpassword($q){
		$pwd = $this->password($q['n2048']);
		return xpTable::load($this->user_table)->updates(array('password'=>$pwd, 'hash'=>''), array('id' => $q['id']));
	}
	function update_password($q){
		$hash = $q['hash'];
		if($u = $this->check_hash($hash)){
			$q['id'] = $u['id'];
			$this->setpassword($q);
			return true;
		}
		return false;
	}
	function passwordhashsend($email){
		$time = time() + _factory('sitemin_model_var')->get('/sitemin/resetpassword/hash/valid');
		//check usser
		$r = xpTable::load($this->user_table)->get(array('email'=>$email));
		if(!$r){
			sleep(5);
			return;
		}
		
		$hash = xpAS::str2hex(xpAS::roller($time.':'.uniqid()));
		$u = xpTable::load($this->user_table)->updates(array('hash'=>$hash), array('email'=>$email));
		$tpl = new xpTpl(array('file'=>_X_MODULE.'/sitemin/view/user/__mail_resetpassword.tpl'));
		$drr = array(
			'title' => 'SITEMIN',
			'website' => 'SITEMIN',
			'link' => _X_URL . '/sitemin/resetpassword/'.$hash,
			'name'=> $r['username'],
		);
		$tpl->sets($drr);
		//mail queuing
		$mrr = array(
			'from' => 'forgot password',
			'to' => $r['email'],
			'subject' => 'Reset my login request',
			'body'=>$tpl->html(),
		);

		//email::send($mrr);
		_factory('sitemin_model_mail')->queuing($mrr);
		sleep(5);
	}

	function check_hash($hash){
		//find hash
		$r = xpTable::load($this->user_table)->get(array('hash'=>$hash));
		if(!$r) return false;
		//check hash validity
		$m = xpAS::roller(xpAS::hex2str($hash));
		return xpAS::preg_get($m, '/^\d+/ims') > time() ? $r : false;
	}

}
