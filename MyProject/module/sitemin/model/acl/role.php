<?php
class sitemin_model_acl_role {
	function __construct() {
		$this->table = 'acl_role';
	}
	/**
	 * find user by
	 *
	 * @param mix $crr conditions
	 * @return user row if found.
	 */
	function get($crr) {
		return xpTable::load($this->table)->get($crr);
	}
	function gets($crr=null) {
		$rs['data'] = xpTable::load($this->table)->gets($crr, '*', 'id');
		return $rs;
	}
	function update($q) {
        $q['id'] = (int)$q['id'];
        //do not edit 1=public and 9000=sitemin
		if(!$q['save'] || $q['id'] == 1 || $q['id'] == 9000) return $rs = xpTable::load($this->user_table)->get(array('id' => $q['id']));
		$arr = xpAS::round_up($q, 'name,description');
		xpTable::load($this->user_table)->write($arr, array('id' => $q['id']));
		xpAS::go('/admin/acl/list_role');
	}
	function delete($q) {
        $q['id'] = (int)$q['id'];
        //do not edit 1=public and 9000=sitemin
        if($q['id'] == 1 || $q['id'] == 9000) return ;
		$rs = xpTable::load($this->table)->deletes(array('id' => $q['id']));
	}

	function search($q){
		return $rs =  xpTable::load($this->table)->gets(array("name like '{$q['term']}%'" ),'name');
	}

	function save($q) {
        $q['id'] = (int)$q['id'];
        //do not edit 1=public and 9000=sitemin
        if($q['id'] == 1 || $q['id'] == 9000) $msg['forbidden'];
		$arr = xpAS::round_up($q, 'name,description');
		if(!$arr['name']) $msg[] = 'name';
		if(!$arr['description']) $msg[] = 'description';

		if($msg) return $msg;
		xpTable::load($this->table)->write($arr, array('id' => $q['id']));
	}


}
