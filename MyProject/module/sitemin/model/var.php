<?php
class sitemin_model_var {
	static $vars = array();

	function __construct() {
		$this->table = 'var';
	}

	function gets(){
		if(!self::$vars) self::$vars = xpAS::key(xpTable::load($this->table)->gets(), 'name');
		return self::$vars;
	}
	function get($name){
		if(!self::$vars) self::$vars = xpAS::key(xpTable::load($this->table)->gets(), 'name');
		return self::$vars[$name]['value'];
	}

	function save($q){
		$name = $q['name'];
		$value = $q['value'];
		$note = $q['note'];
		$this->set($name, $value, $note);
	}

	function set($name, $value='', $note=''){
		self::$vars[$name] = $value;
		$r = xpTable::load($this->table)->write(array('name'=>$name, 'value'=>$value, 'note'=>$note), array('name'=>$name));
		return $r;
	}

}
