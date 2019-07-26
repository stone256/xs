<?php
/**
 * @author  peter wang <xpw365@gmail.com>
 * @version 1.4
 * @package
 */
class xpValidation {
	/**
	 * match expressions
	 *
	 * @var array of (name=>expression) ;
	 */
	static $regexs = array('address' => "/[\-|\/|\,|\.|\'|a-z|A-Z|\x81-\xFE]+/", 'email' => "/[0-9|a-z|A-Z|\.|\-|_]+@([0-9|a-z|A-Z|\-|_]+\.)+([a-z|A-Z]{2,4})/", 'name' => "/[\s|\'|a-z|A-Z|\x81-\xFE]+/", 'number' => "/[0-9]+/", 'number1' => "/[0-9|\s]+/", 'cc_number' => "/[0-9|\s]+/", 'phone' => "/[0-9|\-|\s|\(|\)]+/", 'title' => "/[\-|\(|\)|\/|\'|a-z|A-Z|0-9|\s|\x81-\xFE]+/", 'zip' => "/[0-9]+/", 'id' => "/[\_|\-|\.|\@|a-z|A-Z|0-9|\x81-\xFE]+/", 'time' => "/([0-9]|[0-1][0-9]|[2][0-3]):([0-5][0-9])/", 'date.au' => "/[0-9]{2}-[0-9]{2}-[0-9]{4}/", 'date.iso' => "/[0-9]{4}-[0-9]{2}-[0-9]{2}/", 'money' => "/(\-)?[0-9]+(\.[0-9]{0,2}){0,1}/", 'url' => '/(https?|ftp|gopher|telnet)\:\/\/[^\/]*?(\..+)*([\/]?.*|$)/i', 'ascii_display' => '/[\[|\~|\!|\@|\#|\$|\%|\^|\&|\*|\(|\)|\_|\+|\||\\|\=|\-|\`|\{|\}|\"|\?|\>|\<|\[|\]|\'|\;|\,|\.|\/|\||0-9a-zA-Z|\t|\x20]+/', 'chinese' => '/[\u4e00-\u9fa5]*/', 'readable' => '/^\w+$/', '????????????' => '/^[\u4e00-\u9fa5_a-zA-Z0-9]+$/', '??QQ?' => '/^[1-9]*[1-9][0-9]*$/', 'password' => '/.{3,}/',
	//flowed will process by program
	//			'have'=>'has="12,33"',
	//			'nothave'=>'',
	//			'length' =>'',//check
	//			'required'=>'',//check
	//			'not_null'=>'',//check
	//			*not'if'=>'>15,<=2,like',//not //
	//			range=(12,13);
	//			'in'=>'in=(12,23,45)'
	//			*not'default'
	);
	//from zac
	function fnEmailCheck($strEmail, $strict_mode = false) {
		$pattern_normal = '/^([a-z0-9-_]+(?:\.?[a-z0-9-_])*)@((?:[a-z0-9-_]+\.)+(?:[a-z0-9-_]{2,6}))$/i';
		$pattern_strict = '/^((?:[a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+(?:\.[a-z0-9\!\#\$\%\&\'\*\+\/\=\?\^\_\`\{\|\}\~\-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x20\x21\x23-\x5b\x5d-\x7f]|\\\[\x01-\x09\x0b\x0c\x0e-\x7f])*"))@((?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\]))$/i';
		$blnResult = preg_match($strict_mode ? $pattern_strict : $pattern_normal, $strEmail, $aryMatch);
		return $blnResult ? (strlen($aryMatch[1]) > 64 || strlen($aryMatch[2]) > 255 ? 0 : 1) : 0;
	}
	function fnPhoneCheck($phone) {
		$phoneReg = "/^(\+\d{2}[ \-]{0,1}){0,1}(((\({0,1}[ \-]{0,1})0{0,1}\){0,1}[2|3|7|8]{1}\){0,1}[ \-]*(\d{4}[ \-]{0,1}\d{4}))|(1[ \-]{0,1}(300|800|900|902)[ \-]{0,1}((\d{6})|(\d{3}[ \-]{0,1}\d{3})))|(13[ \-]{0,1}([\d \-]{5})|((\({0,1}[ \-]{0,1})0{0,1}\){0,1}4{1}[\d \-]{8,10})))$/";
		return preg_match($phoneReg, $phone);
	}
	function check($value, $regex) {
		if (!$regex) return true;
		$rs = xpAS::split($regex);
		if (!is_array($rs)) return true;
		foreach ($rs as $k => $r) {
			$r1 = trim(strtolower(xpAS::preg_get($r, '/^[^\=]*/')));
			switch ($r1) {
				case 'have':
					if (!self::_have($value, $r)) return false;
					break;
				case 'nothave':
					if (!self::_nothave($value, $r)) return false;
					break;
				case 'length':
					if (!self::_length($value, $r)) return false;
					break;
				case 'not_null':
				case 'required':
					if (!self::_required($value, $r)) return false;
					break;
				case 'range':
					if (!self::_range($value, $r)) return false;
					break;
				case 'in':
					if (!self::_in($value, $r)) return false;
					break;
				default:
					$p1 = strlen($value);
					$ptn = self::$regexs[$r] ? self::$regexs[$r] : $r;
					preg_match($ptn, $value, $matches);
					$tmp1 = $matches[0];
					$p2 = strlen($tmp1);
					if ($p1 != $p2) return false;
					break;
				}
			}
			return true; //after all check , I declare it OK.
			
		}
		function _range($v, $r) {
			$d = xpAS::split(xpAS::de_quote(xpAS::get(xpAS::split($r, '='), '1')));
			return $d[0] <= $v && $v <= $d[1];
		}
		function _required($v, $r) {
			return preg_match('/^.{1,}$/', $v);
		}
		function _length($v, $r) {
			$d = (xpAS::de_quote(xpAS::get(xpAS::split($r, '='), '1')));
			$ptn = '/^.{' . $d . '}$/';
			return preg_match($ptn, $v);
		}
		function _nothave($v, $r) {
			$d = xpAS::split(xpAS::de_quote(xpAS::get(xpAS::split($r, '='), '1')));
			$v = ' ' . $v;
			if (is_array($d)) foreach ($d as $k => $test) {
				if (strpos($v, $test)) return false; //found one
				
			}
			return true; //did not find
			
		}
		function _have($v, $r) {
			$d = xpAS::split(xpAS::de_quote(xpAS::get(xpAS::split($r, '='), '1')));
			$v = ' ' . $v;
			if (is_array($d)) foreach ($d as $k => $test) {
				if (strpos($v, $test)) return true; //found one
				
			}
			return false; //did not find
			
		}
		function _in($v, $r) {
			return in_array($v, xpAS::split(xpAS::de_quote(xpAS::get(xpAS::split($r, '='), '1'))));
		}
	}
	