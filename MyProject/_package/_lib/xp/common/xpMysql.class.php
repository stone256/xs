<?php
/**************************************************************************
 *	some function relay on id field(field's name is 'id' )!!
 **************************************************************************/
/**
 * @author  peter wang <xpw365@gmail.com>
 * @version  2.12
 * @package mysql_layer
 * 2010 08 21
 */
/*start class */
class xpMysql {
	var $Host = "";
	var $Database = "";
	var $User = "";
	var $Password = "";
	var $Link_ID = 0;
	var $Query_ID = 0;
	var $Fields = array();
	var $Prefix = '';
	var $structure;
	/**
	 * last query
	 *
	 * @var string
	 */
	var $last_sql;
	var $tables;
	/**
	 * need replace with prefix
	 *
	 * @var string
	 */
	static $prefix_replacer = '{#_}';
	/**
	 * get connection in singleton mode
	 * 	* this will only work in php5 up
	 */
	static $connection = null;
	/**
	 * current setting is loaded from this data block
	 */
	static $cfg = null;
	static $switch_cfg = null;
	function conn($cfg = null) {
		if (!self::$connection) self::$connection = new xpMysql($cfg);
		return self::$connection;
	}
	function conn_close() {
		if (self::$connection) mysql_close(self::$connection);
		self::$connection = null;
	}
	/***************************************************/
	/* constructor  - opens connection*/
	function __construct($cfg = null) {
		//		if(!$cfg) global $cfg;
		//		global $config,$CFG;
		//		$c = $cfg['db'] ?$cfg['db'] : ($config['db'] ? $config['db'] :($confg['host']  ? $config : xpAS::object2array($CFG->db)));
		$c = self::get_config($cfg);
		$this->Host = xpAS::priority_get($c['host'], $c['server']);
		$this->User = xpAS::priority_get($c['user'], $c['username']);
		$this->Password = xpAS::priority_get($c['pwd'], $c['pass'], $c['password']);
		$this->Database = xpAS::priority_get($c['db'], $c['database'], $c['dbname']);
		$this->Prefix = xpAS::priority_get($c['prefix'], '');
		$this->connect();
		mysql_query('SET NAMES utf8');
		mysql_query('SET CHARACTER_SET_CLIENT=utf8');
		mysql_query('SET CHARACTER_SET_RESULTS=utf8');
		mysql_query('SET CHARACTER_SET_CONNECTION=utf8');
	}
	/**
	 * sewitch db
	 *
	 * @param mix $cfg	: switch back if is null
	 * @return new db handle
	 */
	function db_switch($cfg = null) {
		if (!$cfg) $cfg = self::$switch_cfg;
		self::$switch_cfg = self::$cfg;
		self::$connection = null;
		return xpMysql::conn(array('db' => $cfg));
	}
	function get_config($cfg = null) {
		if (!$cfg) global $cfg;
		global $xp_config, $CFG;
		switch (true) {
			case $cfg['db']:
				$c = $cfg['db'];
			break;
			case $xp_config['db']:
				$c = $xp_config['db'];
			break;
			default:
				$c = xpAS::object2array($CFG->db);
			break;
		}
		//		$c = $cfg['db'] ?$cfg['db'] : ($config['db'] ? $config['db'] :($confg['host']  ? $config : xpAS::object2array($CFG->db)));
		self::$cfg = $c;
		return $c;
	}
	/**
	 * create a new database
	 *
	 * @param string $db : name
	 * @return boolen of query
	 */
	function new_db($db) {
		$db = mysql_real_escape_string($db);
		$query = "CREATE DATABASE $db";
		return mysql_query($query);
	}
	function db_list() {
		return $this->q('show databases');
	}
	/**
	 * seting tables prefix
	 *
	 * @param string $prefix :  if empty return current
	 * @return  string
	 */
	function prefix($prefix = false) {
		if ($prefix == false) return $this->Prefix;
		$this->Prefix = $prefix;
	}
	function connect($with_db = true) {
		$this->Link_ID = mysql_connect($this->Host, $this->User, $this->Password);
		if ($with_db && $this->Database) {
			//mysql_select_db($this->Database,$this->Link_ID) or die("Fatal Error - Cannot connect to database<br />MySQL returned error: " . mysql_error($this->Link_ID) . ' (Error number: ' . mysql_errno($this->Link_ID) . ')');
			$this->load_db();
		}
	}
	function pconnect($with_db = true) {
		$this->Link_ID = mysql_connect($this->Host, $this->User, $this->Password);
		if ($with_db) {
			//mysql_select_db($this->Database,$this->Link_ID) or die("Fatal Error - Cannot connect to database<br />MySQL returned error: " . mysql_error($this->Link_ID) . ' (Error number: ' . mysql_errno($this->Link_ID) . ')');
			$this->load_db();
		}
	}
	function load_db($db = null) {
		if ($db) $this->Database = $db;
		mysql_select_db($this->Database, $this->Link_ID) or xp_handleShutdown("#001 Fatal Error - connect to database: $sql<br />MySQL returned error: " . mysql_error($this->Link_ID) . ' (Error number: ' . mysql_errno($this->Link_ID) . ')');
		//die("Fatal Error - Cannot connect to database<br />MySQL returned error: " . mysql_error($this->Link_ID) . ' (Error number: ' . mysql_errno($this->Link_ID) . ')');
		$this->tables = $this->q('show tables');
	}
	function disconnect() {
		mysql_close();
	}
	/**
	 * auto db structure
	 *
	 * @return unknown
	 */
	function parse_structure() {
		if ($this->structure) return $this->structure;
		$tables = $this->table_list();
		foreach ((array)(xpAS::preg_gets(implode(',', $tables), '/[a-zA-Z\_]+\_x\_[a-zA-Z\_]+/')) as $v) {
			$d = explode('_x_', $v);
			$xref[$d[0]][] = $d[1];
			$xref[$d[1]][] = $d[0];
		}
		foreach ($tables as $k => $v) {
			$fields = xpAS::key($this->table_info($v), 'Field');
			$links = array();
			$key = '';
			foreach ($fields as $k1 => $v1) {
				if ($v1['Key'] == 'PRI') {
					$key = $v1['Field'];
					continue;
				}
				if (preg_match('/(.*?)\_(id)/', $v1['Field'], $t)) {
					if ($t[1] != $table) $links[$v1['Field']] = $t[1];
				}
			}
			$s = array('fields' => $fields, 'links' => $links, 'key' => $key, 'xref' => $xref[$v]);
			$ts[$v] = $s; //$this->parse_table($v);
			
		}
		return $this->structure = $ts;
	}
	function parse_structure1() {
		$ts = xpMysql::conn()->q("SELECT * FROM information_schema.columns WHERE TABLE_SCHEMA='{$this->Database}'");
		foreach ($ts as $k => $v) {
			if (preg_match('/\-/', $v['TABLE_NAME'])) continue;
			$table[$v['TABLE_NAME']]['fields'][$v['COLUMN_NAME']] = $v;
			if ($v['COLUMN_KEY'] == 'PRI') {
				$table[$v['TABLE_NAME']]['key'] = $v['COLUMN_NAME'];
			}
			if (xpAS::preg_gets($v['TABLE_NAME'], '/[a-zA-Z\_]+\_x\_[a-zA-Z\_]+/')) {
				$d = explode('_x_', $v['TABLE_NAME']);
				$xref[$d[0]][$d[1]] = $d[1];
				$xref[$d[1]][$d[0]] = $d[0];
			}
			if (preg_match('/(.*?)\_(id)/', $v['COLUMN_NAME'], $t)) {
				if ($t[1] != $v['TABLE_NAME']) {
					$table[$t[1]]['down'][$v['TABLE_NAME']] = $v['COLUMN_NAME'];
					$table[$v['TABLE_NAME']]['up'][$t[1]] = 'id';
				}
			}
		}
		foreach ($table as $k => $v) {
		}
		_debug($xref);
		_die($table);
		//  		_debug(fasfasd);
		//  		if($this->structure) return $this->structure;
		//  		_debug(fasfasd);
		$tables = $this->table_list();
		foreach ((array)(xpAS::preg_gets(implode(',', $tables), '/[a-zA-Z\_]+\_x\_[a-zA-Z\_]+/')) as $v) {
			$d = explode('_x_', $v);
			$xref[$d[0]][] = $d[1];
			$xref[$d[1]][] = $d[0];
		}
		$tables = array_flip($tables);
		xpAS::set($tables, preg_quote('-') . '*', null);
		xpAS::set($tables, '*', array());
		foreach ($tables as $k => $v) {
			$fields = xpAS::key($this->table_info($k), 'Field');
			$parents = array();
			$key = '';
			foreach ($fields as $k1 => $v1) {
				if ($v1['Key'] == 'PRI') {
					$key = $v1['Field'];
					continue;
				}
				if (preg_match('/(.*?)\_(id)/', $v1['Field'], $t)) {
					if ($t[1] != $k) {
						$parents[$t[1]] = $v1['Field'];
						$tables[$t[1]]['children'][$k] = $key;
					}
				}
			}
			$tables[$k]['name'] = $k;
			$tables[$k]['fields'] = $fields;
			$tables[$k]['parents'] = $parents;
			$tables[$k]['key'] = $key;
			$tables[$k]['xref'] = $xref[$k];
		}
		return $this->structure = $tables;
	}
	function table_list() {
		$rs = xpAS::column($this->q('show tables'), 'Tables_in_' . $this->Database);
		return $rs;
	}
	function table_info($table) {
		return $this->q('Describe `' . $this->_table_name($table) . '`');
	}
	function backup_db($path, $excludes = array()) {
		$fh = fopen($path, 'w+');
		foreach ((array)$this->table_list() as $kt => $vt) {
			//table head
			if (in_array($vt, $excludes)) continue;
			fwrite($fh, "\n\n------------TABLE: `$vt`-------------\n\n");
			$table_head = $this->q('SHOW CREATE TABLE ' . $vt);
			fwrite($fh, "\n\nDROP TABLE IF EXISTS `$vt`;\n");
			fwrite($fh, $table_head[0]['Create Table']);
			fwrite($fh, ";\n\n");
			if ($rows = $this->q("select * from `$vt`", 'raw')) {
				$ct = count($rows) - 1;
				fwrite($fh, "INSERT INTO  `$vt` VALUES \n");
				foreach ($rows as $kr => $vr) {
					$r = str_replace("\n", "\\n", '"' . implode('","', xpAS::slash($vr)) . '"');
					$r = "( $r ) " . ($kr == $ct ? ";\n\n" : ",\n");
					fwrite($fh, $r);
				}
				fwrite($fh, "\n\n");
			}
			fwrite($fh, "\n");
			//table data
			
		}
		fclose($fh);
	}
	function content_search($con) { //search all relatived field or only on type field
		$con = mysql_real_escape_string($con);
		$struct = $this->parse_structure();
		switch (true) {
			case is_numeric($con):
				$type = "number";
			break;
			case preg_match('/^\d\d\d\d\-\d\d-\d\d(\s\d\d\:\d\d\:\d\d)?$/', trim($con));
			$type = "date";
		break;
		default:
			$type = 'string';
		break;
	}
	foreach ((array)$struct as $ks => $vs) {
		$C = array();
		foreach ($vs['fields'] as $kf => $vf) {
			switch (true) {
				case preg_match('/date|datetime|timestamp/i', $vf['Type']);
				$ftype = "date";
			break;
			case preg_match('/int|decimal|float|double|real|serial/i', $vf['Type']):
				$ftype = 'number';
			break;
			case preg_match('/char|text|blob/i', $vf['Type']):
				$ftype = "string";
			break;
			default:
				$ftype = 'not to search';
		}
		if ($ftype == $type && $type == 'number') $C[] = " `{$ks}`.`{$vf['Field']}` = '$con' ";
		if ($ftype == $type && $type == 'date') $C[] = " `{$ks}`.`{$vf['Field']}` like '$con%' ";
		if ($ftype == $type && $type == 'string') $C[] = " `{$ks}`.`{$vf['Field']}` like '%$con%' ";
	}
	if (count($C) > 0) {
		$r = $this->q("SELECT COUNT(*)  as c FROM {$ks} " . "WHERE " . implode(' OR ', $C));
		if ($r[0]['c'] > 0) $arr[] = $ks;
	}
}
return $arr;
}
/**
 * search column name
 */
function column_search($name) {
	$tables = $this->table_list();
	foreach ($tables as $kt => $vt) {
		$fields = xpAS::key($this->table_info($vt), 'Field');
		foreach ((array)$fields as $kf => $vf) {
			switch (true) {
				case $kf == $name:
					$b['matched'][$vt][] = $vf;
				break;
				case preg_match('/' . preg_quote($name) . '/', $kf):
					$b['similar'][$vt][] = $vf;
				break;
			}
		}
	}
	return $b;
}
function table_empty($table) {
	return $this->query('TRUNCATE TABLE `' . mysql_escape_string($this->_table_name($table)) . '`');
}
/**
 * create new table
 *
 * @param string  $table		: table name without prefix
 * @param array $fields			: field eg.
 *					fields=>array(
 * 							'cid INT NOT NULL AUTO_INCREMENT',
 * 							'cname VARCHAR(20) NOT NULL',
 * 							'cemail VARCHAR(50) NOT NULL',
 * 							'csubject VARCHAR(30) NOT NULL ',
 * 							'cmessage TEXT NOT NULL',
 * 							'PRIMARY KEY(cid)'
 * 							)
 * @return  boolean
 */
function new_table($table, $fields) {
	$table = $this->_table_name($table);
	$fields = $this->_escape_string($fields);
	$columns = implode(', ', "`$fields`");
	$query = " CREATE TABLE IF NOT EXISTS `$table` ( $columns ) ; ";
	return mysql_query($query);
}
/**
 * DUPLICATE TABLE
 *
 * @param string $old 			: old table name
 * @param string $new 		: new table name
 * @param boolean $withdata 	: copy with data
 */
function duplicate($old, $new, $withdata = false) {
	$old1 = $this->_table_name($old);
	$new1 = $this->_table_name($new);
	$this->query("CREATE TABLE `$new1` LIKE `$old1`");
	if ($withdata) $this->copy_data($old, $new);
}
/**
 * copying data from one to othe
 *
 * @param string $sour		: source table
 * @param string $dest		: target table
 */
function copy_data($sour, $dest) {
	$sour = $this->_table_name($sour);
	$dest = $this->_table_name($dest);
	//s.92
	//$q = "SELECT *  INTO $dest  FROM $sour";
	$this->query("INSERT INTO `$dest`  SELECT * FROM `$sour`");
}
/**
 * returns value from $Record
 *
 * @param field name $field
 * @return string
 */
function value($field) {
	return $this->Fields[$field];
}
/**
 * returns number of rows
 * @return number
 */
function num_rows() {
	if ($this->Query_ID) return mysql_num_rows($this->Query_ID);
	else return 0;
}
/**
 * Goes to a given row in the current recordset
 *
 * @param int $row_no
 * @return  array record
 */
function seek($row_no) {
	mysql_data_seek($this->Query_ID, row_no);
	return $this->next_record();
}
/**
 * returns field names on current record set
 *
 * @param string  $table name
 * @param int $i field index
 * @return mix or string
 */
function field_names($table = false, $index = false) {
	$nfields = $this->num_fields($table);
	for ($i = 0;$i < $nfields;$i++) {
		$field = mysql_fetch_field($this->Query_ID, $i);
		$field_name[] = $field->name;
	}
	return $index ? $field_name[$index] : $field_name;
}
/**
 * returns number of fields
 *
 * @return int
 */
function num_fields($table = false) {
	if ($table) {
		$table = $this->_table_name($table);
		$this->query("select * from `$table` limit 1");
	}
	return mysql_num_fields($this->Query_ID);
}
/**
 * get table primary key field name
 *
 * @param string $table
 * @return string
 */
function primary_key_name($table = false) {
	$fields = $this->num_fields($table);
	$key = null;
	for ($i = 0;$i < $fields;$i++) {
		$f = mysql_field_flags($this->Query_ID, $i);
		$n = mysql_field_name($this->Query_ID, $i);
		if (preg_match('/.+(primary(.+)key){1}/i', $f)) $key = $n;
	}
	return $key;
}
/**
 * executes query
 *
 * @param string $sql
 * @return query id
 */
function query($sql) {
	$this->last_sql = $sql;
	$this->Query_ID = mysql_query($sql, $this->Link_ID);
	if (mysql_errno($this->Link_ID) != 0) {
		//xp_handleShutdown("#002Fatal Error - Cannot execute query: $sql<br />MySQL returned error: " . mysql_error($this->Link_ID) . ' (Error number: ' . mysql_errno($this->Link_ID) . ')');
		die("Fatal Error - Cannot execute query: $sql<br />MySQL returned error: " . mysql_error($this->Link_ID) . ' (Error number: ' . mysql_errno($this->Link_ID) . ')');
	}
	return $this->Query_ID;
}
/**
 * inspect last sql
 */
function my_sql() {
	return $this->last_sql;
}
/**
 * get result
 *
 * @param string $q
 * @return array of  records
 */
function q($q, $raw = false) {
	$q = str_replace(self::$prefix_replacer, $this->Prefix, $q);
	$this->query($q);
	return $this->return_records(MYSQL_ASSOC, $raw);
}
/**
 * count total
 *
 * @param table $table
 * @param string/mix $con
 * @return int
 */
function count($table, $cond) {
	$table = $this->_table_name($table);
	$cond = is_array($cond) ? $this->_condition($table, $cond) : $cond;
	//		$cond = $this->_condition($table,$cond);
	$q = "select count(*) as total from `$table` where $cond";
	$this->query($q);
	$r = $this->next_record();
	$this->free_query();
	return $r['total'];
}
/**
 * get a record by key filed
 *
 * @param string $table
 * @param string $keyField
 * @param string $keyValue
 * @return array record
 */
function get($table, $cond = ' 1 ', $fields = '', $order = '') {
	$table = $this->_table_name($table);
	$cond = $this->_condition($table, $cond);
	$fields = self::_fields($table, $fields);
	$order = self::_order($table, $order);
	$q = "select $fields from `$table` where $cond  $order limit 1";
	$this->query($q);
	$r = $this->next_record();
	$this->free_query();
	return $r;
}
/**
 * get groups records on manual condition
 *
 * @param string $table
 * @param string $con
 * @return array of a record
 */
function gets($table, $cond = ' 1 ', $fields = '', $order = '', $limit = '') {
	$table = $this->_table_name($table);
	$cond = $this->_condition($table, $cond);
	$fields = self::_fields($table, $fields);
	$order = self::_order($table, $order);
	$limit = self::_limit($table, $limit);
	$q = "select $fields from `$table` where $cond $order $limit";
	//if($table=='student_profile')_debug($q);
	$this->query($q);
	$rs = $this->return_records();
	$this->free_query();
	return $rs;
}
/**
 * @deprecated
 * will delete one row base on id field
 *
 * @param string $table
 * @param mix $keyValue
 * @param string $keyField
 * @return int
 */
function delete($table, $keyValue, $keyField = null) {
	$keyField = mysql_real_escape_string(xpAS::priority_get($keyField, $this->primary_key_name($table), 'id'));
	$table = $this->_table_name($table);
	$q = "DELETE FROM `$table` where `$table`.`$keyField` ='" . mysql_real_escape_string('' . $keyValue) . "' limit 1";
	$this->query($q);
	return mysql_affected_rows();
}
/**
 * delete reocrds
 *
 * @param string $table
 * @param mix $cond
 * @return int
 */
function deletes($table, $cond) {
	$table = $this->_table_name($table);
	$cond = is_array($cond) ? $this->_condition($table, $cond) : $cond;
	$q = "delete from `$table` where $cond ";
	$this->query($q);
	return mysql_affected_rows();
}
/**
 * insert a record
 *
 * @param string $table : name
 * @param array $data	: data array
 * @return boolean
 */
function insert($table, $data) {
	$table = $this->_table_name($table);
	$str = $this->_set_string($table, $data);
	$q = "INSERT INTO `$table` SET $str ";
	$this->query($q);
	$r = mysql_insert_id();
	if (!$r) {
		$r = $this->q('select LAST_INSERT_ID() as id');
		$r = $r[0]['id'];
	}
	return $r;
}
/**
 * update one record
 *
 * @param string $table	: name
 * @param array $data	: data array
 * @param mix $cond		: conditions or use key field in $data if false
 * @return boolen
 */
function update($table, $data, $cond = false) {
	if (!is_array($data)) return false;
	if (!$cond) { //use keyfield in data array
		$key = $this->primary_key_name($table);
		$key = $key ? $key : 'id';
		$cond = array($key => $data[$key]);
		unset($data['$key']);
	}
	$table = $this->_table_name($table);
	$str = $this->_set_string($table, $data);
	$cond = is_array($cond) ? $this->_condition($table, $cond) : $cond;
	$q = "UPDATE `$table` SET $str WHERE $cond LIMIT 1";
	$this->query($q);
	return mysql_affected_rows();
}
/**
 * update records
 *
 * @param string $table	: table name
 * @param array $data	: data array
 * @param mix $cond		: conditions
 * @return boolen
 */
function updates($table, $data, $cond) {
	$table = $this->_table_name($table);
	$str = $this->_set_string($table, $data);
	$cond = is_array($cond) ? $this->_condition($table, $cond) : $cond;
	$q = "UPDATE `$table` SET $str WHERE $cond";
	$this->query($q);
	return mysql_affected_rows();
}
/** write record back if no exist, create new one;
 *  return insert or update(first one) primary key
 */
function write($table, $data, $cond = null) {
	if (!$cond) {
		$key = $this->primary_key_name($table);
		$cond = array($key => $data[$key]);
	}
	$table = $this->_table_name($table);
	$cond = is_array($cond) ? $this->_condition($table, $cond) : $cond;
	$q = "select * from `$table` where $cond";
	$this->query($q);
	if ($this->num_rows() < 1) {
		return $this->insert($table, $data);
	} else {
		$r = $this->next_record();
		$key = $this->primary_key_name($table);
		$key = $key ? $key : 'id';
		$id = $r[$key];
		//  			$data[$key] = $r[$key];
		$this->update($table, $data, $cond);
		return $id;
	}
}
/**
 *  move to the next record in the record set
 */
function next_record($type = MYSQL_ASSOC, $raw = false) {
	$this->Fields = $raw ? $this->_record($type) : $this->_unserialize($this->_record($type));
	return $this->Fields;
}
/** 
 * return record as raw
 */
function _record($type = MYSQL_ASSOC) {
	if ($this->Query_ID) {
		$this->Fields = @mysql_fetch_array($this->Query_ID, $type);
		return $this->Fields;
	}
	return false;
}
/**
 *  returns the entire record set as a MYSQL_ASS0C array
 */
function return_records($type = MYSQL_ASSOC, $raw = false) {
	$arr = array();
	while ($this->next_record($type, $raw)) {
		$arr[] = $this->Fields;
	}
	return $arr;
}
/**
 *  return raw rs
 */
function _records($type = MYSQL_ASSOC) {
	$arr = array();
	while ($this->_record($type)) {
		$arr[] = $this->Fields;
	}
	return $arr;
}
/**
 * re-format record
 *
 * @param array $arr 	: from db
 * @return array		: original form
 */
function _unserialize($arr = null) {
	if (!is_array($arr)) return $arr; //leaf string
	foreach ($arr AS $k => $v) {
		$c = @unserialize($v);
		$arr[$k] = is_array($c) ? $c : $v;
	}
	return $arr;
}
//or-array( and-array());
function _condition($table, $crr = false) {
	//static $tnt = '/\<?\>|\<|\<?\=|\s+is\s+|\s+like\s+|\s+regexp\s+|\s+in\s+|\s+not\s+in\s+/i';
	static $tnt = '/\s+\<?\>\s+|\s+\<\s+|\s+\>?\=\s+|\s+\<?\=\s+|\s+is\s+|\s+not\s+like\s+|\s+like\s+|\s+regexp\s+|\s+in\s+|\s+not\s+in\s+|\s+rlike\s+|\s+REGEXP\s+|\s+is\s+\not\s+|\s+not\s+\regexp\s+/i';
	if (!$crr) return ' 1 ';
	if (!is_array($crr)) $crr = array($crr);
	if (!is_array(xpAS::first($crr))) $crr = array($crr);
	//or array
	foreach ($crr as $kor => $vor) {
		$and = array(); //clear
		foreach ($vor as $kand => $vand) {
			if (is_numeric($kand)) {
				$cs = xpAS::split($vand);
				foreach ($cs AS $kcs => $vcs) {
					$vcs = trim($vcs);
					if (count($t = xpAS::clean(xpAS::split($vcs, $tnt))) != 2) {
						$and[] = $vcs;
						continue; //donot deal with mal-format
						
					}
					$t[0] = mysql_real_escape_string('' . $t[0]);
					$t[1] = trim($t[1]);
					//						$t[2] = trim(preg_replace('/^'.preg_quote($t[0]).'/','', preg_replace('/'.preg_quote($t[1]).'$/','',$vcs)));
					$t[2] = trim(preg_replace('/^' . preg_quote($t[0]) . '/', '', str_replace($t[1], '', $vcs)));
					//_debug($t,$vcs);
					$t[1] = xpAS::is_quoted($t[1]) ? xpAS::de_quote($t[1], '()') : $t[1];
					if (!preg_match('/rlike|regexp|not\s+regexp/i', $t[2])) $t[1] = mysql_escape_string('' . $t[1]);
					else $t[1] = str_replace("'", '\\' . "'", $t[1]);
					//_debug($t,$vcs);
					if (strtolower($t[2]) != 'is' && strtolower($t[2]) != 'in' && strtolower($t[2]) != 'not in') $t[1] = "'" . $t[1] . "'";
					//_debug(						"`{$table}`.`{$t[0]}` {$t[2]} ". " {$t[1]} " );
					$and[] = "`{$table}`.`{$t[0]}` {$t[2]} " . " {$t[1]} ";
				}
			} else {
				if (is_array($vand) && count($vand)) {
					//use in
					$vand = self::_escape_string($vand);
					$and[] = "`$table`.`$kand` in ('" . implode("','", $vand) . "') ";
				} else {
					$kand = mysql_real_escape_string('' . $kand);
					$vand = mysql_real_escape_string('' . $vand);
					$and[] = "`$table`.`$kand` ='$vand' ";
				}
			}
		}
		$cond_and[] = ' ( ' . implode("\n AND ", $and) . ' ) ';
	}
	$cond = implode("\n OR ", $cond_and);
	return $cond ? $cond : ' 0 ';
}
/**
 *  set sql set string
 */
function _set_string($table, $data, $dm = ', ') {
	if (!is_array($data)) return;
	$data = $this->_field_filter($table, $data);
	$pair = array();
	foreach ($data AS $n => $v) {
		if (is_array($v) || is_object($v)) $v = serialize($v);
		$pair[] = '`' . $table . '`.`' . $n . "`='" . mysql_real_escape_string('' . $v) . "' ";
	}
	return implode($dm, $pair);
}
/**
 * setup limit
 *
 * @param mix $arr
 * @return string
 */
function _limit($table, $arr = null) {
	if (!$arr) return '';
	$arr = is_array($arr) ? $arr : preg_split('/\,\s*/', $arr);
	$arr[0] = (int)$arr[0];
	if ($arr[1] = (int)$arr[1]) return " LIMIT {$arr[0]},{$arr[1]}";
	return " LIMIT {$arr[0]}";
}
/**
 * select fields
 *
 * @param string $table
 * @param mix $arr
 * @return string
 */
function _fields($table, $arr = null) {
	if (!$arr || $arr == '*') return ' * ';
	return $arr;
	$fields = $this->field_names($table);
	$arr = is_array($arr) ? $arr : preg_split('/\,\s*/', $arr);
	foreach ($fields as $k => $f) {
		if (in_array($f, $arr)) $brr[] = "`{$table}`.`{$f}`";
		//			if(preg_match('/\(.*\)/',$f)) $brr[] = $f;
		
	}
	return implode(', ', $brr);
}
/**
 * make order
 *
 * @param string $table
 * @param mix $arr
 * @return string
 */
function _order($table, $arr = null) {
	if (!$arr) return '';
	$fields = $this->field_names($table);
	$arr = is_array($arr) ? $arr : preg_split('/\,\s*/', $arr);
	foreach ($arr as $k => $f) {
		if (in_array(str_replace('-', '', $f), $fields)) $brr[] = "`$table`.`" . (preg_match('/\-/', $f) ? str_replace('-', '', $f) . "` DESC " : $f . "`");
	}
	return ' ORDER BY ' . implode(',', $brr) . ' ';
}
/**
 * filter out none column field
 *
 * @param string $table
 * @param array $arr
 */
function _field_filter($table, $arr) {
	$fields = $this->field_names($table);
	foreach ($fields as $k => $f) if (isset($arr[$f])) $brr[$f] = $arr[$f];
	return $brr;
}
function _escape_string($a) {
	if (is_array($a)) {
		$b = array();
		foreach ($a as $k => $v) $b[mysql_escape_string($k) ] = self::_escape_string($v);
		return $b;
	}
	return mysql_escape_string($a);
}
/**
 * create in range like (1,4,55,6)
 *
 * @param unknown_type $arr
 * @return unknown
 */
function _in($arr) {
	if (is_array($arr)) {
		if (count($arr) < 1) return '()';
		$arr = array_values($arr);
		$arr = self::_escape_string($arr);
		$arr = " ('" . implode("', '", $arr) . "') ";
	} else {
		$arr = " (" . $arr . ") ";
	}
	return $arr;
}
/**
 *  free result;
 */
function free_query() {
	mysql_free_result($this->Query_ID);
}
/**
 * prepare query and call
 *
 * @param string 	$sql 	: template of query string  {abc}   = >$arr[abc]=15
 * @param array 	$arr 	: data array
 * @return array of query result
 */
function run($sql, $arr, $m = "##~#~#") {
	if (!$sql || !is_array($arr)) return false;
	foreach ($arr as $k => $v) $sql = preg_replace('/\{' . preg_quote($k) . '\}/ims', $m . $k . $m, $sql);
	foreach ($arr as $k => $v) $sql = preg_replace('/' . preg_quote($m . $k . $m) . '/ims', mysql_escape_string($v), $sql);
	return $this->query($sql);
}
/**
 * make up a query
 *  not realy useful
 *
 * @param mix $arr
 * @return array of query result
 */
function make($arr) {
	$SELECT = $arr['select'] ? 'SELECT ' . (is_array($arr['select']) ? implode(',', $arr['select']) : $arr['select']) : 'SELECT  * ';
	$FROM = 'FROM ' . (is_array($arr['from']) ? implode(',', $arr['from']) : $arr['from']);
	if ($r = $arr['join']) {
		$JOIN = $r['type'] ? $r['type'] . ' ' : 'JOIN ';
		$JOIN.= $r['table'];
		$JOIN.= $r['on'];
	}
	if ($r = $arr['where']) $WHERE = 'WHERE  ' . (is_array($r) ? implode(' AND ', $r) : $r);
	else $WHERE = 'WHERE 1 ';
	if ($r = $arr['group by']) $GROUP_BY = 'GROUP BY ' . (is_array($r) ? implode(',', $r) : $r);
	if ($r = $arr['having']) $HAVING = 'HAVING ' . (is_array($r) ? implode(',', $r) : $r);
	if ($r = $arr['order by']) $GROUP_BY = 'ORDER BY ' . (is_array($r) ? implode(',', $r) : $r);
	if ($r = $arr['limit']) $HAVING = 'LIMIT ' . (is_array($r) ? implode(',', $r) : $r);
	$q = "$SELECT $FROM $JOIN $WHERE $GROUP_BY $HAVING $ORDER_BY $LIMIT";
	//return $q;
	return $this->q($q);
}
/**
 * add prefix to table name
 *
 * @param unknown_type $table
 * @return unknown
 */
function _table_name($table) {
	$table = mysql_escape_string($table);
	if (substr($table, 0, strlen($this->Prefix)) == $this->Prefix) return $table;
	return $this->Prefix . mysql_escape_string($table);
}
/**
 * simple locking 	:lock  a row
 *
 * @param $table		:  	table name
 * @param $cond 		:  	search condition of which row can be multiple
 * @param $until		:  	local for how long;	default = 5 minutes / 300 seconds
 * @param $type			: 	locking type array(0=>r/w,1=r-only , 2=>none) 1:readonly 3: no access
 * @param $try			:  	how many time to try;
 * @param $max			:  	max value of random milisecond to next try;
 * @return locker id	or false can not lock a row
 * @note	:	lock_field [type=char(20)] and lock_time [type=big-int]
 */
function _lock($table, $cond, $try = 55, $max = 25, $lock = 'lock') {
	//xxx ,xxx_ttl, xxx_type for locker field
	$table = $this->_table_name($table);
	$lock_id = uniqid();
	$cond = $this->_condition($table, $cond);
	while ($try-- > 0) {
		//  			$time=microtime(true);
		//  			$t = $time + $until*1000;
		$time = time();
		$t = $time + $until;
		$q = "update 
				`$table` 
				set 
				`$table`.`{$lock}`='$lock_id'
				,`$table`.`{$lock}_ttl`='$t'
				where 
				$cond and ( `$table`.`{$lock}`='' OR `$table`.`{$lock}` IS NULL OR `$table`.`{$lock}_ttl` <= '$time' OR `$table`.`{$lock}_ttl` IS NULL )	 
				limit 1
				";
		$this->query($q);
		//  			usleep(10);		//just for pre-empty queue accessed by other thread
		$q = "select * from `$table` where `$table`.`{$lock}`= '$lock_id' limit 1";
		$this->query($q);
		if ($rm = $this->next_record()) return $lock_id; //I got it ! let's go. return record
		usleep(rand(1, $max)); //TSMA/CD - Thread Sense Multiple Access/Collision Detect ,step back
		
	}
	return false; //can not lock it within $try times
	
}
/**
 * unlock a locked(by '_lock()') row
 *
 * @param  $table	: table name
 * @param  $locker	: locker number
 */
function _unlock($table, $lock_id, $lock = 'lock') {
	$table = $this->_table_name($table);
	$time = microtime();
	$q = "update 
			`$table` 
			set 
			`$table`.`{$lock}`=''
			,`$table`.`{$lock}_ttl`='$time'
			where
			`$table`.`{$lock}`='$lock_id'  
			";
	$this->query($q);
}
function write_lock($table, $arr, $cond = null, $try = 1024) {
	if (!$cond) {
		$key = $this->primary_key_name($table);
		$cond = array($key => $data[$key]);
	}
	$r = $this->get($table, $cond);
	$arr['lock_ttl'] = 0;
	$arr['lock'] = '';
	if (!$r) {
		return $this->insert($table, $arr);
	} else {
		if (!($r = $this->_lock($table, $cond, $try))) return false;
		$this->update($table, $arr, array('lock' => $r));
		return $id;
	}
}
function read_lock($table, $cond, $try = 1024) {
	if (!($r = $this->_lock($table, $cond, $try))) return false;
	$v = $this->get($table, array('lock' => $r));
	$this->_unlock($table, $r);
	return $v;
}
}
//
//
//final class DBbackup implements iDBbackup {
//    private $dbserver,
//        $user,
//        $password,
//        $file,
//        $dbname;
//
//    /**
//     * Parameters needed to backup database
//     *
//     * @param dbServer
//     * @param user
//     * @param password
//     * @param file
//     * @param dbName
//     */
//    public function params($dbServer, $user, $password, $file="default",
//        $dbName=null)
//    {
//        $date         = date("Ymd_Hi");
//        $this->dbserver = $dbServer;
//        $this->user    = $user;
//        $this->password    = $password;
//        $this->file    = ($file=="default") ? "dbbackup_$date.php" :
//            $file;
//        $this->dbname    = $dbName;
//    }
//
//    /**
//     * Backup MySQL server
//     * This would backup the *whole* my sql database except the 'mysql' db
//     * Specific db filter would be added later :)
//     *
//     */
//    public function mysql()
//    {
//        $fd = fopen($this->file, 'w');
//        $t = date("H:i:s");
//        $d = date("Y/m/d");
//        $head = "<?php\n\n
//        /* \$Id: {$this->file},v 1.0 $d $t aedavies Exp $ */
//        /*
//         * MySQL Backup by:
//         * laudarch(Archzilon Eshun-Davies)
//         */
//
//        \$user = '{$this->user}';\n
//        \$password = '{$this->password}';\n
//        \$con = mysql_connect('{$this->dbserver}', \$user, \$password);
//        \n\n";
//
//        fwrite($fd, $head);
//
//        $con    = mysql_connect($this->dbserver, $this->user, $this->password );
//        $dblist = mysql_list_dbs($con);
//        while ($row  = mysql_fetch_object($dblist)) {
//            $db = $row->Database;
//            if ($db !== "mysql") {
//                $dbcreate = "mysql_query(\"CREATE DATABASE `$db` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci\");";
//                fwrite($fd, "$dbcreate\n");
//                $dbselect = mysql_select_db("$db");
//                fwrite($fd, "mysql_select_db('$db');\n");
//                $tbl = mysql_list_tables($db);
//                while ($table = mysql_fetch_row($tbl)) {
//                    $fld = mysql_query("SELECT * FROM `$table[0]`");
//                    if ($fld)
//                        $fields = mysql_num_fields($fld);
//                    else
//                        $fields = 0;
//
//                    unset($tblcreate);
//
//                    $tblcreate = "CREATE TABLE `$table[0]` (";
//                    $arrfield = array();
//                    $arrfieldnum = 0;
//                    for ($i=0; $i<$fields; $i++) {
//                        $fieldinfo = mysql_fetch_field($fld, $i);
//                        unset($fieldname);
//                        $fieldname = $fieldinfo->name;
//                        $arrfield[$arrfieldnum] = $fieldname;
//                        $arrfieldnum++;
//                        $fieldlen = mysql_field_len($fld, $i);
//                        if ($fieldlen=="65535") {
//                            $fieldtype = "TEXT";
//                        } else {
//                            $fieldtype = "VARCHAR($fieldlen)";
//                        }
//                        $tblcreate .= "`$fieldname` $fieldtype NOT NULL";
//                        if ($i !== $fields - 1) {
//                            $tblcreate .= ", ";
//                        }
//                    }
//                    $tblcreate .= ")";
//                    fwrite($fd,"     mysql_query(\"$tblcreate\", \$con);\n" );
//                    $readtable = "SELECT * FROM `$table[0]`";
//                    $retval = mysql_query($readtable);
//
//                    if ($retval)
//                        while ($col = mysql_fetch_row($retval)) {
//                            $insert = "mysql_query(\"INSERT INTO `$table[0]` (";
//                            for ($ifield=0; $ifield<count($arrfield)-1; $ifield++) {
//                                $insert .= "`{$arrfield[$ifield]}`, ";
//                            }
//                            $insert .= "`{$arrfield[$ifield]}`) VALUES(";
//                            for ($icol=0; $icol<count($col)-1; $icol++) {
//                                $col[$icol] = str_replace("\"", "'", $col[$icol]);
//                                $insert .= "'{$col[$icol]}', ";
//                            }
//                            $insert .= "'{$col[$icol]}'";
//                            $insert .= ")\", \$con);";
//                            fwrite($fd, "          $insert\n");
//                        }
//                }
//            }
//        }
//        mysql_close($con);
//
//        $tail = "\nmysql_close(\$con);\n\n
//        ? >\n\n
//        <html><title>MySQL Restoration by laudarch</title><body>\n
//        <font face=\"arial\" size=4 color=\"#000000\"><b>Database Restored!</b></font>\n
//        </body></html>";
//
//        fwrite($fd, $tail);
//        fclose( $fd );
//    }
//}
//
