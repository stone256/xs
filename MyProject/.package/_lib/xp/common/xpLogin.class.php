<?php
abstract class xpLogin {
	/**
	 * init with user object
	 *
	 * @param class $user : object
	 */
	function __contruct($user, $type = 'frontend') {
		$this->user = $user;
		$this->type = $type;
//		$this->type_field = 'type';
		$this->login_field = 'email';
		$this->password_field = 'password';
		$this->hash_field = 'hash';
		$this->status_field = 'status';
		$this->status_active = 'active';
		session_start();
	}
	/**
	 * change user password
	 *
	 * @param array	$arr
	 */
	function changePassword($arr) {
		$pwd = $this->user->password($arr[$this->password_field]);
		$crr[$this->login_field] = $arr[$this->login_field];
		if (!$r = $this->check($crr)) return false;
		return $this->user->save(array('password' => $pwd), $crr);
	}
	/**
	 * check if user exist
	 *
	 * @param array $arr	: user indentifies
	 * @return array of user row or null;
	 */
	function check($arr) {
		return $this->user->get($arr);
	}
	/**
	 * return current user
	 * also use this to check logged in
	 *
	 *@param  string $type	:user type : admin frontend, client..
	 *
	 * @return mix $user  or false
	 */
	function currentUser() {
		return xpAS::get($_SESSION, 'login,' . $this->type);
	}
	/**
	 * logout a user
	 *
	 * @param string	$type	:admin,frontend,clients,agents..
	 */
	function logout() {
		xpAS::set($_SESSION, 'login,' . $this->type, null);
	}
	/**
	 * try to login a user
	 *
	 * @param array $arr : user credential
	 * @return  array $user  or false;
	 */
	function login($arr, $login_field=null) {
		$login_field = $login_field ? $login_field : $this->login_field ; 
		$r = $this->check(array($login_field => $arr[$this->login_field]));
		$pwd = $this->user->password($arr[$this->password_field]);	//if $pwd == -1 login by admin used server key
		//die($pwd); //to show hash
		if (($pwd == -1 ||$r[$this->password_field] === $pwd )  && $r[$this->status_field] == $this->status_active) return $this->success($r);
		sleep(1);
		return $false;
	}
	/**
	 * successful login
	 *
	 * @param array $r	: user row
	 * @param user type $type
	 * @return user row
	 */
	function success($r) {
		xpAS::set($_SESSION, 'login,' . $this->type, $r);
		return $r;
	}
}
