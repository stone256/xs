<?php

class sitemin_api_model_user {
	var $status = array('pending', 'active', 'suspend', 'deleted');

	function __construct() {
		$this->user_table = 'api_user';
	}

	/**
	 * find api user by
	 *
	 * @param mix $crr conditions
	 * @return user row if found.
	 */
	function get($crr) {
		return xpTable::load($this->user_table)->get($crr);
	}
	/**
	 * get all api users
	 *
	 * @param array $q
	 * @return users
	 */
	function gets($q) {

		$q = xpAS::escape(xpAS::trim($q));

		if($q['filter']['login_id']) $search[] = "login_id like '%{$q['filter']['login_id']}%'";
		if($q['filter']['user_detail']) $search[] = "user_detail like '%{$q['filter']['user_detail']}%'";

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

		$rs['filter'] = $q['filter'];
		$rs['sort'] = $q['sort'];
		$rs['page'] = $page;

		return $rs;
	}
	/**
	 *reset api user password
	 */
	function setpassword($q){
		$rs = xpTable::load($this->user_table)->updates(array('password' => $this->password($q['n2048'])), array('id' => $q['id']));
	}

	function password($s, $salt='$this->user_table'){
		return md5($s.sha1($salt));
	}

	/**
	 *	check if api user login id/name is duplicated
	 */
	function idcheck($q){
		$r = xpTable::load($this->user_table)->get(array('login_id'=>$q['login_id'], " id <> '{$q['id']}'"));
		return $r;
	}

	/**
	 *check login
	 */
	function login_check($id, $pwd){
		$r = xpTable::load($this->user_table)->get(array('login_id'=>$id, 'password'=>$this->password($pwd)));
		return $r;
	}
	/**
	 *suspend api user
	 */
	function active($q) {
		return xpTable::load($this->user_table)->updates(array('status' => 'active'), array('id' => $q['id']));
	}
	/**
	 *set api user status active
	 */
	function suspend($q) {
		return xpTable::load($this->user_table)->updates(array('status' => 'suspend'), array('id' => $q['id']));
	}
	/**
	 *save api user info
	 */
	function save($q) {

		if(!$q['login_id']) $msg[] = 'login id';
		if(!$q['quota']) $msg[] = 'quota';
		if(!$q['quota_start_seconds']) $msg[] = 'quota start';
		if(!$q['quota_period_seconds']) $msg[] = 'quota period';
		$q['quota_start_seconds'] = xpDate::returnUNIXTimestampFromMYSQL($q['quota_start_seconds']);
		$q['created'] = $q['created'] ? $q['created'] : date("Y-m-d H:i:s");
		$arr = xpAS::round_up($q, 'login_id,detail,quota,quota_start_seconds,quota_counter,quota_period_seconds,status,created');
		if($msg) return $msg;
		xpTable::load($this->user_table)->write($arr, array('id' => (int)$q['id']));
	}
	/**
	 *search user by a key
	 */
	function search($value, $key="login_id"){
		$value = addslashes($value);
		$key = addslashes($key);
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
					'search'=>array(" {$key} like '%$value%'"),
					'limit'=>12,
				);
		$rs = xpTable::load($this->user_table)->lists($arr);
		return $rs;
	}

}
