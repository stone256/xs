<?php
/**
 * @author  peter wang <xpw365@gmail.com>
 * @version  1.23
 * @package data array/grid
 *
 * stantard xp table: key field : id auto incremantal
 */
/*start class */
class xpTable {
	//loaded tables
	//	static $tables = array();
	static $handles = array();
	var $table;
	var $field_names;
	var $key;
	/**
	 * object handler
	 *
	 * @param string $name	:  table name
	 * @param boolean $new	:  reload cfg,
	 * @return object handler
	 */
	static function load($name, $cfg = null) {
		if (!$cfg) global $cfg;
		$hash = md5(json_encode($cfg['db']));
		if (self::$handles[$hash][$name]) return self::$handles[$hash][$name];
		return self::$handles[$hash][$name] = new self($name, $cfg);
	}
	/**
	 * create table name
	 *
	 * @param string $name	: table name
	 */
	function __construct($name, $cfg = null) {
		$this->table = $name;
		$this->db = xpPdo::conn($cfg);
		$r = $this->db->table_info($name);
		$this->field_names = $this->db->table_fields($name);
		$this->key = $this->db->table_primary_key($name);
		return;
		$this->structure = $db->parse_structure();
	}
	/**
	 * relay all other call to xpMysql
	 *
	 * @param string $name 	: method name
	 * @param mix $args		: parameters
	 * @return mix
	 */
	function __call($name, $args) {
		array_unshift($args, $this->table);
		return call_user_func_array(array($this->db, $name), $args);
	}
	function q($q) {
		return $this->db->q($q);
	}
	function query($q){
		return $this->db->query($q);		
	}	
	function check($value, $field = null) {
		if (!$field) $field = $this->key ? $this->key : $this->field_names[0];
		return $this->get(array($field => $value));
	}
	/**
	 * list
	 *  status, limit , orders, search
	 *
	 * @param array $arr
	 * @return mix
	 */
	function lists($arr = null, $query = null, $count_query = null) {
		/**
		 * $arr =array(
		 * table=>table name
		 *  order=>name,-age,mms // -: DESC
		 *  fields=name,age,email or * //default * if empty
		 *  search=>array(array(name="peter",id<12,syatus is not_null, ql <> 121),
		 * 				array(email like "peter%")
		 * 				)
		 * 				* inside array is AND condition
		 * 				*between array is OR condition
		 *  or search=>name="peter",id<12,syatus is not_null, ql <> 121
		 *  'data' => when user query and count_query this is the data sent to xpPdo->query(query,data)
		 *
		 * $page['no']	(passed in as request page, and current page as calculated)
		 * 					= max(1,min($page['pages'], ((int)$arr['page']['no'] ? (int)$arr['page']['no']: 1) ));
		 * $page['length']  (rows in a page)	= $arr['page']['length']  || 20 ;
		 * $page['pagination_max_length']  (max pagination shows)
		 * 					= $arr['page']['pagination_max_length']  || 12;
		 *    //those are calculated value
		 * $page['pages'] (total pages) 	= ceil($count/$page['length']);
		 * $page['total']   (total rows)		= $count;
		 *
		 * $page['current_start']  (current start page in pagination )
		 * 				= floor($page['no'] / $page['pagination_max_length']) * $page['length'] + 1;  // 1...xxx
		 * $page['current_end'] (current last page in pagination)
		 * 				= min($page['pages'] ,  $page['current_start'] + $page['length'] + 1)
		 * $page['omit'] (flag for show omit link "..")
		 * 				= $page['pages'] > $page['pagination_max_length'];
		 * $page['backward'] 	(flag for show back button)
		 * 				= $page['current_shows'] > 1;
		 * $page['forward'] 	(flag for show forward button)
		 * 				= $page['current_shows']*$page['pagination_max_length'] < $page['pages'];
		 *
		 * )
		 *
		 */
		//counting query: this query is to count total rows/reocrds
		if (!$query) {
			$rs[0] = $this->get($arr['search'], "count(*) as c");
		} else {
			$rs = $this->query($count_query, $arr['data']);
		}
		$count = $rs[0]['c'];
		//calculate page and limit
		$page['length'] = (int)$arr['page']['length'] ? (int)$arr['page']['length'] : 12;
		$page['pagination_max_length'] = (int)$arr['page']['pagination_max_length'] ? $arr['page']['pagination_max_length'] : 6;
		$page['total'] = $count;
		$page['pages'] = ceil($count / $page['length']);
		$page['no'] = max(1, min($page['pages'], ((int)$arr['page']['no'] ? (int)$arr['page']['no'] : 1)));
		$page['current_start'] = floor(($page['no'] - 1) / $page['pagination_max_length']) * $page['pagination_max_length'] + 1; // 1...xxx
		$page['current_end'] = min($page['pages'], $page['current_start'] + $page['pagination_max_length'] - 1);
		$page['range'] = range(max(1, $page['current_start']), max($page['current_start'], $page['current_end']));
		$page['backward'] = max(1, $page['no'] - $page['pagination_max_length']);
		$page['backward_omit'] = $page['no'] <= $page['pagination_max_length'];
		$page['backward_fast'] = $page['backward_omit'] ? 0 : ceil($page['backward'] * 0.3);
		$page['forward'] = min($page['no'] + $page['pagination_max_length'], $page['pages']);
		$page['forward_omit'] = $page['no'] + $page['pagination_max_length'] > $page['pages'];
		$page['forward_fast'] = $page['forward_omit'] ? 0 : floor($page['pages'] * 0.7 - $page['no'] * 0.3);
		$limit = array(($page['no'] - 1) * $page['length'], $page['length']);
		if (!$query) {
			$rs = $this->gets($arr['search'], $arr['fields'], $arr['order'], $limit);
		} else {
			$rs = $this->query($query, $arr['data']);
		}
		$arr['rows'] = $rs;
		$arr['page'] = $page;
		return $arr;
	}
}
