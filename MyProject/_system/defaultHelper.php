<?php
/**
 * x application default helper class
 *
 */
class defaultHelper {
	static $_global_data = array();

	/**
	 * setting hash for validate page links
	 *
	 * @param string $name	: e.g. "user,admin", "blog,login"..
	 * @param string $value : default is auto generated
	 * @return boolean
	 */
	function hash_set($name='user', $value=null){
		return self::data_set($name,  $value ? $value : sha1(uniqid()));
	}
	/**
	 * check page return hash is match to user hash, to make sure that the page link chian is not broken
	 *
	 * @param string $value
	 * @param unknown_type $name
	 * @return unknown
	 */
	function hash_check($value, $name='user'){
		return self::data_get($name) === $value;
	}

	function page_hash_get($name) {
		return self::data_get('pages,' . $name);
	}
	function page_hash_set($name, $value=null) {
		return self::data_set('pages,' . $name, $value ? $value : sha1(uniqid()));
	}



	/**
	 * save or goto the last url
	 *
	 * @param boolean $return	:goto the page if true
	 * @return string url of back page
	 */
	function return_url($return = false) {
		$r = $_SERVER['HTTP_REFERER'];
		$c = $_SERVER['REQUEST_URI'];
		$path = preg_replace('/^.*?\:\/\/.*?\//','/',$r);
		if($path == $c) $r = null;	//same page

		$rt = self::data_get('url,return,' . md5($c));
		if ($return) xpAS::go($rt ? $rt : '/');
		$r = $r?$r:$rt;
		self::data_set('url,return,' . md5($c), $r);
		return $r;
	}
	/**
	 * get related path from application root
	 *
	 * @param string$a
	 * @return string related path
	 */
	function rpath($a) {
		return str_replace(_X_ROOT, '', $a);
	}


	/**
	 * save global or application data
	 *
	 * @param  string $path
	 * @param  mix $value
	 * @param  string $type	: default = 0 :global;  1: application private
	 */
	function data_set($path, $value, $type = 0) {
		/**what is the F with php7
		xpAS::set($type ? self::$_global_data : $_SESSION, ($type ? '' : '_application_data,') . $path, $value);
		*/
		$fpg = $type ? self::$_global_data : $_SESSION;
		xpAS::set($fpg, ($type ? '' : '_application_data,') . $path, $value);
		if($type)
		 	self::$_global_data = $fpg;
		else
		 	$_SESSION = $fpg;

		return $value;
	}
	/**
	 * get global or application data
	 *
	 * @param string $path
	 * @param  string $type	: default = 0 :global;  1: application private
	 * @return mix $value
	 */
	function data_get($path, $type = 0) {
		return xpAS::get($type ? self::$_global_data : $_SESSION, ($type ? '' : '_application_data,') . $path);
	}
	function iso_time($time) {
		if (preg_match('/\d\d:\d\d/', $time)) $time = $time . ":00";
		if (!preg_match('/\d\d:\d\d:\d\d/', $time)) {
			switch (strlen($time)) {
				case 1:
					$time = "0" . $time . "0000";
				break;
				case 2:
					$time = $time . "0000";
				break;
				case 3:
					$time = "0" . $time . "00";
				break;
				case 4:
					$time = $time . "00";
				break;
				case 5:
					$time = "0" . $time;
				break;
			}
			$time = substr_replace($time, ':', 4, 0);
			$time = substr_replace($time, ':', 2, 0);
		}
		return date("H:i:s", strtotime($time)) == $time ? $time : false;
	}
	//only return format no : for dhx project
	function iso_date($date) {
		if (preg_match('/^\d\d-\d\d\-\d\d$/', $date)) $date = "20" . $date;
		if (!preg_match('/^\d\d\d\d-\d\d\-\d\d$/', $date)) {
			if (strlen($date) == 6) $date = "20" . $date; //150923 => 20150923
			$date = substr_replace($date, '-', 6, 0);
			$date = substr_replace($date, '-', 4, 0);
		}
		return date("Y-m-d", strtotime($date)) == $date ? $date : false;
	}


	/**
	 *  return url
	  */
	function return_to_url($page){
		return Redirect::to(self::return_get_url($page));
	}

	function return_get_url($page){
		$mod = xpAS::preg_get(get_called_class(), '/\\(.*?)controller/',1);
		return self::data_get("return-url,{$mod},{$page}",1);
	}

	function return_save_url($page, $ret ){
		$mod = xpAS::preg_get(get_called_class(), '/\\(.*?)controller/',1);
		self::data_set("return-url,{$mod},{$page}", $ret, 1);
	}

	function message_set($massage_name, $message, $type=1){
		if(!is_array($message)) $message = array($message);
		$msg = (array)Myhelper::data_get($massage_name, $type);
		foreach ($message as $m){
			$msg[] = date("H:i:s") . " : " . $m;
		}
		$max = Config::get('drhx.message.length');
		while(count($msg)>$max) array_shift($msg);
	    	Myhelper::data_set($massage_name, $msg, $type);
	}

	function message_get($massage_name, $type=1){
		return Myhelper::data_get($massage_name, $type);
	}

	function message_delete($massage_name, $index, $type=1){
		$msg = (array)Myhelper::data_get($massage_name, $type);
		unset($msg[$index]);
	    	Myhelper::data_set($massage_name, $msg, $type);
	}

	/**
	 * ajax batching
	 */
	function batch($batch, $handlers) {
		if (!$handler = $handlers[$batch['handler']]) return array('status' => 'error', 'msg' => 'please provide case handler name');
		$handler = explode('@', $handler);
		if (!method_exists($handler[0], $handler[1])) return array('status' => 'error', 'msg' => 'case handler is on holday');
		$batch['size'] = $batch['size'] ? $batch['size'] : 10;
		$batch['next'] = $batch['next'] ? $batch['next'] : 1;
		$batch['start'] = $batch['start'] ? $batch['start'] : 0;
		$batch['end'] = $batch['end'] ? $batch['end'] : -1;
		$ret = call_user_method($handler[1], new $handler[0], $batch);
		$ret['msg'] = $ret['msg'] ? $ret['msg'] : $ret['status'];
		return xpAS::merge($batch, $ret);
	}
	function test($s, $e) {
		return array('msg' => "$s - $e", 'status' => $e > 300 ? 'end' : 'next');
	}
}





function _log($data, $level = 1, $name = 'log', $path = null, $max = 8000000) {
	if (_X_LOG_LEVEL == '_X_LOG_LEVEL' || $level > _X_LOG_LEVEL) return false;
	static  $fp = array();
	$path = $path ? $path : _X_LOG;
	$file = "$path/$name";
	$hash = md5($file);
	if(!$fp[$hash]){
		if(file_exists($file) && (filesize($file) > $max)){
			$log = file_get_contents($file);
			$log = substr($log, -ceil($max/2));
			file_put_contents($file, $log);
		}
		$fp[$hash] = fopen($file, 'a+');
	}
	fwrite($fp[$hash],  "\n" . date("Y-m-d H:i:s :").var_export($data,1));
}

function _dt($from_last=true) {
	static $last;
	$start = $from_last && $last ? $last : _X_START_TIME;
	$last = microtime(1);
	_dv(array('elapsed' => $last - _X_START_TIME));
}
function _d($x, $die = false, $display = true, $tab = 6, $deep = 50, $level = 0) {
	//if (!(__X_DEBUG === true)) return;
	if ($level == 0) $con = "<pre>\n{\n"; //1st start
	$level++;
	if ($level == $deep) return serialize($x);
	if (is_array($x) || is_object($x)) {
		foreach ($x as $k => $v) {
			if (is_array($v)) {
				$con.= str_pad($level, 5, '.') . str_pad('', $level * $tab, '.', STR_PAD_LEFT) . "$k=>[\n";
				$con.= _d($v, '', $display, $tab, $deep, $level);
				$con.= str_pad($level, 5, '.') . str_pad('', $level * $tab, '.', STR_PAD_LEFT) . "]\n";
			} else {
				$con.= str_pad($level, 5, '.') . str_pad('', $level * $tab, '.', STR_PAD_LEFT) . "$k=>";
				$con.= $v . "\n";
			}
		}
	} else {
		$con.= str_pad($level, 5, '.') . str_pad('', $level * $tab, '.', STR_PAD_LEFT) . "$x\n";
	}

	if ($level == 1) {
		$con.= "} TIME=".round((microtime(true)-$_SERVER['REQUEST_TIME_FLOAT'])*1,4)."s\n \n</pre>\n"; //end
		if ($display) {
			echo $con; //display
			if($die){
				foreach (debug_backtrace() as $k => $v) {
					$r = xpAS::clean(array('#' . ($k + 1) . ': ', $v['class'], $v['type'], $v['function'] . '()', '  ' . $v['file'] . ' [' . $v['line'] . ']'));
					$rs.= implode("", $r) . "\n";
				}
			}
			echo "<pre>";
			print_r($rs);
			echo "</pre>";
			if($die)die();
			ob_implicit_flush();
		}
	}
	return $con;
}
function _dv($a, $die = false) {
	if (__X_DEBUG !== true) return;
	if(_X_CLI_CALL === true){
		print_r($a);
		echo PHP_EOL;
	}else{
		echo "<pre>";
		var_export($a);
		echo "</pre>";
	}
	if ($die) die();
}
function _ld($a){
	_dv($a);
	_log($a);
}
function _dm($body = "test email : \n Hello world!", $subject = null, $to = "your.email.address@your.email.com", $headers = "From: webmaster@p.com \n ") {
	//	if(__XP_DEBUG__ !== true) return;
	$b = debug_backtrace();
	$subject = $subject ? $subject : "#{$b[0]['line']} .." . substr($b[0]['file'], -48);
	mail($to, $subject, var_export($body, 1), $headers);
}
$_cache = new xpCacheFile();
$_cache->path(_X_CACHE . '/');
function _cg($k, $ttl = 300) {
	global $_cache;
	return $_cache->get($k, $ttl);
}
function _cp($k, $value) {
	global $_cache;
	return $_cache->put($k, $value);
}

//function _factory($name, $no_singleton = false, $construct_data=null){
function _factory(){
	global $overwrites;
	static $objects=array();
	$args = func_get_args();
	$name =array_shift($args);
	if($overwrites[$name]) $name = $overwrites[$name];
	$no_singleton = array_shift($args);
	if(!$no_singleton && $objects[$name]) return $objects[$name];
	//use if(phpversion()>7)
	return $objects[$name]  = new $name(...$args[0]);
	//else use
	//  return  $objects[$name] = call_user_func_array([ $name, '__construct'], $args[0]);

}
function _config($name){
	static $cnf;
	if(!$cnf){
		global $config;
		$cnf = $config;
	}
	if(is_null($value)) return xpAS::get($cnf, $name);
	xpAS::set($cnf,$name,$value);
	global $config;
	xpAS::set($config,$name,$value);
}
function _url(){
	return app::$_url;
}
function _r_url(){
    return app::$_request_url;
}
function _rp($path){
	return str_replace(_X_ROOT, '', $path );
}
function _mapper(){
	return app::$_mapper;
}
function _controller(){
	return app::$_controller;
}
function _router(){
	global $routers;
	return $routers;
}
