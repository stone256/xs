<?php

/**
 * default sitemin controller
 *
 */
class sitemin_loginController extends _system_defaultController {


	function __construct($argv){
		$this->q = $_REQUEST;
		session_start();
		//$this->return_url = defaultHelper::return_url();
		if(_X_SITEMIN_LOG === true && $_p != '/sitemin/keepalive') _factory('sitemin_model_log')->insert();
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
				case 'PHP ARRAY':
				default:
					$data = var_export($r, 1);
					break;
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
        _factory('sitemin_model_login')->sendresetpasswordlink($this->q);
    }
	function loginAction() {
		if( !($r = defaultHelper::return_url())) $r = _X_URL.'/sitemin/dashboard';
		//if google bot check used
		//f**k the captcha which is not able to work in old firefox: _x_captcha data.invigorgroup.com/sitemin/login
		$rs['google_key'] = _config('google,bot check,key');
		$rs['no_captcha'] = $_COOKIE['_x_captcha'] ;
		$rs['ret'] = $r;
		$rs['tpl'] = 'user/_login.phtml';
		$rs['TITLE'] = 'SITEMIN LOGIN';
		return array('view'=>'/sitemin/view/index.phtml', 'data' => array('rs' => $rs));
	}

	function loginajaxAction() {
		$q = $_REQUEST;
		//$l = new sitemin_model_login($u = new sitemin_model_user);
		////google captach
		////if ($l->captcha($q)) 	return array('data' => array('msg'=>'Robot check failed', 'msg_type'=>'warning' ));
		//if( !xpCaptcha::check($q['vcode']) ) $ret = array('status' =>'failed',  'msg'=>'Robot check failed, Vcode error, click on image to refresh code', 'msg_type'=>'warning' );
		//if (!$ret && !($r = $l->login($q)))   	$ret =  array('status' =>'failed', 'msg'=>'login failed, username and password are not match', 'msg_type'=>'warning' );
		//if (!$ret)  $ret =  array('status' =>'ok', 'msg'=>xpAS::get(defaultHelper::data_get('admin,login'),'permission,login'),  'msg_type'=>xpAS::get(defaultHelper::data_get('admin,login'),'user,username') );
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
//		if(_X_SITEMIN_LOG === true) _factory('sitemin_log_model_log')->insert();
		echo json_encode($ret);
	}

	function _login($q){
		$l = _factory('sitemin_model_login');
		//google captach
		//f* the captcha which is not able to work in old firefox: _x_captcha data.invigorgroup.com/sitemin/login
		$parent_uri = xpAS::preg_get(_X_URL_REQUEST, '/^(.*?\/)login.*/ims', 1);

		if (!$_COOKIE['_x_captcha']== 'THE_COOKIE_VALUE_NO_CAPTCHA_&*%*&FGHDFG$%bvsdfE%T^Y3342' && $l->captcha($q)) 	return array('status' =>'failed', 'msg'=>'Robot check failed', 'msg_type'=>'warning' );
		//if( !xpCaptcha::check($q['vcode']) ) $ret = array('status' =>'failed',  'msg'=>'Robot check failed, Vcode error, click on image to refresh code', 'msg_type'=>'warning' );
		if (!$ret && !($r = $l->login($q)))   	$ret =  array('status' =>'failed', 'msg'=>'login failed, username and password are not match', 'msg_type'=>'warning' );
		if (!$ret)  $ret =  array('status' =>'ok', 'msg'=>xpAS::get(defaultHelper::data_get('admin,login'),'permission,login'),  'msg_type'=>xpAS::get(defaultHelper::data_get('admin,login'),'user,username') );
		return $ret;
	}

	function logoutAction(){
		_factory('sitemin_model_login')->logout();
		xpAS::go('/sitemin');
	}
}
