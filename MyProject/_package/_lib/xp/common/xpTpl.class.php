<?php
/**
 * @author 	peter <stone256@hotmail.com>
 * @version 1.01
 * @date	2015-07-12
 *
 * @dependence: xpAS, xpValidation(di)
 */
class xpTpl {
	/**
	 * template string;
	 * @var string
	 */
	var $main = '';
	/**
	 * template data
	 * @var array
	 */
	var $data = array();
	/**
	 * simple condition
	 *
	 * @var str
	 */
	var $operator = '\!\=\=|\=\=\=|\!\=|\=\=|\<\=|\>\=|\<|\>|\&\&|\|\||\(\)|\)\(|\/\/|\%';
	/**
	 * template parsed once
	 */
	var $parsed = false;
	/**
	 * tester
	 *
	 * @param A $a
	 * @param operator $op
	 * @param B $b
	 * @return boolean true;
	 */
	function _op($a, $op = null, $b = null) {
		switch ($op) {
			case '!==':
				return $a !== $b;
			case '!=':
				return $a != $b;
			case '<=':
				return $a <= $b;
			case '>=':
				return $a >= $b;
			case '>':
				return $a > $b;
			case '<':
				return $a < $b;
			case '===':
				return $a === $b;
			case '==':
				return $a == $b;
			case '&&':
				return $a && $b;
			case '||':
				return $a || $b;
			case '+':
				return $a + $b;
			case '-':
				return $a - $b;
			case '*':
				return $a * $b;
			case '/':
				return $a / $b;
			case '%':
				return $a % $b;
			case '()':
				if (is_string($b)) $b = explode(',', $b);
				return in_array($a, (array)$b);
			case ')(':
				if (is_string($b)) $b = explode(',', $b);
				return !in_array($a, (array)$b);
			case '//':
				return preg_match('/' . preg_quote($b) . '/', $a);
				//used only in filter
				
			case '**':
				return substr(substr($a, 1), -1);
			case 'dv':
				return json_encode($a);
			default:
				return $a;
			}
			return false;
		}
		/**
		 * set template data item
		 *
		 * @param mix $key:	key string(comma separated) or array
		 * @param unknown_type $value
		 */
		function set($key, $value) {
			xpAS::set($this->data, $key, $value);
		}
		/**
		 * set new template data
		 *
		 * @param mix $arr
		 */
		function sets($arr) {
			//adding common vars:
			$arr['%RANDOM'] = mt_rand();
			$arr['%LEVEL'] = (int)$this->setting['level'];
			$arr['%__setting'] = $this->setting;
			foreach ($_SERVER as $k => $v) $arr['%_' . $k] = $v;
			//		foreach (xpAS::get_globals('user') as $k=>$v) $arr['%_'.$k] = $v;
			$this->data = $arr;
		}
		/**
		 * get template data
		 *
		 * @param string $key
		 */
		function get($key) {
			return xpAS::get($this->data, $key);
		}
		/**
		 * get var for given data object;
		 *
		 * @param string $var_name: key_name
		 * @param array $data: data object
		 * @return as string
		 */
		function _get_var($var_name, $data) {
			$name = xpAS::preg_get($var_name, '/^\s*\\$?(.*?)\s*$/i', 1);
			return is_array($data) ? xpAS::get($data, $name) : '';
		}
		/**
		 * apply internal filter
		 *	e.g. @+15
		 */
		function _internal_filter($var, $vf, $data) {
			preg_match('/^\@([^a-z0-9]+)(.*)$/i', $vf, $tmp);
			//if(preg_match('/^\$/', $tmp[2])) $tmp[2] = $this->_get_var($tmp[2], $data);
			if ($tmp[2] {
				0
			} == '$') $tmp[2] = $this->_get_var($tmp[2], $data);
			return $this->_op($var, $tmp[1], $tmp[2]);
		}
		/**
		 * apply filters to result
		 * {{$abc|ucwords|xpAS::str2hex|@+20|@-$other}}
		 *
		 */
		function _apply_filters($var, $fs, $data) {
			foreach ((array)$fs as $kf => $vf) {
				//if(preg_match('/^\@/', $vf)) $var = $this->_internal_filter($var, $vf, $data);
				if ($vf{0} == '@') $var = $this->_internal_filter($var, $vf, $data);
				else $var = call_user_func_array($vf, array($var));
			}
			return $var;
		}
		/**
		 * contructor
		 * @return template
		 */
		function __construct($setting = null) {
			//passed directory not a array;
			/*
			$setting = [
			string=>'', 	//template string
			file=>'',		//file name
			'lang'=>'' 		//lang handler"
			]
			*/
			$this->caller = xpAS::caller();
			if ($setting['file']) { //for linux system start
				//$this->file  = preg_match('/^(\/)/', $setting['file']) ? $setting['file'] :  dirname($this->caller['file']). '/' . $setting['file'] ;
				$this->file = $setting['file'] {
					0
				} == '/' ? $setting['file'] : dirname($this->caller['file']) . '/' . $setting['file'];
				$this->path = dirname($this->file);
				$this->main = file_get_contents($this->file);
			} else {
				$this->main = $setting['string'];
				$this->path = dirname($this->caller['file']);
			}
			$setting['base'] = $setting['base'] ? $setting['base'] : $this->path;
			$setting['validator'] = $setting['validator'] ? $setting['validator'] : 'xpValidation::check';
			//		$this->level = (int)$setting['level'];
			$this->setting = $setting;
		}
		/**
		 * randereing template
		 *
		 */
		function html($output = false, $reload = false) {
			$this->parse($reload);
			if (!$output) return $this->output;
			echo $this->output;
		}
		/**
		 * parse the template.
		 *
		 * any $abd.dd means $this->data['abd']['dd']
		 * $2.12.value = $this->data[2][12]['value']
		 *
		 */
		function parse($reload = false) {
			if ($this->parsed && !$reload) return;
			$str = $this->main;
			//check includes
			$str = $this->_includes($str, $this->data);
			//check blocks
			$str = $this->_blocks($str, $this->data);
			//check recursions
			$str = $this->_recursions($str, $this->data);
			//check standard achors
			$str = $this->_standards($str, $this->data);
			$this->parsed = true;
			$this->output = $str;
		}
		/**
		 * <!--recursion start $rc-->
		 * <p>
		 * 		<h1>{{$title}}</h1>
		 * 		<p>
		 * 			{{$text}}
		 * 			<!-- recursion $rc-->
		 * 		</p>
		 * </p>
		 * <!--recursion end $rc-->
		 */
		function _recursions($str, $data) {
			//<!-- start of recursion -->
			preg_match_all("/\<!--\s*recursion\s+start\s+(.*?)\s*-->(.*?)<!--\s*recursion\s+end\s+\\1\s*-->/ims", $str, $tmp);
			if (count($tmp[1]) < 1) return $str;
			foreach ($tmp[1] AS $kr => $vr) {
				$vr_name = xpAS::preg_get($vr, '/^\\$?(.*?)$/', 1);
				//no more recursion
				if (!($var = $data[$vr_name])) continue;
				foreach ($var as $ks => $vs) {
					$tpl_s = $tmp[2][$kr];
					$tpl_s = preg_replace('/<!--\s*recursion\s+' . preg_quote($vr) . '\s*-->/i', $vs[$vr_name] ? $tmp[0][$kr] : '', $tpl_s);
					$sub = new self(xpAS::setting($this->setting, array('string' => $tpl_s, 'file' => null, 'base' => $this->setting['base'], 'level' => $this->setting['level'] + 1)));
					$vs['%NAME'] = $vr_name;
					$vs['%K'] = $ks;
					$vs['%V'] = $vs;
					$vs['%EVEN_ODD'] = ($ks % 2) ? '_EVEN' : '_ODD';
					$vs['_parent'] = & $data;
					$sub->sets($vs);
					$rpl[] = $sub->html();
				}
				$replacer = "\n<!-- recursion $vr_name -->" . implode("\n", $rpl);
				$str = str_replace($tmp[0][$kr], $replacer, $str);
			}
			return $str;
		}
		/**
		 *<!-- start of name --> xxxx..{{abc}}xx.x <!-- end of name -->
		 *
		 */
		function _blocks($str, $data) {
			while (preg_match("/\<!--\s*start\s+of\s+(\?\S*\s+)?(\S*)\s*-->(.*?)<!--\s*end\s+of\s+\\2\s*-->/ims", $str, $tmp)) {
				if ($tmp[1]) {
					preg_match('/^\?(.+?)(' . $this->operator . ')(.*?)$/', $tmp[1], $cond);
					$a = $this->_get_var($cond[1], $data);
					$b = preg_match('/^\$/') ? $this->_get_var(trim($cond[3]), $data) : trim($cond[3]);
					if (!$this->_op($a, $cond[2], $b)) {
						$str = str_replace($tmp[0], '', $str);
						continue;
					}
				}
				$rpl = array();
				$name = preg_replace('/^\?\\$?|\s*/', '', $tmp[2]);
				$var = $this->_get_var($name, $data);
				foreach ((array)$var as $kv => $vv) {
					$vv0 = $vv;
					$tpl = new self(xpAS::setting($this->setting, array('base' => $this->setting['base'], 'level' => $this->setting['level'] + 1, 'string' => $tmp[3], 'file' => null)));
					if (!is_array($vv)) $vv = array('_self' => $vv);
					else $vv['_self'] = array(&$vv);
					$vv['%NAME'] = $name;
					$vv['%K'] = $kv;
					$vv['%V'] = $vv0;
					$vv['%EVEN_ODD'] = ($kv % 2) ? '_EVEN' : '_ODD';
					$vv['_parent'] = & $data;
					$tpl->sets($vv);
					$rpl[] = "\n" . $tpl->html();
				}
				$replacer = "<!--block $name --> " . implode(" ", $rpl);
				$str = str_replace($tmp[0], $replacer, $str);
			}
			return $str;
		}
		/**
		 * standard anchors:
		 *  {{$a,d,c,0,value|my:method}}
		 * {{$ab,c,d,f:defalasdfasdf|ucword}}
		 * {{$abc.c>=asdasd?asdasd:adasd}}
		 * {{$abc.c!=$bbb?asdasd:adasd}}
		 * {{$cc?asdf:asdfasdf}}
		 * {{$b,d,e#111:222:333:5=555:a=23a56b}}
		 *
		 * @param string $str
		 */
		function _standards($str, $data) {
			$pattern = preg_match_all("/\{\{([^\*]([^\{]*?)[^\*])\}\}/i", $str, $tmp);
			foreach ($tmp[1] as $ka => $va) {
				switch (true) {
					case preg_match('/^[^\?\#\=\<\>\!]*?(\:.*)?$/', $va):
						$str = $this->_standard_1($str, $va, $data);
					break;
					case preg_match('/^[^\#]+$/', $va):
						$str = $this->_standard_2($str, $va, $data);
					break;
					case preg_match('/^[^\?]*$/', $va):
						$str = $this->_standard_3($str, $va, $data);
					break;
				}
			}
			return $str;
		}
		/**
		 *  {{$a,d,c,0,value|my::method}}
		 * {{$ab,c,d,f:defalasdfasdf|ucword}}
		 */
		function _standard_1($str, $va, $data) {
			//filters
			$fs = explode('|', $va);
			$var = array_shift($fs);
			//get default if has one
			$default = explode(':', $var);
			$var = $default[0];
			$default = preg_match('/^\s*\\$/', $default[1]) ? $this->_get_var($default[1], $data) : $default[1];
			$replacer = $this->_get_var($var, $data);
			$replacer = isset($replacer) ? $replacer : $default;
			$replacer = is_array($replacer) ? implode(',', $replacer) : $replacer;
			//apply filter
			$replacer = $this->_apply_filters($replacer, $fs, $data);
			$str = str_replace('{{' . $va . '}}', $replacer, $str);
			return $str;
		}
		/**
		 * {{$abc.c>=asdasd?asdasd:adasd}}
		 * {{$abc.c!=$bbb?asdasd:adasd|ucwords}}
		 * {{$cc?asdf:asdfasdf}}
		 */
		function _standard_2($str, $va, $data) {
			//filters
			$fs = explode('|', $va);
			$var = array_shift($fs);
			//3 elements operation
			$pattern = '/^(.*?)(\?)(.*?)(\:)(.*)$/';
			preg_match($pattern, $var, $values);
			$yes = preg_match('/^\s*\\$/', $values[3]) ? $this->_get_var(substr($values[3], 1), $data) : $values[3];
			$no = preg_match('/^\s*\\$/', $values[5]) ? $this->_get_var(substr($values[5], 1), $data) : $values[5];
			$var = $values[1];
			//ge operator
			$pattern = '/^(.*?)((' . $this->operator . ')(.*))?$/';
			preg_match($pattern, $var, $values);
			$var = $values[1];
			$op = $values[3];
			$obj = $values[4];
			//get replacer
			$var = $this->_get_var($var, $data);
			$obj = preg_match('/^\s*\\$/', $obj) ? $this->_get_var($obj, $data) : $obj;
			$replacer = $this->_op($var, $op, $obj) ? $yes : $no;
			$replacer = is_array($replacer) ? implode(',', $replacer) : $replacer;
			//filter
			$replacer = $this->_apply_filters($replacer, $fs, $data);
			$str = str_replace('{{' . $va . '}}', $replacer, $str);
			return $str;
		}
		/**
		 * {{$b#111:222:333:5=555:a=$bbb|ucwords}}
		 */
		function _standard_3($str, $va, $data) {
			//filters
			$fs = explode('|', $va);
			$var = array_shift($fs);
			//value
			$values = explode("#", $var);
			$index = $this->_get_var($values[0], $data);
			if (preg_match('/^\$/', $values[1])) {
				$brr = $this->_get_var($values[1], $data);
			} else {
				$arr = explode(':', $values[1]);
				foreach ($arr as $k => $v) {
					preg_match('/^((.*?)[\=])?(.*)$/', $v, $vv);
					$val = preg_match('/^\$/', $vv[3]) ? $this->_get_var(substr($vv[3], 1), $data) : $vv[3];
					if ($vv[2]) $brr[$vv[2]] = $val;
					else $brr[] = $val;
				}
			}
			$replacer = is_array($brr[$index]) ? implode(',', $brr[$index]) : $brr[$index];
			//filter
			$replacer = $this->_apply_filters($replacer, $fs, $data);
			$str = str_replace('{{' . $va . '}}', $replacer, $str);
			return $str;
		}
		/**
		 * 1st get file inclusion
		 * <!-- include of tpl/manual_input_div_category.tpl.html -->
		 * or
		 * <!-- include of ?$abc.def.0.value.1=5  tpl/manual_input_div_other.tpl.html -->
		 * 	$abc = $this->data['abc']
		 * or
		 * <!-- include of tpl/|$item.abc.path|_input_div_other.tpl.html -->
		 * 	$item  = this->data['item']
		 */
		function _includes($str, $data) {
			preg_match_all("/\<!--\s*include\s+of\s+(\?.*?\s+)?(.*?)\s*-->/ims", $str, $tmp);
			foreach ($tmp[2] as $kinc => $vinc) {
				//replace |$var|..
				if ($var = xpAS::preg_get($vinc, '/\{\{\$?(.*?)\}\}/', 1)) {
					$rpl = xpAS::get($data, $var);
					$vinc = preg_replace('/\{\{\$(.*?)\}\}/', $rpl, $vinc);
				}
				//check file location is relative
				//			if(!preg_match('/^(\/)/', $vinc)){
				if ($vinc{0} !== '/') {
					$vinc = $this->setting['base'] . '/' . $vinc;
				}
				//check if is conditional
				if ($tmp[1][$kinc]) {
					preg_match('/^\?(.*?)((' . $this->operator . ')(.*?)\s+)?$/', $tmp[1][$kinc], $cond);
					//array ( //0 => '?$about.on=1 ', //1 => '$about.on', //2 => '=1 ', //3 => '=', //4 => '1',)
					$cond[1] = $this->_get_var($cond[1], $data);
					if ($cond[4]) $cond[4] = preg_match('/^\s*\\$/', $cond[4]) ? $this->_get_var($cond[4], $data) : $cond[4];
					if (!$test = $this->_op($cond[1], $cond[3], $cond[4])) $vinc = ''; //do not include file
					
				}
				$tpl = new self(xpAS::setting($this->setting, array('file' => $vinc, 'string' => null, 'level' => $this->setting['level'] + 1)));
				$tpl->sets($data);
				$replacer = $tpl->html();
				$str = str_replace($tmp[0][$kinc], $replacer, $str);
			}
			return $str;
		}
		/**
		 * validate form
		 *
		 * @param string $id : form html e.g. <form id="myFormId">
		 * @param array $data :  form data to be validated
		 * @param  function : $validator: custom-validator can be injected here or use validator provide by xpValidation
		 * @return failed validating fields
		 */
		function form_validation($id, $data, $validator = null) {
			$validator = $valudator ? $validator : $this->setting['validator'];
			foreach ((array)$this->_form_fields($id) as $k => $v) {
				if ($v['data-validation']) {
					if (!call_user_func_array($validator, array($data[$k], $v['data-validation']))) $err[$k] = " $k";
				}
			}
			return $err ? $err : false;
		}
		/**
		 * get a form info
		 *
		 */
		function _form_fields($id) {
			if (!preg_match('/(\<form[^\>]*?id\=("|\')' . preg_quote($id) . '\\2.*?\>)(.*?)\<\/form>/ims', $this->html(), $tmp)) return array();
			$ri = $this->_get_input($tmp[3]);
			$rt = $this->_get_textarea($tmp[3]);
			$rs = $this->_get_select($tmp[3]);
			return $form = (array)$rs + (array)$rt + (array)$ri;
		}
		function _get_input($form) {
			preg_match_all("/<input(.*?)>/ims", $form, $tmp);
			foreach ($tmp[1] as $k => $v) {
				preg_match_all('/(\S+)="(.*?)"/ims', $v, $t);
				$e = array_combine($t[1], $t[2]);
				$elements[] = $e;
			}
			return xpAS::key($elements, 'name');
		}
		function _get_textarea($form) {
			preg_match_all("/<textarea(.*?)>(.*?)<\/textarea>/ims", $form, $tmp);
			foreach ($tmp[1] as $k => $v) {
				preg_match_all('/(\S+)="(.*?)"/ims', $v, $t);
				$elements[] = array_merge(array_combine($t[1], $t[2]), array('type' => 'textarea', 'value' => $tmp[2][$k]));
			}
			return xpAS::key($elements, 'name');
		}
		function _get_select($form) {
			preg_match_all("/<select(.*?)>(.*?)<\/select>/ims", $form, $tmp);
			if (is_array($tmp[1])) foreach ($tmp[1] as $k => $v) {
				preg_match_all('/(\S+)="(.*?)"/ims', $v, $t);
				preg_match_all('/<option(\s*?value="(.*?)".*?)>(.*?)<\/option>/ims', $tmp[2][$k], $tv);
				$lists = null;
				$value = '';
				if (count($tv[3])) foreach ($tv[3] as $k1 => $v1) {
					if (strpos($tv[1][$k1], 'selected=') !== false) $value = $tv[2][$k1];
					$lists[$tv[2][$k1]] = $v1;
				}
				$elements[] = array_merge(array_combine($t[1], $t[2]), array('type' => 'select', 'list' => $lists, 'value' => $value));
			}
			return xpAS::key($elements, 'name');
		}
	}
	