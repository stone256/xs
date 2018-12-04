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
		$this->set($name, $value);	
	}

	function set($name, $value=''){
		self::$vars[$name] = $value;
		$r = xpTable::load($this->table)->write(array('name'=>$name, 'value'=>$value), array('name'=>$name));
		return $r;
	}

}
