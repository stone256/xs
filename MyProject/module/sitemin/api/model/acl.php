<?php

class sitemin_api_model_acl {
	function __construct() {
		$this->table = 'api_acl';
	}
	/**
	 * list acl
	 *
	 * @param array $q
	 * @return array of results
	 */
	function gets($q) {
		$q = xpAS::escape(xpAS::trim($q));

		if ($q['filter']['login_id']) $search[] = " u.login_id like  '%{$q['filter']['login_id']}%'  ";
		if ($q['filter']['url']) $search[] = " i.url like '%{$q['filter']['url']}%'  ";
		if($search) $search = "AND ". implode(" AND ", $search);

		if($q['sort']) $order =  "ORDER BY ".($q['sort']{0} =='-' ? substr($q['sort'], 1)." DESC " : $q['sort']);

		//get counter
		$sql = "
			SELECT count(*) as c
			FROM api_user as u
			LEFT JOIN api_acl as a ON a.api_user_id = u.id
			LEFT JOIN api as i ON i.id = a.api_id
			WHERE  1 $search GROUP BY u.id
		";
		$r = xpTable::load('api_acl')->q($sql);
		$count = count($r);

		$rs = $q;
		$page['total'] = $count;
		$page['length'] = $q['page_length'] ? $q['page_length'] : 3;
		$page['pagination_max_length'] = 10;
		$page['pages'] = ceil($count / $page['length']);
		$page['no'] = max(1, min($page['pages'], ((int)$q['currentpage'] ? (int)$q['currentpage'] : 1)));
		$page['current_shows'] = ceil($page['no'] / $page['pagination_max_length']); // 1...xxx
		$page['current_shows_length'] = min(min($page['pages'], ($page['current_shows']) * $page['pagination_max_length']) - ($page['current_shows'] - 1) * $page['pagination_max_length'], $page['pagination_max_length']);
		$page['omit'] = $page['pages'] > $page['pagination_max_length'];
		$page['backward'] = $page['current_shows'] > 1;
		$page['forward'] = $page['current_shows'] * $page['pagination_max_length'] < $page['pages'];
		$limit = "LIMIT " . (($page['no'] - 1) * $page['length']) . ",{$page['length']} ";
		$sql = "
			SELECT u.*, group_concat(i.url) as url
			FROM api_user as u
			LEFT JOIN api_acl as a ON a.api_user_id = u.id
			LEFT JOIN api as i ON i.id = a.api_id
			WHERE  1 $search GROUP BY u.id $order  $limit
		";

		$rs['data'] = xpTable::load($this->table)->q($sql);
		$rs['search'] = $q['search'];
		$rs['page'] = $page;
		return $rs;
	}

	/**
	 *get acl by a key/column
	 */
	function get($value, $key='id'){
		$value = addslashes($value);
		$key = 'u.'.addslashes($key);
		$sql = "
			SELECT u.*, group_concat(i.url) as url
			FROM api_user as u
			LEFT JOIN api_acl as a ON a.api_user_id = u.id
			LEFT JOIN api as i ON i.id = a.api_id
			WHERE  $key = '$value'
		";

		$rs = xpTable::load($this->table)->q($sql);
		return $rs[0];
	}
	/**
	 *save acl entry for a api user
	 * this involve delete old entrys
	 */
	function saves($q) {
		if(!($user = (int)$q['id'])) return array('gateway');
		$gateway = $q['url'];
		//remove old entries
		xpTable::load($this->table)->deletes(array('api_user_id' => $user));
		foreach($gateway as $g){
			//$g = '/api'.$g;
			if($acl = _factory('sitemin_api_model_api')->get_acl_by_url($g))
				xpTable::load($this->table)->insert(array('api_user_id'=>$user, 'api_id'=>$acl['id']));
		}
	}
	/**
	 *check user acl on api call
	 */
	function check_acl($user, $api){
		return xpTable::load($this->table)->get(array('api_id'=>$api['id'], 'api_user_id'=>$user['id']));
	}
}
