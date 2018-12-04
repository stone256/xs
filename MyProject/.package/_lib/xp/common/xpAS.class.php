<?php
/**
 * array and string related function
 * @author  peter wang <xpw365@gmail.com>
 * @version 1.2
 * @package misc.class
 *
 * to fix some php5 array bug bug without using $preserve_keys
 *  and some extend function
 */
class xpAS {
	
	
	/**
	 * switch between https and http request
	 *
	 * @param bolean $https
	 */
	function https_switch($https = true) {
		if (((boolean)$_SERVER['HTTPS'] xor $https)){
			xpAS::go(($https ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
		}
	}
	/**
	 * return _ name from camel case
	 *   getAbcFromDef => get_abc_from_def
	 * @param string $name
	 * @return string
	 */
	function camel2_($name) {
		return strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
	}

	/**
	 * convert number to uid (string)
	 *
	 * @param int $n
	 * @param int $mix
	 * @param int $length
	 * @return string
	 */
	function n2uid($n, $mix = 3, $length = 8) {
		$r = self::padl_length($n, $length + 1, '0');
		$r = substr($r, -$mix) . substr($r, 0, $length - $mix + 1);
		$r = '9' . $r;
		$r = base_convert($r, 10, 34);
		$r = str_replace('0', 'y', $r);
		return strtoupper($r);
	}
	/**
	 * reverse back to number from uid string
	 *
	 * @param string $u
	 * @param int $mix
	 * @param int $length
	 * @return string
	 */
	function uid2n($u, $mix = 3, $length = 8) {
		$r = strtolower($u);
		$r = str_replace('y', '0', $r);
		$r = base_convert($r, 34, 10);
		$r = substr($r, 1);
		$r = substr($r, $mix, $length - $mix + 1) . substr($r, 0, $mix);
		return (int)$r;
	}
	/**
	 * create hash code string from array 
	 *
	 * @param array $arr
	 * @param string $key
	 * @return string
	 */
	function enHash($arr, $key = null) {
		$str = md5(serialize($arr));
		$arr['xpHash'] = self::encode('xphash' . $str);
		$str = serialize($arr);
		$str = self::str2hex($str);
		return $str;
	}
	/**
	 * reverse hash coded string to array
	 *
	 * @param string $str
	 * @param string $key
	 * @return array
	 */
	function deHash($str, $key = null) {
		$str = self::hex2str($str);
		$arr = unserialize($str);
		$hash = $arr['xpHash'];
		unset($arr['xpHash']);
		if (self::decode($hash) != 'xphash' . md5(serialize($arr))) return false;
		return $arr;
	}
	/**
	 * check method or function existed. use string as 'abc::ge'
	 *
	 * @param string $method
	 * @return boolean
	 */
	function method_exist($method) {
		$method = explode('::', $method);
		return count($method) == 1 ? function_exists($method[0]) : method_exists($method[0], $method[1]);
	}
	/**
	 * make a call user 'abc::ge'
	 *
	 * @param string $method
	 * @param array $data
	 * @return mix result from call
	 */
	function method_call($method, $data) {
		$func = strpos($method, '::') ? explode('::', $method) : $method;
		return call_user_func_array($func, $data);
	}

	/**
	 * find duplicated element in 2 dimensional arrsy
	 *
	 * @param array $a
	 * @return duplicated element (k=>v, k1=>v1); (use array_keys or array_values to get keys or values)
	 */
	function find_duplicated($a) {
		array_unshift($a, '|>###$$$###$$$##<|');
		$b = array_flip(array_unique($a));
		foreach ($a as $k => $v) {
			if (isset($b[$v])) {
				unset($a[$k]);
				unset($b[$v]);
			}
		}
		return array_unique($a);
	}
	/**
	 * return item from array least less than (or eq, if equ=true) $value
	 *
	 * @param mix $arr
	 * @param string $value
	 * @param boolean $eq
	 */
	function least_less($arr, $value, $eq = true) {
		if (!is_array($arr)) return $eq ? $arr <= $value : $arr < $value;
		rsort($arr);
		foreach ($arr as $a) if ($eq && $a == $value || $a < $value) return $a;
		return false;
	}
	/**
	 * return item from array least great than (or eq, if equ=true) $value
	 *
	 * @param mix $arr
	 * @param string $value
	 * @param boolean $eq
	 */
	function least_great($arr, $value, $eq = true) {
		if (!is_array($arr)) return $eq ? $arr >= $value : $arr > $value;
		sort($arr);
		foreach ($arr as $a) if ($eq && $a == $value || $a > $value) return $a;
		return false;
	}
	/**
	 * get session, post, get...
	 *
	 * @param mix $arr
	 * @return $arr
	 */
	function system_var($arr = 'session') {
		if (!is_array($arr)) $arr = explode(',', $arr);
		foreach ($arr as $k => $v) eval('$brr[' . $v . ']=$_' . strtoupper($v) . ';');
		return $brr;
	}
	/**
	 * multi level explode
	 *
	 * @param string $string
	 * @param delimiter $dm 	: ";,-,_" or array(',',';')..
	 * @return array
	 */
	function explode($string, $dm = ',') {
		if (!is_array($dm)) {
			if ($dm == ',') $dm = array(',');
			else $dm = explode(',', $dm);
		}
		$d = array_shift($dm);
		$brr = explode($d, $string);
		if (count($dm)) {
			foreach ((array)($brr) as $k => $v) {
				$brr[$k] = self::explode($v, $dm);
			}
		}
		return $brr;
	}
	/**
	 * join arr to string
	 * e.g.  array(a=>5,b=>12) ==> a=5&b=12
	 * @param mix $arr
	 * @param str $d1
	 * @param str $d2
	 */
	function compound($arr, $d1 = '=', $d2 = '&', $encode = true) {
		if (!is_array($arr)) return $arr;
		foreach ($arr as $k => $v) {
			//if(is_numeric($k)) continue;
			if ($encode) $c[] = rawurlencode($k) . $d1 . rawurlencode($v);
			else $c[] = $k . $d1 . $v;
		}
		return implode($d2, $c);
	}
	/**
	 * dissolve string to array
	 * e.g.  array(a=>5,b=>12) <== a=5&b=12
	 * @param string $str
	 * @param str $d1
	 * @param str $d2
	 */
	function dissolve($str, $d1 = '=', $d2 = '&', $decode = true) {
		$f = explode($d2, $str);
		foreach ($f as $k => $v) {
			$g = explode($d1, $v);
			$brr[$decode ? rawurldecode($g[0]) : $g[0]] = $decode ? rawurldecode($g[1]) : $g[1];
		}
		return $brr;
	}
	/**
	 * change array from 
	 * 	array(a=>array(1,2,3), b=>array(b1,b2,b3)....)  
	 * to
	 * 	array(array(a=>1,b=>b1 ..), array(a=>2,b=>b2 ..), array(a=>3,b=>b3 ..))
	 *
	 * @param unknown_type $data
	 */
	function columnize($data){
		$data = (array)$data;
		$columns = array_keys($data);
		foreach ($data[$columns[0]] as $k=>$v){
			foreach ($columns as $kc=>$vc){
				$arr[$k][$vc] = $data[$vc][$k];
			}
		}
		return $arr;
	}	
	/**
	 * change back flaten tree from database;
	 *	note orphan child will be omitted
	 *  method : fruit picking,
	 * @param mix $arr		flat table
	 * @param string $id	name of id
	 * @param string $parent_id name of parent id
	 * @return array of tree or forest if multiple top level nodes
	 */
	function flat2tree($arr, $id_name = 'id', $parent_id_name = 'parent_id', $child_name = 'children') {
		$arr = self::key($arr, $id_name);
		foreach ($arr as $k => $v) {
			if (!$arr[$v[$parent_id_name]]) continue;
			$arr[$v[$parent_id_name]][$child_name][] = & $arr[$k];
			$unset[] = $k;
		}
		foreach ($unset as $u) unset($arr[$u]);
		return $arr;
	}
	/**
	 * reserve tree to flat
	 *
	 * @param array $arr
	 * @param string $child_name : name of child (key)
	 * @param int $level	: max recurrsive level
	 * @param int $current	: current level of recurrsive, do not set
	 */
	function tree2flat($arr, $parent_id = 'root', $parent = 'parent', $child_name = 'children', $level = 50, $currtnt = 0) {
		if ($level == $currtnt || !is_array($arr)) return false;
		foreach ($arr as $k => $v) {
			$arr[$k][$parent] = $parent_id;
			if ($v[$child_name]) {
				$r = self::tree2flat($v[$child_name], $k, $parent, $child_name, $level, $currtnt + 1);
				unset($arr[$k][$child_name]);
				if ($r) $arr = self::extend($arr, $r);
			}
		}
		return $arr;
	}
	/**
	 * use key word mapping to change column name
	 *	$arr =	array(
	 * 		array(a=>10, b=>20),
	 * 		array(a=>11, b=>90),
	 * 		)
	 * 
	 * 	$k = array(age=>a, point=>b)
	 * => 
	 *	$arr =	array(
	 * 		array(age=>10, point=>20),
	 * 		array(age=>11, point=>90),
	 * 		)
	 * 
	 * 
	 * @param array  $arr
	 * @param array $map_name
	 * @return array
	 */
	function mapping($arr, $map_name) {
		if (is_array($arr)) foreach ($arr as $k => $v) foreach ($map_name as $kf => $vf) $rows[$k][$kf] = $v[$vf];
		return $rows;
	}
	/**
	 * first element of array
	 * @param array $arr
	 */
	function first($arr, $value = true) {
		$crr = $arr;
		if (!$value) return each($crr);
		list($k, $v) = each($crr);
		return $v;
	}
	/**
	 * last element of array
	 * @param array $arr
	 */
	function last($arr, $value = true) {
		$crr = array_reverse($arr, true);
		if (!$value) return each($crr);
		list($k, $v) = each($crr);
		return $v;
	}
	/** @ php5 : 
	 *	  1> if one of it is null, it return wrong
	 *	  2> it not retain the key value if the key is number
	 *    3> lose key index if key is number
	 * @param array $arr
	 * @param array $brr
	 * @return array;
	 */
	function merge($arr, $brr, $onduplicated=false) {
		if (!is_array($arr) && !is_array($brr)) return array();
		if (!is_array($arr)) return $brr;
		if (!is_array($brr)) return $arr;
		$crr = array();
		foreach ($arr as $k => $v) $crr[$k] = $v;
		foreach ($brr as $k => $v)  if($onduplicated && isset($crr[$k])) {  $crr[$onduplicated.'_'.$k] = $v; }else{$crr[$k] = $v; }
		return $crr;
	}	
	/**
	 * merge to default setting
	 *
	 * @param array $default
	 * @param array $setting
	 * @return array
	 */
	function setting($default, $setting = array()) {
		foreach ((array)($setting) AS $k => $v) {
			if (is_null($v)) {
				unset($default[$k]);
			} else {
				$default[$k] = $v;
			}
		}
		return $default;
	}
	/**
	 * create a copy a to b and overwrite if b has it
	 * *
	 * @param array $arr	: source
	 * @param array $brr 	: destination
	 */
	function copy($arr, &$brr) {
		$brr = array();
		if (is_array($arr)) foreach ($arr as $k => $v) $brr[$k] = $v;
	}
	/**
	 * swap element
	 *
	 * @param array $arr
	 * @param int/string $i
	 * @param int/string $j
	 */
	function swap($arr, $i, $j) {
		$t = $arr[$j];
		$arr[$j] = $arr[$i];
		$arr[$i] = $t;
		return $arr;
	}
	/**
	 * get number i item
	 *
	 * @param mix $arr
	 * @param int $i
	 * @return mix
	 */
	function item($arr, $i = 1) {
		if ($i > 0) while ($i-- > 0) $a = array_shift($arr);
		if ($i < 0) while ($i++ < 0) $a = array_pop($arr);
		return $a;
	}
	/**
	 * reverse list array
	 *
	 * @param array $arr
	 * @return array
	 */
	function reverse($arr) {
		if (!is_array($arr)) return $arr;
		$brr = array();
		foreach ($arr as $k => $v) {
			$brr = self::unshift($brr, $v, $k);
		}
		return $brr;
	}
	/**
	 * shity php array lost numerica index after unshift
	 *
	 * @param array $arr		: array
	 * @param mix $element	: insert element
	 * @param string $index	: element key
	 * @return array
	 */
	function unshift(&$arr, $element, $index = null) {
		$brr = array();
		if ($index) $brr[$index] = $element;
		else $brr[] = $element;
		if (is_array($arr)) foreach ($arr as $k => $v) $brr[$k] = $v;
		$arr = $brr;
		return $brr;
	}
	/**
	 * shift 1st one : just for match unshift
	 *
	 * @param array $arr
	 * @return array
	 */
	function shift(&$arr) {
		$f = 0;
		if (!is_array($arr)) return null;
		foreach ($arr as $k => $v) {
			unset($arr[$k]);
			return $v;
		}
	}
	/**
	 * get array colmun
	 *
	 * @param array  $arr	: source array
	 * @param string(index/key)  $index : column index
	 * @return array
	 */
	function column($arr, $index) {
		if (is_array($arr)) foreach ($arr as $k => $v) $brr[$k] = $v[$index];
		return $brr;
	}
	/**
	 * return group columns
	 *
	 * @param array $arr	: source array
	 * @param arraye $irr  : index array
	 * @return array
	 */
	function columns($arr, $irr, $dm = ',') {
		if (!is_array($arr)) return false;
		if (!is_array($irr)) $irr = explode($dm, $irr);
		foreach ($arr as $k => $v) {
			$c = array();
			foreach ($irr as $ki => $vi) $c[$vi] = $v[$vi];
			$brr[$k] = $c;
		}
		return $brr;
	}
	/**
	 * insert a column
	 * @param mix $arr
	 * @param mix $key
	 * @param mix $value
	 */
	function insert($arr, $key, $value = null) {
		if (is_array($key)) {
			list($key, $value) = each($key);
		}
		foreach ($arr as $k => $v) {
			$arr[$k][$key] = $value;
		}
		return $arr;
	}
	/**
	 * filter out group columns
	 *
	 * @param array $arr	: source array
	 * @param array $irr  : index array
	 * @return array
	 */
	function cuts($arr, $irr, $dm = ',') {
		if (!is_array($arr)) return false;
		if (!is_array($irr)) $irr = explode($dm, $irr);
		foreach ($arr as $k => $v) {
			$c = array();
			foreach ($v as $ki => $vi) {
				if (!in_array($ki, $irr)) $c[$ki] = $vi;
			}
			$brr[$k] = $c;
		}
		return $brr;
	}
	/**
	 * replace status with displayable
	 *
	 * @param array $arr	: source array
	 * @param arraye $irr  : index array
	 * @return array
	 */
	function display($arr, $index, $replacer) {
		if (!is_array($arr)) return false;
		foreach ($arr as $k => $v) {
			$c = array();
			foreach ($v as $ki => $vi) {
				if ($ki == $index) $c[$ki] = $replacer[$vi];
				else $c[$ki] = $vi;
			}
			$brr[$k] = $c;
		}
		return $brr;
	}
	/**
	 * round off those element from a array
	 *
	 * @param array $arr			: data pool
	 * @param array/string $names	: names of element to be extracted
	 * @param char $dm	: delima
	 * @return array 	:return array of those elements;
	 */
	function round_off($arr, $names, $dm = ',') {
		if (!is_array($names)) $names = explode($dm, $names);
		foreach ((array)$arr as $k => $v) if (!in_array($k, $names)) $brr[$k] = $v;
		return $brr;
	}
	/**
	 * round up those element from a array
	 *
	 * @param array $arr			: data pool
	 * @param array/string $names	: names of element to be extracted
	 * @param char $dm	: delima
	 * @return array 	:return array of those elements;
	 */
	function round_up($arr, $names, $dm = ',') {
		if (!is_array($names)) $names = explode($dm, $names);
		foreach ($names as $k => $v) if (isset($arr[$v])) $brr[$v] = $arr[$v];
		return $brr;
	}
	
	/**
	 * set key to array, use a column as key
	 *
	 * @param array $arr		:  array
	 * @param string $name	: key field name
	 * @return array
	 */
	function key1($arr, $name) {
		if (!is_array($arr)) return null;
		$brr = array();
		foreach ($arr as $k => $v) {
			$brr[$v[$name]] = $v;
		}
		return $brr;
	}
	
	/**
	 * set key to array, use a column as key
	 *
	 * @param array $arr		:  array
	 * @param string $name	: key field name
	 * @return array
	 */
	function key($arr, $name, $dm = ',') {
		if (!is_array($arr)) return null;
		$brr = array();
		foreach ($arr as $k => $v) {
			if ($m = self::round_up($v, $name, $dm)) $m = implode($dm, $m);
			$brr[$m] = $v;
		}
		return $brr;
	}
	/**
	 * key on multi-dimension array
	 *	eg. $r = self::key_on($r,'*,attr,Code');
	 * @param array $arr
	 * @param string $name :xp array (',')
	 * @return $array with  k0 =  name;
	 */
	function key_on($arr, $name, $dm = ',') {
		$ks = self::get($arr, $name, $dm);
		if (is_array($ks)) return array_combine($ks, $arr);
		return null;
	}
	/**
	 * this is mainly to filtering $_REQUEST
	 *
	 * @param array $arr		: array to be filted
	 * @param array $filter		: filter array ( key name )
	 * @param boolean $on		: filter type (on: only keys; off: not keys)
	 */
	function filter($arr, $filter, $on = true) {
		if (is_array($arr) && is_null($filter)) return $on ? array() : $arr;
		$brr = array();
		foreach ($arr as $k => $v) {
			if (in_array($k, $filter) ^ $on) $brr[$k] = $v;
		}
		return $brr;
	}
	/**
	 * find a value by key name in array
	 * 	use for looking xml array
	 *
	 * @param array $arr
	 * @param mix $key
	 */
	function find($arr, $keys, $dm = ',') {
		$ks = explode($dm, $keys);
		foreach ($ks as $k => $v) {
			$arr = self::_find($arr, $v);
		}
		return $arr;
	}
	function _find($arr, $keyValue) {
		if (!is_array($arr)) return false;
		foreach ($arr as $k => $v) {
			if ((string)$k == $keyValue) {
				return $v;
			}
			if (is_array($v) && ($r = self::_find($v, $keyValue)) !== false) {
				return $r;
			}
		}
		return false;
	}
	/**
	 * go in deep to get value from an array
	 *
	 * @param array  $arr :  source array
	 * @param array or string  $irr : index array or string in format  "i1,i2,i3,i4.. " - ix name of index
	 * @param  char  $dm :  column delimiter default: ","
	 * @return node of array
	 */
	function get($arr, $irr = null, $dm = ',', $level = 50, $current = 0) {
		if ($level == $current) return;
		if (!is_array($irr)) $irr = explode($dm, $irr);
		if (!count($irr)) return $arr;
		if (!(is_array($arr) || is_object($arr))) return null;
		$i = self::shift($irr);
		$regx = false;
		if (strpos($i, '*') !== false || strpos($i, '?') !== false || ($regx = strpos($i, '/'))) {
			if (!$regx) {
				$i = str_replace('?', '.{1}', $i);
				$i = str_replace('*', '.*?', $i);
				$i = '/' . $i . '/';
			}
			foreach ($arr as $k => $v) {
				if (preg_match($i, $k)) {
					if ($m = self::get(is_array($arr) ? $arr[$k] : $arr->$k, $irr, $dm, $level, $current + 1)) {
						$b[$k] = $m;
					}
				}
			}
			return $b;
		} else {
			return self::get(is_array($arr) ? $arr[$i] : $arr->$i, $irr, $dm, $level, $current + 1);
		}

	}
	/**
	 * get row in multi-dimension array , if field in the row has value=$value
	 *
	 * @param array $arr		: searched object
	 * @param string $field 	: field name
	 * @param string $value 	: search value;
	 * @param int	$level		: max recurring level
	 * @param int $current		: !do not set !!, current recurring level
	 *
	 * @return array or null
	 */
	function grab($arr, $field, $value, $level = 50, $current = 0) {
		if ($current == $level || !is_array($arr)) return null; //hit max level
		foreach ($arr as $k => $v) {
			if (!is_array($v) && $v[$field] == $value) return $v;
			if (is_array($v) && ($m = self::grab($v, $filed, $value, $level, $current + 1))) return $m;
		}
		return null;
	}
	
	function value_search($arr, $value, $level = 50, $current = 0){
		if ($current == $level || !is_array($arr)) return null; //hit max level
		$search = '/'.preg_quote($value).'/ims';
		$array = true;
		foreach($arr as $k=>$v){
			if(!is_array($v)) $array = false;
		}
		foreach($arr as $k=>$v){
			if (!is_array($v) && preg_match($search, $v)) return $arr;
			if (is_array($v) && ($m = self::value_search($v, $value, $level, $current + 1))) if($array) $brr[$k] = $m; else  return $arr; 
		}
		return $brr;
	}
	
	/**
	 * locate position in multi-dimension array , return key e.g.locate(products, postcode, 2000) => "2,address,1" 
	 *
	 * @param array $arr		: searched object
	 * @param string $field 	: field name
	 * @param string $value 	: search value;
	 * @param int	$level		: max recurring level
	 * @param int $current		: !do not set !!, current recurring level
	 *
	 * @return array or null
	 */
	function locate($arr, $field, $value, $level = 50, $current = 0) {
		if ($current == $level || !is_array($arr)) return null; //hit max level
		foreach ($arr as $k => $v) {
			if ((is_array($v) ? $v[$field] : $v->$field) == $value) {
				return $k;
			}
			if (is_array($v) && ($m = self::locate($v, $field, $value, $level, $current + 1))) return "$k,$m";
		}
		return null;
	}
	/**
	 * extract element if it key has value, most apply to data from database e,g. extract($data, age, 25)
	 *
	 * @param array $arr		: searched object
	 * @param string $field 	: field name
	 * @param string $value 	: search value;
	 * @param int	$level		: max recurring level
	 * @param int $current		: !do not set !!, current recurring level
	 * @return array or null
	 */
	function extract($arr, $field, $value, $level = 50, $current = 0) {
		if ($current == $level || !is_array($arr)) return null; //hit max level
		$brr = array();
		foreach ($arr as $k => $v) {
			if ($v[$field] == $value) $brr[] = $v;
			else if (is_array($v) && ($m = self::grab($v, $filed, $value, $level, $current + 1))) $brr = $v;
		}
		return $brr;
	}
	/**
	 * get value by first element with key regardless what levels they are
	 *
	 * @param array $arr		: array
	 * @param string $key		: key name
	 * @param int	$level		: max recurring level
	 * @param int $current		: !do not set !!, current recurring level
	 * @return value of found element or null
	 */
	function value($arr, $key, $level = 50, $current = 0) {
		if ($current == $level || !is_array($arr)) return null;
		foreach ($arr as $k => $v) {
			if ($k === $key) return $v;
			if (is_array($v) && ($m = self::value($v, $key, $level, ++$current))) return $m;
		}
		return null;
	}
	
	/**
	 * get all values with key in m-array regardless what levels they are
	 *
	 * @param array $arr	:
	 * @param string $key	: key name
	 * @param int $level	: max depth
	 * @param iont $current	: do not set
	 * @return array
	 */
	function values($arr, $key, $level = 50, $current = 0) {
		$r = array();
		if ($current == $level || !is_array($arr)) return $r;
		foreach ($arr as $k => $v) {
			if ($k === $key) $r[] = $v;
			if (is_array($v)) {
				$r = self::extend($r, self::values($v, $key, $level, $current + 1));
			}
		}
		return $r;
	}
	/**
	 * setting value to an  array
	 *  note this one call by refs!!!
	 *
	 * @param array  $arr :  source array
	 * @param array or string  $irr : index array or string in format  "i1,i2,i3,i4.. " - ix name of index
	 * @param  max  $value : need set value
	 * @param  char  $dm :  column delimiter default: ","
	 * @param int	$level		: max recurring level
	 * @param int $current		: !do not set !!, current recurring level
	 */
	function set(&$arr, $irr, $value, $dm = ',', $level = 50, $current = 0) {
		if ($level == $current) return;
		if (!is_array($irr)) $irr = explode($dm, $irr);
		if (!is_array($arr)) $arr = array();
		if (count($irr) == 1) {
			$i = $irr[0];
			$regx = false;
			if (strpos($i, '*') !== false || strpos($i, '?') !== false || ($regx = strpos($i, '/'))) {
				if (!$regx) {
					$i = str_replace('?', '.{1}', $i);
					$i = str_replace('*', '.*?', $i);
					$i = '/' . $i . '/';
				}
				$i = str_replace('?/', '/', $i);
				foreach ($arr as $k => $v) {
					if (preg_match($i, $k)) {
						if (is_null($value)) {
							unset($arr[$k]);
						} else $arr[$k] = $value;
					}
				}
			} else {
				if (is_null($value)) unset($arr[$i]);
				else $arr[$i] = $value;
			}
		} else {
			$i = array_shift($irr);
			$regx = false;
			if (strpos('*', $i) !== false || strpos('?', $i) !== false || ($regx = strpos($i, '/'))) {
				if (!$regx) {
					$i = str_replace('*', '.*?', $i);
					$i = str_replace('?', '.{1}', $i);
					$i = '/' . $i . '/';
				}
				foreach ($arr as $k => $v) {
					if (preg_match($i, $k)) self::set($arr[$k], $irr, $value, $dm, $level, ++$current);
				}
			} else {
				self::set($arr[$i], $irr, $value, $dm, $level, ++$current);
			}
		}
	}
	
	
	/**
	 * pack multi-dimensional array to one level with flat key e.g. ['ab,cd']
	 *
	 * @param array $arr
	 * @param string $return :  false= return one level key=>value array, 'key' : return its' keys; 'value' : return its' values
	 * @param int $level	: max depth
	 * @param int $current	: do not set
	 * @return mix
	 */
	function pack($arr, $return=false, $level = 50, $current = 0){ 
		if ($level == $current) return array('max-depth'=>json_encode($arr));
		if(!is_array($arr)) return $arr;
			
		$brr = array();
		foreach ($arr as $k=>$v){
			if(!is_array($v)) $brr[$k] = $v;
			else foreach (self::pack($v, $flat, $level, $current + 1) as $ks=>$vs) $brr["$k,$ks"] = $vs;
		}
		
		switch ($return){
			case 'key' : return array_keys($brr);
			case 'value' : return array_values($brr);
			default: /*return $brr*/;
		}
		return $brr;
		
	}
	
	/**
	 * clear a key in multi - demansion
	 * @param array $arr	: array;
	 * @param string $key	: key
	 */
	function key_clear($arr, $key_array) {
		foreach ((array)$key_array as $k=>$v){
			 self::set($arr,$v, null);
		}
		return $arr;
	}
	/**
	 * get a element by key name
	 *
	 * @param array $arr	: array;
	 * @param string $key	: key	flat key array. e.g. "user,name", "catalog,category,products,multi-tag"
	 */
	function key_gets($arr, $key_array) {
		foreach ((array)$key_array as $k=>$v){
			if(is_null($b = self::get($arr,$v))) continue;
			$brr[$v] = $b;
		}
		return $brr;
	}
	/**
	 * test value in multi dimension array
	 *
	 * @param type $arr
	 * @param value  $value searched value
	 * @param value  $key_name $key name  if search by key
	 * @return return key . false if not found , test using:  ===false !!! (key maybe 0
	 * @param int	$level		: max recurring level
	 * @param int $current		: !do not set !!, current recurring level
	 * @return  boolean
	 */
	function has($arr, $value, $key_name = false, $level = 50, $current = 0) {
		if (!is_array($arr)) return false;
		foreach ($arr AS $k => $v) {
			if ($key_name !== false && $k === $key_name) return true;
			if (is_array($v)) {
				if (self::has($v, $value, $key_name, $level, $current + 1)) return true;
			} else {
				if ($value === $v) return true;
			}
		}
		return false;
	}
	/**
	 * join array a and b 's element as c[i] = array(a[i],b[i])
	 *
	 * @param array $a
	 * @param array $b
	 * @return array
	 */
	function combine($a, $b) {
		if (!is_array($a)) return;
		foreach ($a as $k => $v) $crr[] = array($a[$k], $b[$k]);
		return $crr;
	}
	/**
	 * extend array a to b     c= a + b
	 *
	 * @param array $a
	 * @param array $b
	 * @return array
	 */
	function extend($a, $b) {
		if (is_array($b)) foreach ($b as $v) $a[] = $v;
		return $a;
	}
	/**
	 * add group of element to an array only when no null element in the group
	 *	''-included
	 * @param array $arr
	 * @param  element $argvs
	 * @return  by &ref
	 */
	function add_not_empty($arr) {
		$argv = func_get_args();
		if (($ct = count($argv)) < 2) return;
		for ($i = 1;$i < $ct;$i++) {
			if (!$argv[$i] && !trim($argv[$i])) return;
			$b[] = $argv[$i];
		}
		$arr = self::extend($arr, $b);
		return $arr;
	}
	/**
	 * only add ext if not empty
	 *
	 * @param string $str
	 * @param string $ext
	 * @return string
	 */
	function add_not_null($str, $ext) {
		return $str ? $str . $ext : null;
	}
	/**
	 *  implode to string with empty one.  ''- included
	 *
	 * @param array $arr
	 * @param string $dm
	 * @return array
	 */
	function implode_not_empty($arr, $dm = ',') {
		$arr = self::clean($arr);
		return is_array($arr) ? implode($dm, $arr) : array();
	}
	/**
	 * clean empty element
	 *
	 * @param array $arr		:array to clean
	 * @param int $deep		:deapth of array
	 * @param int $level		: no not set this
	 * @return array
	 */
	function clean($arr, $deep = 50, $level = 0) {
		if ($level == $deep) return;
		foreach ($arr as $k => $v) {
			if (is_array($v)) $arr[$k] = self::clean($v, $deep, $level + 1);
			if ($v === null || $v === '') unset($arr[$k]);
		}
		return $arr;
	}
	/**
	 * apply trim to each array element
	 *
	 * @param array $arr
	 * @param int	$level		: max recurring level
	 * @param int $current		: !do not set !!, current recurring level
	 * @return array
	 */
	function trim($arr, $level = 50, $current = 0) {
		return self::walk($arr, 'trim', false, $level);
	}
	/**
	 * change flat array to stack array
	 * e.g.
	 * 	array(a=>12,b=>13)
	 * array(
	 * array(a=>12),
	 * array(b=>13)
	 * )
	 *	this is mostly for xpTemplate
	 * @param array $arr
	 */
	function array_stack($arr) {
		if (!is_array($arr)) return array();
		foreach ($arr as $k => $v) $brr[] = array('k' => $k, 'v' => $v);
		return $brr;
	}
	/**
	 * to format a element as array if is not a array;
	 * 	make it number indexed array e.g.  array('a'=>5,b=>23) ==> array( array('a'=>5,b=>23)) ;
	 *  push down one level
	 * designed for xml to array function
	 * @param mix $arr
	 * @return array
	 */
	function array_array($arr) {
		return $arr[0] ? $arr : array($arr);
	}
	/**
	 * if not array make it array first element
	 * @param mix $arr
	 * @return array
	 */
	function array_me($arr) {
		return is_array($arr) ? $arr : array($arr);
	}
	/**
	 * split to array if is not array 'as,b,c' =>array('as','b','c')
	 *
	 * @param string or array $arr : input
	 * @param  string $dm	: spliter
	 * @return array
	 */
	function arraylize($arr, $dm = ',') {
		if (!is_array($arr)) $arr = self::split($arr, $dm);
		return $arr;
	}
	/**
	 * put element in a ring pipe
	 * 	if element more than count;
	 * 	 1st element will poped out
	 *
	 * @param  $arr	: the ring
	 * @param  $value	: element
	 * @param  $size 	: number of element will hold
	 * @return array of the pipe
	 */
	function ring($arr, $value, $size = 12) {
		if (!is_array($arr)) $arr = array();
		array_push($arr, $value);
		while (count($arr) >= $size) array_shift($arr);
		return $arr;
	}
	/**
	 * walk around array and do something to each element
	 *
	 * @param mix $a		: array
	 * @param string $func_name	: function name
	 * @param string $key 		: =false , not to apply to  key
	 * @param int $level		: max recurring level
	 * @param int $current_level	: !Do not set !!, recurring level indicator,
	 * @return mix
	 */
	function walk($a, $func_name, $key = false, $level = 50, $current = 0) {
		if (!is_array($a)) return $key === false ? $func_name($a) : $func_name($a, $key);
		if ($level == $current) return json_encode($a);
		foreach ($a AS $k => $v) $a[$k] = self::walk($v, $func_name, $key === false ? false : $k, $level, $current + 1);
		return $a;
	}
	/**
	 * escape string 
	 *
	 * @param mix $a
	 * @param int $level		: max recurring level
	 * @return mix
	 */
	function escape($a, $level = 50) {
		return self::walk($a, 'addslashes', false, $level);
	}
	/**
	 * change uc word in array
	 *
	 * @param mix $a
	 * @param int $level
	 * @param  $currrent
	 */
	function ucwords($a, $level = 50, $currrent = 0) {
		if ($currrent >= $level) return ucwords(json_encode($a));
		if (is_array($a)) {
			foreach ($a as $k => $v) $a[$k] = self::ucwords($v, $level, $currrent + 1);
		} else {
			$a = ucwords(str_replace(array('/', '_'), array(' ', ' '), $a));
		}
		return $a;
	}
	/**
	 * strip tag within array
	 *
	 * @param array  $arr	:array or single element
	 * @param string $keep: tag to be keept
	 * @param int	$level		: max recurring level
	 * @param int $current		: !do not set !!, current recurring level
	 * @return array
	 */
	function strip($arr, $keep = '', $level = 50, $current = 0) {
		if (!is_array($arr)) return strip_tags($arr, $keep);
		if ($level == $current) return strip_tags(json_encode($arr), $keep);
		foreach ($arr AS $k => $v) $arr[$k] = self::strip_tag($v, $keep, $level, ++$current);
		return $arr;
	}
	/**
	 * apply addslash to each element also the key!!
	 *
	 * @param arr $arr
	 * @param int	$level		: max recurring level
	 * @param int $current		: !do not set !!, current recurring level
	 * @return array
	 */
	function slash($arr, $level = 50, $current = 0) {
		if (!is_array($arr)) return strpos($arr, "\\") === false ? addslashes($arr) : $arr; //do not double slash
		if ($level == $current) return addslashes(json_encode($arr));
		foreach ($arr as $k => $v) {
			unset($arr[$k]);
			$arr[self::slash($k) ] = self::slash($v, $level, ++$current);
		}
		return $arr;
	}
	/**
	 * strip slash in array elements
	 *
	 * @param array $arr
	 * @param int $level	: max depth
	 * @param int $current	: do not set
	 * @return array
	 */
	function stripslash($arr, $level = 50, $current = 0) {
		if (!is_array($arr)) return stripslashes($arr); //do not double slash
		if ($level == $current) return stripslash(json_encode($arr));
		foreach ($arr as $k => $v) {
			unset($arr[$k]);
			$arr[self::stripslash($k) ] = self::stripslash($v, $level, ++$current);
		}
		return $arr;
	}
	/**
	 * encode html in all array elements
	 *
	 * @param mix $arr
	 * @param int $deep		: recurring depth
	 * @param int $level		: do not set
	 * @return mix
	 */
	function html($arr, $deep = 50, $level = 0) {
		if ($level == $deep) return json_encode($arr);
		if (!is_array($arr)) return htmlentities(strip_tags($arr));
		$level++;
		foreach ($arr as $k => $v) {
			$arr[$k] = self::html($v, $deep, $level);
		}
		return $arr;
	}
	/**
	 * multi-dimension array sort on a level 1 element
	 *
	 * @param array  $arr		:array to be sort
	 * @param string/int $keyname: keyfield name : level 1 element's keyname
	 * @param boolean  $descent : sort by ascent(default) or descent
	 * @return array
	 */
	function sort_on($arr, $keyname, $descent = false, $temp_key = 'temp_key_for_sort') {
		foreach ($arr as $k => $v) {
			$key = implode(chr(1), xpAS::round_up($v, $keyname));
			//_debug($key);
			$arr[$k][$temp_key] = preg_replace('/[\.|\s]+/', '', $key . '.' . microtime());
		}
		$arr = self::key($arr, $temp_key);
		$a = $descent ? krsort($arr) : ksort($arr);
		$arr = self::cuts($arr, $temp_key);
		return array_values($arr);
	
	}
	/**
	 * multi-dimension array sort on any level key	*,*,*,key1
	 *
	 * @param array  $arr		: array to be sort
	 * @param string/int $keys	: keys : "*,f,*,key"
	 * @param boolean  $descent	: sort by ascent(default) or descent
	 * @return array
	 */	
	function sort($arr, $keys, $descent = false) {
		$_REQUEST['_xpAS_sort'] = array('key' => $keys, 'descent' => $descent);
		function cmp($a, $b) {
			$va = xpAS::get($a, $_REQUEST['_xpAS_sort']['key']);
			$vb = xpAS::get($b, $_REQUEST['_xpAS_sort']['key']);
			if ($va == $vb) return 0;
			$sign = $_REQUEST['_xpAS_sort']['descent'] ? -1 : 1;
			return $va < $vb ? -1 : 1;
		};
		usort($arr, 'cmp');
		return $arr;
	}
	/**
	 * force in a range..
	 *
	 * @param string $a
	 * @param string $from
	 * @param string $to
	 * @return string
	 */
	function confine($a, $from, $to) {
		if ($a < $from) return $from;
		if ($a > $to) return $to;
		return $a;
	}
	/**
	 * if in a range
	 *
	 * @param string $a
	 * @param string $from
	 * @param string $to
	 * @return string
	 */
	function is_in($a, $from, $to) {
		if ($a < $from || $a > $to) return false;
		return true;
	}
	/**
	 * add / at end of direct if doese not have one
	 *  /var/ww/eg => /var/ww/eg/
	 *
	 * @param string $str
	 * @return string
	 */
	function path($str) {
		return self::path_short(preg_replace('/(?<!:)\/\//', '/', $str . '/'));
	}

	/**
	 * get string from content using preg
	 * the name may bite on the ass
	 *
	 * @param string $regx		: pattern
	 * @param string $str		: string
	 * @param string $pos		: which tmp to return
	 * @return string
	 */
	function preg_get($str, $regx, $pos = 0) {
		preg_match($regx, $str, $m);
		return $pos >= 0 ? $m[$pos] : $m;
	}
	/**
	 * get all matched string from content using preg
	 * the name may bite on the ass
	 *
	 * @param string $regx		: pattern
	 * @param string $str		: string
	 * @param string $pos		: which tmp to return
	 * @return array
	 */
	function preg_gets($str, $regx, $pos = 0) {
		preg_match_all($regx, $str, $m);
		return $pos >= 0 ? $m[$pos] : $m;
	}
	/**
	 * replace group things inside a string
	 * use preg_replace
	 *
	 * @param replace $arr =array('needle'=>'replacer'...)
	 * @param string $str
	 * @return string
	 */
	function replace($str, $arr) {
		if (!is_array($arr)) return false;
		foreach ($arr as $k => $v) $str = preg_replace($k, $v, $str);
		return $str;
	}
	/**
	 * split by $dm with quote
	 *
	 * @param string $str
	 * @param char $dm
	 * @param string $replace :
	 */
	function split($str, $dm = ',', $replace = '#@-@#') {
		$way = '/([\'|\"|\/]|\().*?(?<!\\\)(\1|\))/i';
		preg_match_all($way, $str, $tmp);
		$quotes = $tmp[0];
		$str = preg_replace($way, $replace, $str);
		if ($dm{0} != '/') $dm = '/' . preg_quote($dm) . '/i';
		$s = preg_split($dm, $str);
		foreach ($quotes as $k => $v) {
			foreach ($s as $ks => $vs) {
				if (preg_match($replace, $vs)) {
					$s[$ks] = preg_replace('/' . $replace . '/', $v, $vs, 1);
					break;
				}
			}
		}
		return $s;
	}
	/**
	 * check is quoted
	 *
	 * @param string $str
	 * @return string
	 */
	function is_quoted($str) {
		return preg_match('/^\'.*\'$|^\".*\"$|^\(.*\)$/', $str);
	}
	/**
	 * add \ to quote string. e.g.  abc's  => abc\'s
	 *
	 * @param string $str
	 * @param char $keep 	: to keep
	 * @return string
	 */
	function de_quote($str, $keep = "") {
		$q = array("''" => '^\'.*\'$', '""' => '^\".*\"$', '()' => '^\(.*\)$',);
		unset($q[$keep]);
		$q = '/' . implode('|', $q) . '/';
		//if(preg_match('/^\'.*\'$|^\".*\"$|^\(.*\)$/',$str)){
		if (preg_match($q, $str)) {
			return substr($str, 1, strlen($str) - 2);
		}
		return $str;
	}
	
	/**
	 * cancel the null elements
	 * 	mostly for amfphp zamf to convert flex object
	 *
	 * @param mix $arr
	 * @return mix
	 */
	function de_null($arr) {
		if (is_array($arr) || is_object($arr)) {
			foreach ($arr as $k => $v) {
				$b = self::de_null($v);
				if (is_array($arr)) $arr[$k] = $b;
				else $arr->$k = $b;
			}
		} else {
			if (is_null($arr)) $arr = '';
		}
		return $arr;
	}
	
	/**
	 * check is wildcarded string 
	 *
	 * @param string $str
	 * @return string
	 */
	function is_wildcard($str) {
		return preg_match('/(?<![\\\])[\?|\*]/', $str);
	}
	

	/**
	 * brief of info
	 *
	 * @param string $str
	 * @param int $len	: max length
	 * @param boolen $word: stop at word
	 * @return  shorten string
	 */
	function brief($str, $len = 255, $word = true) {
		$s = substr($str, 0, $len);
		if (strlen($s) > $len) $s = substr($s, 0, $len);
		if ($word) $s = self::preg_get($s, '/(.*)[\,|\?|\s|\.|\!]/ims');
		return $s . '...';
	}
	/**
	 * padding to right to fix length
	 *
	 * @param string $s		: string to fix length
	 * @param int $n		: length
	 * @param char/string $c	: pad char
	 * @return string
	 */
	function padr_length($s, $n, $c = '&nbsp;') {
		$m = $n - strlen($s);
		if ($m < 0) $s = substr($s, 0, $n);
		else $s.= str_repeat($c, $m);
		return $s;
	}
	/**
	 * padding to left to fix length
	 *
	 * @param string $s		: string to fix length
	 * @param int $n		: length
	 * @param char/string $c	: pad char
	 * @return string
	 */
	function padl_length($s, $n, $c = '&nbsp;') {
		$m = $n - strlen($s);
		if ($m < 0) $s = substr($s, 0, $n);
		else $s = str_repeat($c, $m) . $s;
		return $s;
	}
	/**
	 * return data by input order
	 *
	 * @param list of vars as priority_get($a,$b,$c...)..
	 * @return unknown
	 */
	function priority_get() {
		$n = func_num_args();
		for ($i = 0;$i < $n;$i++) {
			$d = func_get_arg($i);
			if (trim($d) !== '' && $d !== null) return $d;
			//if(isset($d)) return $d;
			
		}
		return null;
	}
	/**
	 * add a char to end of string if not there
	 * use for path ..
	 *
	 * @param string $str
	 * @param char $tc
	 * @return string
	 */
	function tail($str, $tc) {
		if ($str{strlen($str) - 1} !== $tc) $str.= $tc;
		return $str;
	}
	/**
	 * blank of cc number
	 *
	 * @param str $ccn
	 */
	function cc_blank($ccn, $m = 8, $t = 4) {
		$ccn = preg_replace('/\s/', '', $ccn);
		return preg_replace('/(\d*)(\d{' . $m . '})(\d{' . $t . '})/', "$1********$3", $ccn);
	}
	/**
	 * reading string unitil $del; or to the end, if no $del
	 *
	 * @param string $str
	 * @param string $delimiter
	 * @param boolean $from_end	:reading derection
	 * @return string
	 */
	function read_til($str, $del, $from_end = false) {
		if ($from_end) $i = strrpos($str, $del);
		else $i = strpos($str, $del);
		if ($i === false) return $str;
		return substr($str, 0, $i);
	}
	/**
	 *reading string after $del; or from the beginning, if no $del
	 *
	 * @param string $str
	 * @param string $del
	 * @param boolean $from_end
	 * @return string
	 */
	function read_after($str, $del, $from_begin = false) {
		if ($from_begin) $i = strpos($str, $del);
		else $i = strrpos($str, $del);
		if ($i === false) return null;
		return substr($str, $i + strlen($del));
	}
	/**
	 * read from anchor
	 *
	 * @param string $str
	 * @param string $ptn
	 * @param noolean $after
	 * @param position $c
	 * @return string
	 */
	function read_from($str, $ptn, $after = false, $c = 0) {
		if ($ptn{0} == '/') {
			//do preg
			if (!preg_match_all($ptn, $str, $t)) return false;
			$ptn = $t[0][$c];
		} else {
			if ($ptn{0} == "\\" && $ptn{1} == "/") $ptn = substr(2);
		}
		if (($i = strpos($str, $ptn)) === false) return false;
		return ($after ? $ptn : '') . substr($str, $i); //+($after?-strlen($ptn):0));
		
	}
	/**
	 * read til anchor
	 *
	 * @param string $str
	 * @param string $ptn
	 * @param noolean $after
	 * @param position $c
	 * @return string
	 */
	function read_to($str, $ptn, $after = false, $c = 0) {
		if ($ptn{0} == '/') {
			//do preg
			if (!preg_match_all($ptn, $str, $t)) return false;
			$t = array_reverse($t[0]);
			$ptn = $t[$c];
		} else {
			if ($ptn{0} == "\\" && $ptn{1} == "/") $ptn = substr(2);
		}
		if (($i = strpos($str, $ptn)) === false) return false;
		return substr($str, 0, $i) . ($after ? $ptn : '');
	}
	
	/**
	 * msak out credit card number for safe display
	 *
	 * @param string $str	: card number
	 * @param int $start	: start place to mask
	 * @param int $len	: end place to mask
	 * @param char $p	: masked with
	 * @returnstring
	 */
	function ccmask($str, $start = 4, $len = 4, $p = '*') {
		$stars = self::padl_length('', $len, $p);
		$pattern = '/(.{' . preg_quote($start) . '})(.{' . preg_quote($len) . '})(.*)/ims';
		//return $pattern;
		return preg_replace($pattern, '$1' . $stars . '$3', $str);
	}
	
	/**
	 * change string to hex string, for safe encode string during transport.
	 *
	 * @param string $string
	 * @return hex string
	 */
	function str2hex($string) {
		$hex = '';
		for ($i = 0;$i < strlen($string);$i++) {
			$h = '0' . dechex(ord($string[$i]));
			$hex.= substr($h, -2);
		}
		return $hex;
	}
	
	
	/**
	 * change hex to string. this is mostly to reverse str2hex
	 *
	 * @param string $hex	: hex value string e.g. 33ef89..
	 * @return string
	 */
	function hex2str($hex) {
		$string = '';
		for ($i = 0;$i < strlen($hex);$i+= 2) {
			$string.= chr(hexdec($hex[$i] . $hex[$i + 1]));
		}
		return $string;
	}
	
	/**
	 * break long string (for table td)
	 *
	 * @param string $str
	 * @param int $len
	 * @return string after break;
	 */
	function breaks($str, $len = 60) {
		$b = '';
		while (strlen($str) > $len) {
			$b.= substr($str, 0, $len) . ' ';
			$str = substr($str, $len);
		}
		$b.= $str;
		return $b;
	}
	/**
	 * encode a string
	 *
	 * @param string $str
	 * @param string $key
	 * @param php flag $cipher
	 * @param php flag $mode
	 * @param php flag $source
	 * @return string
	 */
	function encode($str, $key = 'this is default key:~!@#$%^&*()-=|', $cipher = MCRYPT_BLOWFISH, $mode = MCRYPT_MODE_ECB, $source = MCRYPT_RAND) {
		return mcrypt_encrypt($cipher, $key, base64_encode($str), $mode, mcrypt_create_iv(mcrypt_get_iv_size($cipher, $mode), $source));
	}
	/**
	 * decode a string
	 *
	 * @param string $str
	 * @param string $key
	 * @param php flag $cipher
	 * @param php flag $mode
	 * @param php flag $source
	 * @return string
	 */
	function decode($str, $key = 'this is default key:~!@#$%^&*()-=|', $cipher = MCRYPT_BLOWFISH, $mode = MCRYPT_MODE_ECB, $source = MCRYPT_RAND) {
		$rstr = base64_decode(mcrypt_decrypt($cipher, $key, $str, $mode, mcrypt_create_iv(mcrypt_get_iv_size($cipher, $mode), $source)));
		if ($rstr == 'Z') $rstr = ''; //error
		return $rstr;
	}
	/**
	 * simple encryption
	 *   y = f(x,k) and x= f(y,k);
	 * @param $str		: encrypte content
	 * @param $key	: key word
	 * @param $keylength	: minium key block size;
	 * @return encrypted content
	 */
	function roller($str, $key = "this_is-packg~length", $keylength = 128) {
		// * product block cipher  */
		$v = str_split($key);
		$cipher = $v;
		while (count($cipher) < $keylength) {
			$cipher = array_merge($cipher, array_reverse($cipher));
		}
		$keylength = count($cipher);
		// * xor string;  */
		$len = strlen($str);
		for ($i = 0;$i < $len;$i++) {
			$str{$i} = $str{$i} ^ ($cipher[$i % $keylength]);
		}
		return $str;
	}
	/**
	 * generate password
	 *
	 * @param int $len
	 * @param  string $cs	: chars of code
	 * @return string
	 */
	function password_generator($len = 8, $cs = null) {
		if (!$cs) $cs = '01234567890abcdefghijklmnopqrstuvwxyz01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890~!@#$%^&*()_+=-[]{}<>,.?';
		$cs = is_array($cs) ? $cs : str_split($cs);
		$range = count($cs) - 1;
		for ($i = 0;$i < $len;$i++) {
			$p.= $cs[mt_rand(0, $range) ];
		}
		return $p;
	}
	/**
	 * start session
	 *
	 * @param boolean $new_id : if need new id
	 */
	function session_start($new_id = false) {
		if ($new_id) session_regenerate_id();
		session_start();
	}
	/**
	 * test a var status
	 *
	 * @param var $a
	 * @return array
	 */
	function test($a) {
		$t = array('value' => "'$a'", 'isset' => isset($a) ? 'y' : 'n', 'empty' => empty($a) ? 'y' : 'n', 'is_null' => is_null($a) ? 'y' : 'n', '$a?' => $a ? 'y' : 'n', '$a===false' => $a === false ? 'y' : 'n', '$a==""' => $a == "" ? 'y' : 'n', '$a===""' => $a === "" ? 'y' : 'n', '$a==0' => $a == 0 ? 'y' : 'n', '$a===0' => $a === 0 ? 'y' : 'n',);
		return $t;
	}
	/**
	 * url write
	 *
	 * @param array $arr   :  name,value pair
	 */
	function url_append_var($arr) {
		if (is_array($arr)) foreach ($arr as $k => $v) output_add_rewrite_var($k, $v);
	}
	/**
	 * xml2array() will convert the given XML text to an array in the XML structure.
	 * Link: http://www.bin-co.com/php/scripts/xml2array/
	 * Arguments : $contents - The XML text
	 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
	 * Return: The parsed XML in an array form.
	 */
	function xml2array(&$contents, $get_attributes = true, $array_element = true) {
		if (!$contents) return array();
		if (!function_exists('xml_parser_create')) {
			//print "'xml_parser_create()' function not found!";
			return array();
		}
		//Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $contents, $xml_values);
		xml_parser_free($parser);
		if (!$xml_values) return; //Hmm...
		//Initializations
		$xml_array = array();
		$parents = array();
		$opened_tags = array();
		$arr = array();
		$current = & $xml_array;
		//Go through the tags.
		foreach ($xml_values as $data) {
			unset($attributes, $value); //value left from last time extract ;Remove existing values, or there will be trouble
			//This command will extract these variables into the foreach scope
			// tag(string), type(string), level(int), attributes(array).
			extract($data); //We could use the array by itself, but this cooler.
			$result = '';
			if ($get_attributes) { //The second argument of the function decides this.
				$result = array();
				if (isset($value)) $result['value'] = $value;
				//Set the attributes too.
				if (isset($attributes)) {
					foreach ($attributes as $attr => $val) {
						if ($get_attributes == 1) $result[$attr] = $val; //Set all the attributes in a array called 'attr'
						
						/**  :TODO: should we change the key name to '_attr'? Someone may use the tagname 'attr'. Same goes for 'value' too */
					}
				}
			} elseif (isset($value)) {
				$result = $value;
			}
			//See tag status and do the needed.
			if ($type == "open") { //The starting of the tag '<tag>'
				$parent[$level - 1] = & $current;
				if (!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag] = $result;
					$current = & $current[$tag];
				} else { //There was another element with the same tag name
					if (isset($current[$tag][0])) {
						array_push($current[$tag], $result);
					} else {
						$current[$tag] = array($current[$tag], $result);
					}
					$last = count($current[$tag]) - 1;
					$current = & $current[$tag][$last];
				}
			} elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
				//See if the key is already taken.
				if (!isset($current[$tag])) { //New Key
					//modified 2009 09 09 : push in array if only one element.
					$current[$tag] = $array_element ? array($result) : $result;
				} else { //If taken, put all things inside a list(array)
					if ((is_array($current[$tag]) and $get_attributes == 0) //If it is already an array...
					 or (isset($current[$tag][0]) and is_array($current[$tag][0]) and $get_attributes == 1)) {
						array_push($current[$tag], $result); // ...push the new element into that array.
						
					} else { //If it is not an array...
						$current[$tag] = array($current[$tag], $result); //...Make it an array using using the existing value and the new value
						
					}
				}
			} elseif ($type == 'close') { //End of tag '</tag>'
				$current = & $parent[$level - 1];
			}
		}
		return ($xml_array);
	}

	/**
	 *  array2xml will general simple utf-8 xml 1.0 string
	 *   <?xml version="1.0" encoding="utf-8"?>
	 *
	 * @param array $data
	 * @param staring $node_name
	 * @param int $max_level
	 * @param int $level	: DO NOT SET
	 * @return xml string
	 */
	function array2xml($data, $node_name = 'xml_node', $max_level = 50, $level = 0) {
		if ($level > $max_level) return serialize($data);
		if (!is_array($data) || !count($data)) {
			$xml = '<' . $node_name . '>' . htmlspecialchars(is_array($data) ? '' : $data, ENT_COMPAT, 'UTF-8', false) . '</' . $node_name . '>';
		} else {
			$i = 0; //first element;
			$attr = '';
			$value = '';
			foreach ($data as $k => $v) {
				$index = htmlspecialchars($k, ENT_COMPAT, 'UTF-8', false);
				if ($i !== $k && !is_array($v)) $attr.= ' ' . $index . '="' . $v . '" ';
				else $value.= (self::array2xml($v, $index, $max_level, $level + 1)) . "\r";
				$i++;
			}
			if ($attr) $xml.= '<' . $node_name . $attr . ' />';
			if ($value) $xml.= $value . "\r";
		}
		if ($level > 0) return $xml;
		else return '<?xml version="1.0" encoding="utf-8" ?>' . "\r<$node_name>\r" . $xml . "</$node_name>";
	}

	/**
	 * convert class var to array
	 *
	 * @param object $obj
	 * @return array
	 */
	function object2array($obj) {
		// this only change one level of object
		// get_object_vars($obj);
		if (is_object($obj) || is_array($obj)) foreach ($obj as $k => $v) {
			$k1 = preg_replace('/[\-|\s|\+]/', '_', $k);
			if (is_array($v) || is_object($v)) $vars[$k1] = self::object2array($v);
			else $vars[$k1] = $v;
		}
		return $vars;
	}
	/**
	 * convert array to class objetc for some stupid software 
	 *
	 * @param array $arr
	 * @return object
	 */
	function array2object($arr) {
		$obj = new stdClass();
		if (!is_array($arr)) return;
		foreach ($arr as $k => $v) {
			if (is_numeric($k)) $k = 'var_' . $k;
			$k = preg_replace('/[\s|\,|\.|\-|\+|\'|\"|\n|\r]/ims', '_', $k);
			$obj->$k = $v;
		}
		return $obj;
	}
	/**
	 * this is special for amfphp
	 *
	 * @param mix $arr		: data
	 * @param int $deep  	: max level
	 * @param  int $level 	: donot set
	 * @return mix
	 */
	function to_amfphp($arr, $deep = 20, $level = 0) {
		//_explicitType
		if ($deep == $level) return $arr;
		if (!is_array($arr)) return $arr;
		foreach ($arr as $k => $v) if (is_array($v)) $arr[$k] = self::to_amfphp($v, $deep, $level + 1);
		if ($arr['_explicitType']) {
			$cl = new stdClass();
			foreach ($arr as $k => $v) if (!is_null($v)) $cl->$k = $v;
			return $cl;
		}
		return $arr;
	}
	/**
	 * get parent caller
	 *
	 * @param int $shift : 2 =  parent's parent
	 * @return info of parent
	 */
	function caller($shift = 1) {
		$trace = debug_backtrace();
		return $trace[$shift];
	}
	/**
	 * back trace the php file
	 *
	 */
	function back_track() {
		$ms = (debug_backtrace());
		foreach ($ms as $m) echo $m['file'] . "<br>\n";
		exit();
	}

	/**
	 * ul type tree
	 *
	 * @param array $x		: object;
	 * @param string $name	: obj name
	 * @param int $deep		: max depth;
	 * @param int $level		: do not set!
	 * @return html string
	 */
	function tree_with_tick($x, $name = '', $close = 50, $deep = 50, $level = 0, $path = 'root') {
		if ($level > $deep) return "<li id=\"$id\">" . serialize($x) . "</li>";
		$id = uniqid() . $level;
		$num = count($x);
		$myid = $id . '_tree_block';
		$con.= ($level ? '' : '<ul style="padding:0;margin:0;">');
		if (is_array($x) || is_object($x)) {
			$con.= "<li style=\"border-bottom:dotted 1px #eee;\"><input value='1' type=\"checkbox\" name=\"tree[$path]\" onclick=\"tree_with_tick_dir(this,'$id')\"style=\"float:left;\"/> <a href=\"javascript:void(0)\" onclick=\"xp_tree_with_tick_expend(this,'$id');\"><span class=\"symbol\"><b>" . ($level >= $close ? "+" : "-") . "</b>" . $name . "</span></a> <span> &rarr;</li><li id=\"$id\" style=\"display:" . ($level >= $close ? "none" : "") . "\"><ul >";
			foreach ($x as $k => $v) $con.= self::tree_with_tick($v, $k, $close, $deep, $level + 1, $path . ",$k");
			$con.= "</ul></li>";
		} else {
			$con.= "<li id=\"$id\"><div><div style=\"float:left;margin-right:12px;\"><input value='1' type=\"checkbox\" name=\"tree[$path]\" onclick=\"\" style=\"float:left;\"> <span> $name &rarr; " . implode('<br>', self::clean(explode('*#', str_replace('*#', '*#&bull; ', $x)))) . "</span></div><div style=\"clear:both;\" ></div></div></li>";
		}
		$con.= $level ? '' : '</ul>';
		//			if(!$level) $con = ""
		//					   .$con;
		if (!$level) {
?>
			<style type="text/css">
				/*
				b{margin:0 3px 0 0;} 
				.bbb{color:#ddd;font-size:0.6em;} 
				sup,sub{color:#bbb; font-size:0.6em;} 
				sub{ margin-left:-5px;} 
				*/
				.symbol{height:0.8em;font-size:0.8em;padding:0 3px 0 10px;margin: 0 3px; background:#fff0b0;border:#eee solid 1px;border-right:#ddd solid 2px;border-bottom:#ddd solid 2px;} 
				ul {font-family: courier new;margin-left:40px;padding-top:0;display:block;list-style-type:none;} 
				li{margin:7px 0;} a{text-decoration:none;}			
			</style>			
			<script type="text/javascript">
				function xp_tree_with_tick_dir(t,id){
					var e=document.getElementById(id);
					var ip = e.getElementsByTagName('input');
					for(var i=0,l=ip.length;i<l; i++){
						ip[i].checked=t.checked;
					}
				}

				function xp_tree_with_tick_expend(t,id){
					t.blur(); 
					var e=document.getElementById(id);
					var d=e.style.display; 
					if(d){
						e.style.display='';
						this.childNodes[0].childNodes[0].innerHTML='-';
					}else{
						e.style.display='none';
						this.childNodes[0].childNodes[0].innerHTML='+';
					}	
				}

			</script>
			<?php
		}
		return $con;
	}
	/**
	 * format size  in MB. KB ...
	 *
	 * @param int/string $size 	: e.g. 23984783
	 * @param int $precision		: decimal point or reverse if less then 0;
	 * @return string
	 */
	function size($size, $precision=2, $suffixes = 'KMGTPEZY'){
		if($precision >= 0){
			$base = log($size+0.00000001, 1024);	//+0.00000001 is for base == 0;
			return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)-1];
		}else{
			$base = xpAS::preg_get($size,'/^[\d|\.]+(.)/',1);
			$d = xpAS::preg_get($size,'/^([\d|\.]+)(.)*/',1);
			$base = strtoupper($base);
			if(($p = strpos($suffixes, $base))!==false){
				$d = ceil($d*pow(1024,$p+1)); 
			}
			return $d;			
		}
	}
	/**
	 * php header to
	 *
	 * @param string $location
	 */
	function go($location) {
		//relative path;
		if (!preg_match('/^https*\:\/\//i', $location) && ($location{0} != '/' && $_SERVER['REDIRECT_URL'])) $location = xpAS::path_short(xpAS::path($_SERVER['REDIRECT_URL']) . $location);
		header("location:$location");
		die();
		exit; //stop script
		
	}
	/**
	 * redirect to url
	 *
	 * @param string $url
	 * @param string $perm : 301 permanently or 302 temporary or 503 Service Unavailable
	 */
	function redirect($url, $perm='301') {
		header("Location: $url", true, $perm);
		exit();
	}
	/**
	 * get global variable
	 *
	 * @param string $part : which part of global var such as user_data, _SESSION 
	 * @return unknown
	 */
	function get_globals($part = null) {
		$filter = array('GLOBALS', 'HTTP_ENV_VARS', 'HTTP_POST_VARS', 'HTTP_GET_VARS', 'HTTP_COOKIE_VARS', 'HTTP_SERVER_VARS', 'HTTP_POST_FILES', 'HTTP_SESSION_VARS');
		foreach ($GLOBALS as $k => $v) {
			if (!in_array($k, $filter)) $info[$k] = $v;
		}
		$info['constant'] = get_defined_constants(1);
		return $part == 'all' || !$part ? $info : $info['constant'][$part];
	}
	/**
	 * get client ip
	 *
	 * @return ip address (iv.4)
	 */
	function get_client_ip() {
		return ($ip = $_SERVER['HTTP_CLIENT_IP']) ? $ip : (($ip = $_SERVER['HTTP_X_FORWARDED_FOR']) ? $ip : (($ip = $_SERVER['HTTP_X_FORWARDED']) ? $ip : (($ip = $_SERVER['HTTP_FORWARDED_FOR']) ? $ip : (($ip = $_SERVER['HTTP_FORWARDED']) ? $ip : $_SERVER['REMOTE_ADDR']))));
	}
	/**
	 * get user agent
	 *
	 * @return string
	 */
	function get_user_agent() {
		return $_SERVER['HTTP_USER_AGENT'];
	}
	/**
	 * change ip to int
	 *
	 * @param string $ip
	 * @return int
	 */
	function ip2int($ip) {
		$a = explode('.', $ip);
		$a = array_reverse($a);
		foreach ($a AS $v) $m+= $v << 8 * $i++;
		return $m;
	}
	/**
	 *  get spider
	 */
	function get_spider($bots = array('googlebot' => 'Googlebot', 'msnbot' => 'MSNbot', 'slurp' => 'Yahoobot', 'baiduspider' => 'Baiduspider', 'sohu-search' => 'Sohubot', 'lycos' => 'Lycos', 'robozilla' => 'Robozilla')) {
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
		foreach (bots as $k => $v) if (strpos($useragent, $k)) return $v;
		return false;
	}
	/**
	 * curl page
	 *
	 * @param string $server
	 * @param string $buff
	 * @param int $timeout
	 * @param string  $header
	 * @param string $backheader
	 * @return string
	 */
	function curlOut($server, $buff = null, $timeout = 300, $header = null, $backheader = false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_URL, $server);
		curl_setopt($ch, CURLOPT_HEADER, $backheader);
		if ($header) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//for https
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if ($buff) {
			if(is_array($buff)) $buff = http_build_query($buff);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $buff);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.23 (Windows NT 5.1; U; en)');
		$str = curl_exec($ch);
		curl_close($ch);
		return $str;
	}
	/**
	 * get page except id/password for authentication.
	 *
	 * @param string $server
	 * @param string $buff
	 * @param string $id
	 * @param string $pwd
	 * @param int $timeout
	 * @param string $header
	 * @param string $backheader
	 * @return string
	 */
	function curlOutAuth($server, $buff = null, $id, $pwd, $timeout = 300, $header = null, $backheader = false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLOPT_URL, $server);
		curl_setopt($ch, CURLOPT_HEADER, $backheader);
		//curl_setopt($ch,	CURLAUTH_NTLM);
		curl_setopt($ch, CURLOPT_USERPWD, "$id:$pwd");
		//for https
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if ($header) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($buff) {
			if(is_array($buff)) $buff = http_build_query($buff);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $buff);
		}
		$str = curl_exec($ch);
		curl_close($ch);
		return $str;
	}
	/**
	 * curl with all options
	 *
	 * @param  array $arr : all options
	 * @return array of result;
	 */
	function curl($arr=array()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		if ($arr['POSTFIELDS']) {
			$arr['POST'] = true;
			if(is_array($arr['POSTFIELDS'])) $arr['POSTFIELDS'] = http_build_query($arr['POSTFIELDS']);
		}
		foreach ($arr as $k => $v) {
			$k = strtoupper($k);
			if (!is_numeric($k)) $k = constant('CURLOPT_' . $k);
			curl_setopt($ch, $k, $v);
		}
		$result['result'] = curl_exec($ch);
		$result['info'] = curl_getinfo($ch);
		$result['error'] = curl_error($ch);
		curl_close($ch);
		return $result;
	}
	/**
	 * after loo
	 *
	 */
	function flash() {
		ob_flush();
		flush();
	}
	/**
	 * generate html random color (e.g. #ef56a2)
	 *
	 * @param mix $arr	: array('r'->'31-9a',grey=>1)
	 * @return string
	 */
	function random_color($arr = null) {
		static $index = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f');
		static $color = array('r', 'g', 'b');
		foreach ($color as $v) {
			$rang = preg_split('/[\-|\,]/i', $arr[$v] ? $arr[$v] : '00-ff');
			$low = hexdec($rang[0]);
			$height = hexdec($rang[1]);
			$c[$v] = dechex(rand($low, $height));
		}
		return '#' . ($arr['grey'] ? $c['r'] . $c['r'] . $c['r'] : implode('', $c));
	}
	/**
	 * generate gibberish for html mockup
	 *
	 * @param int $cs
	 * @param boolean $html
	 * @return string
	 */
	function gibberish($cs = 300, $html = true) {
		static $s = "abcdefghijklmnopqrstuvwxyd";
		/**
		 * punctuation used
		 *
		 * @var string private
		 */
		static $p = ",,....;!???";
		function xpAS_gibberish_word($n = 1, $m = 16, $s, $html = true) {
			$len = rand($n, $m);
			if ($html && !rand(0, 7)) {
				$a = '<b>';
				$b = '</b>';
			}
			for ($i = 0;$i < $len;$i++) $w.= $s{rand(0, strlen($s) - 1) };
			return $a . $w . $b;
		}
		function xpAS_gibberish_sentence($n = 3, $m = 8, $p, $s, $html = true) {
			$len = rand($n, $m);
			$st[] = ucwords(xpAS_gibberish_word(1, 16, $s, $html));
			for ($i = 1;$i < $len;$i++) $st[] = xpAS_gibberish_word(1, 16, $s, $html);
			return implode(' ', $st) . $p{rand(0, 11) } . ' ' . (!rand(0, 6) ? '<br/>' : '');
		}
		$a = '<p>';
		$b = '</p>';
		while (strlen($gb) < $cs) {
			if ($html && !rand(0, 20)) $gb.= $a . xpAS_gibberish_sentence(3, 8, $p, $s, $html) . $b;
			else $gb.= xpAS_gibberish_sentence(3, 8, $p, $s, $html);
		}
		return $gb;
	}
}
