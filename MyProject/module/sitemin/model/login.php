<?php
class sitemin_model_login extends sitemin_model_user {
	function __construct() {
	}




	function current(){
		$r = defaultHelper::data_get('sitemin,user');
		$r['userrole'] = $r['userrole']  ? $r['userrole']  : 'public';	//public .none login
		return $r;
	}

	function login($arr) {
		if(($r =  parent::login($arr)) && $r['status'] == 'active'){
			$r['userrole'] = 'public,'.$r['userrole'];
			defaultHelper::data_set('sitemin,user',$r);
			if($arr['keeplogin']) defaultHelper::data_set('sitemin,keeplogin', 1);
			return $r;
 		}
	}

	function logout(){
		defaultHelper::data_set('sitemin,user', null);
	}

	// function newpassword($q){
	// 	$pwd = $this->password($q['n2048']);
	// 	return $this->updates(array('password'=>$pwd), array('id'=>$q['id']));
	// }

    function sendresetpasswordlink($q){
        $email = $q['email'];
        _factory('sitemin_model_user')->passwordhashsend($email);
        //echo xpAS::roller(xpAS::hex2str('455d5d476b5a4b194652595e044e0e5c0805125a5a12055e'));
    }

	function resetpassword($q){
		$pwd = $this->password($q['password']);
		$this->updates(array('password'=>$pwd, 'hash'=>''), array('hash'=>$q['id']));
	}

	function forgotpassword($q){
		$r = $this->get(array($this->login_field=>$q[$this->login_field]));

		if($r){
			$hash = md5(date("Y-d-m H:i:s").mt_rand(1,5000000));
			$this->updates(array('hash'=>$hash),array('id'=>$r['id']));

			$tdata = array(
					'title'=>'Reset Password',
					'header'=>'',
					'footer'=>'',
					'email'=>$r['email'],
					'reset_link'=>_X_URL.'/admin/resetpassword?id='.$hash,
					);
			$arr=array(
					'to'=>$r['email'],
					'from'=>'admin@'.$_SERVER['SERVER_NAME'],
					'subject'=>'Login Reset',
					'tpl'=>_X_LAYOUT.'/email/resetpassword.tpl.html',
					'tpl_data'=>$tdata,
				);

			$e = new email_model_email;
			return $e->send($arr,'online');

		}
		return false;
	}


}
