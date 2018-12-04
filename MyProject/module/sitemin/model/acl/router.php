<?php
class sitemin_model_acl_router {
	function __construct() {
		$this->table = 'acl_router';
	}


	function search($q){
		//return xpTable::load($this->table)->gets(array("router like '%{$q['term']}%'"), '*', 'router', 8 );
		$rs = $this->gets();
		$pattern = '/'.str_replace('/', '.', preg_quote($q['term'])).'/ims';
		foreach ($rs as $k=>$v){
			//_dv($v);
			//if(preg_match('/acl/ims', $k)) $names[] = $k;
			if(preg_match($pattern, $k)) $names[] = $k;
		}

		return array_splice($names, 0, 12);

	}

	function gets($clean = false){
		static $_routers;
		if($_routers) return $_routers;
		global $routers;
		$rs = array_flip(array_keys($routers));
		foreach((array)xpTable::load($this->table)->gets() as $k=>$v){
			if(isset($rs[$v['router']])){
				$rs[$v['router']] = $v['role'];
			}else{
				if($clean){
					xpTable::load($this->table)->deletes(['id'=>$v['id']]);
				}
			}
		}
		return $_routers = $rs;
	}

	function change($q){
		$routers = $this->gets();
		if(!isset($routers[$q['router']])) return 0;
		$router = xpTable::load($this->table)->get(array('router'=>$q['router']));
		$roles = array_flip(!$router['role'] || is_numeric($router['role']) ? array() : explode(',', substr($router['role'], 0, -1)));
		if(isset($roles[$q['role']])) unset($roles[$q['role']]);
		else $roles[$q['role']] = 100000;	//avoid array_flip's key value
		$role = $roles ? implode(',', array_flip($roles)).',' : 0;
		$router = xpTable::load($this->table)->write(array('router'=>$q['router'], 'role'=>$role), array('router'=>$q['router']));
		return $roles[$q['role']] ? 1: 0;
	}

	function check($roles, $router){
		$r = xpTable::load($this->table)->get(array('router'=>$router));
		$roles = preg_replace('/\,\s*$/ims', '', $roles);
		$roles = preg_split('/\s*\,\s*/ims', $roles);
		foreach ($roles as $k=>$v){
			if(!($v1 = trim($v))) continue;
			$pattern = '/(^|\,)'.preg_quote($v1).'(\,|$)/ims';
			if(preg_match($pattern, $r['role'])) return true;
		}
		return false;
	}

	function allowed($roles){
		$menu = xpTable::load($this->table)->gets(true, '*', 'router');

		foreach ($menu as $km=>$vm){
			//if(_factory('sitemin_model_acl_router')->check($roles, $vm['router'])) $rr[$vm['router']] = $vm['router'];
			if($this->check($roles, $vm['router'])) $rr[$vm['router']] = $vm['router'];
			if($this->check('public', $vm['router'])) $rr[$vm['router']] = $vm['router'];
		}
		return $rr;
	}

//	function get_public_router(){
//		$router = xpTable::load($this->table)->gets(array("role like '%public%'"), '*', 'router');
//		$router = xpAS::get($router, '*,router');
//		$router = array_combine($router, $router);
//		return $router;
//	}


}
