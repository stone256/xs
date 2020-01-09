<?php

/**
 * default sitemin controller
 *
 */
//error_reporting(E_ALL);
class sitemin_loginController extends _system_defaultController {
	var $captcha;
	var $captcha_type =['off'=>'off', 'google'=>'google', 'local'=>'local'];

	function __construct(){
		$this->q = $_REQUEST;
		$_p = _url();
		session_start();
		//$this->return_url = defaultHelper::return_url();
		if(_factory('sitemin_model_var')->get('sitemin/log') && $_p != '/sitemin/keepalive') _factory('sitemin_model_log')->insert();
		$this->_captcha();
	}
	function _captcha(){
		$type = $this->captcha_type[_factory('sitemin_model_var')->get('sitemin/captcha')];
		$type = $type ?: 'off';
		$this->captcha = _factory('sitemin_model_captcha_'.$type);
		//testing
		//$this->captcha->test();
	}
	function dashboardAction() {
		$rs['tpl'] = 'user/_dashboard.phtml';
		$rs['TITLE'] = 'SITEMIN DASHBOARD';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}
	function testAction() {
		$rs['tpl'] = '_test.phtml';
		$rs['TITLE'] = 'SITEMIN TEST';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}
	function hashgeneratorAction() {
		$q = $this->q;
		$cmds = array(
				'crypt',  'htmlentities',
				'htmlspecialchars', 'md5', 'metaphone', 'nl2br',
				'sha1', 'base64_decode', 'base64_encode',  'strlen', 'strtolower', 'strtoupper',
			      );

		if ($q['save']) {
			if(in_array($cmd = $q['cmd'], $cmds)){
				$r = $q['short'] ? $cmd($q['hint'], $q['short']) : $cmd($q['hint']);
			}else{
				$r = '1234129086628726923972393453485934858349563492';
			}
			echo ($r);
			die();
		}
		$rs['cmds'] = $cmds;
		$rs['tpl'] = 'user/_hashgenerator.phtml';
		$rs['TITLE'] = 'SITEMIN HASH GENERATOR';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}
	function passwordgeneratorAction() {
		$q = $_REQUEST;
		if ($q['save']) {
			//$l = new admin_model_login;
			//if($l->captcha($q)) die("Google said: you are robot!");
			$r = md5(md5($q['hint']) . xpAS::roller($q['hint']));
			if($q['short']) $r = $this->_short($r);

			die($r);

		}
		$rs['tpl'] = '_passwordgenerator.phtml';
		$rs['TITLE'] = 'PASSWORD GENERATOR';
		return array('view'=>'/sitemin/view/user/index.phtml','data' => array('rs' => $rs));
	}
	function csv2jsonAction() {
		ini_set('memory_limit', '664M');
		$q = $_REQUEST;
		if ($q['save']) {
			//$l = new admin_model_login;
			//if($l->captcha($q)) die("Google said: you are robot!");

			$name = $_FILES['csv']['tmp_name'];
			//_dv(file_get_contents($name));
			$r = xpCSV::gets("$name");
			switch($q['type']){
				case 'JSON':
					$data = json_encode($r);
					break;
				case 'PHP ARRAY':		//if( !xpCaptcha::check($q['vcode']) ) $ret = array('status' =>'failed',  'msg'=>'Robot check failed, Vcode error, click on image to refresh code', 'msg_type'=>'warning' );

			}
			$rs['data'] = $data;
		}
		$rs['tpl'] = '_csv2json.phtml';
		$rs['TITLE'] = 'SITEMIN CSV2JSON';
		return array('view'=>'/sitemin/view/user/index.phtml','data' => array('rs' => $rs));
	}	
	function _short($r){
		//return base_convert(array_sum(str_split($r)), 16, 36);
		$ds = str_split($r);
		foreach ((array)$ds as $k=>$d){
			if($k%2) unset($ds[$k]);
		}
		$r = implode('', $ds);
		if(strlen($r)>12) $r = $this->_short($r);
		return $r;
	}
	function requestpasswordAction(){
		$q = $this->q;
	       // die(asdfasdf);
		if ($l->captcha($q))
		 	$r = _factory('sitemin_model_login')->sendresetpasswordlink($this->q);
			
	}		
	function resetpasswordAction(){
		$q = $this->q;
	   // die(asdfasdf);
		if($q['save']){
			if ($l->captcha($q)){
				$q['n2048'] = $q['p1'];
				_factory('sitemin_model_user')->update_password($q);
			}
			sleep(3);
			exit;
		}else{
			$hash = key($q);
			//check hash
			$u = _factory('sitemin_model_user')->check_hash($hash);
		   if(!$u) $rs['overdue'] =1;
		   $rs['hash'] = $hash;
		   $rs['user'] = $u;;
	   }
	   //check hash
	   //if google bot check used
	   //f**k the captcha which is not able to work in old firefox: _x_captcha data.invigorgroup.com/sitemin/login
	   $rs['google_key'] = _config('google,bot check,key');
	   $rs['no_captcha'] = !$this->captcha;
	   $rs['tpl'] = 'user/_resetpassword.phtml';
	   $rs['TITLE'] = 'SITEMIN USER';
	   return array('view'=>'/sitemin/view/index.phtml', 'data' => array('rs' => $rs));
   }  
	function loginAction() {
		if( !($r = defaultHelper::return_url())) $r = _X_URL.'/sitemin/dashboard';

		$q = $_REQUEST;
		if($q['password']){	//try login
			$ret = $this->_login($q);
			if($ret['status']=='ok'){
				$u = _factory('sitemin_model_login')->current();
				$ip = xpAS::get_client_ip();
				$u['ip'] = $ip;
				_factory('sitemin_model_message')->send_to_group('sitemin', 'user '.$u['id'] .' '.($u['email'] ? '-'.$u['email'] : '@'.base64_decode($u['username']))." logged in from $ip", -1);
				$arr = array('user_id' => $u['id'], 'router' => 'logged in', 'data'=>$u, );
				_factory('sitemin_model_log')->insert($arr);
			}
			sleep(1);	//slow down
			die( json_encode($ret));
		}
		$rs['captcha_html'] = $this->captcha->html();
		$rs['ret'] = $r;
		$rs['tpl'] = 'user/_login.phtml';
		$rs['TITLE'] = 'SITEMIN LOGIN';
		return array('view'=>'/sitemin/view/index.phtml', 'data' => array('rs' => $rs));
	}

	function _login($q){
		$l = _factory('sitemin_model_login');

		if ( !$this->captcha->validate($q)) return array('status' =>'failed', 'msg'=>'Robot check failed', 'msg_type'=>'warning' );

		if (!$ret && !($r = $l->login($q)))   	$ret =  array('status' =>'failed', 'msg'=>'login failed, username and password are not match', 'msg_type'=>'warning' );
		if (!$ret)  $ret =  array('status' =>'ok', 'msg'=>xpAS::get(defaultHelper::data_get('admin,login'),'permission,login'),  'msg_type'=>xpAS::get(defaultHelper::data_get('admin,login'),'user,username') );
		return $ret;
	}

	function logoutAction(){
		_factory('sitemin_model_login')->logout();
		xpAS::go('/sitemin');
	}

}
