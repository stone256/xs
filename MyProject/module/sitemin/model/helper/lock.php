<?php


class sitemin_helper_model_lock {


	var $locker_try = 1000;		//try the locker xx time before give up.
	var $max_wait = 100000;		//0.1 second;
	var $max_loked_time = 10;		//locked for 10 seconds;

	/**
	 * @var obj
	 */
	function __construct($server = null,  $port = null){
		if(!class_exists('Memcached')) die("Memcached requested for ". get_class());
		$memserver = _config('memcached');

		$this->mc  = new Memcached();
		$this->mc->addServer($server?$server:$memserver['server'] , $port?$port:$memserver['port']);
	}

	function get_server(){
		return $this->mc;
	}

	function set($id, $value){
		$this->mc->set($id, $value);
	}
	function get($id){
		return $this->mc->get($id);
	}
	function delete($id){
		return $this->mc->delete($id);
	}


	/**
	 * @var
	 */
	function get_locker($id){
		while ( $ct++ < $this->locker_try) {
			if($this->mc->add($id,"1",$this->max_loked_time)){
				return true;
			} else {
				usleep(mt_rand(20, $this->max_wait));
			}
		}
		return false;
	}
	function remove_locker($id){
		$this->mc->delete($id);
	}

	/**
	 * create lock with 0 value
	 */
	function counter_create($id){
		$this->mc->set($id."_value", 0);
	}

	/**
	 * counter increase
	 */
	function counter_increase($id, $value=1){
		if($this->get_locker($id)){
			$r = $this->mc->increment($id."_value", $value);
			$this->mc->delete($id);
			return $r;
		}
		return -1;
	}

	/**
	 * counter decrease
	 */
	function counter_decrease($id, $value=1){
		if($this->get_locker($id)){
			$r = (int) $this->mc->decrement($id."_value", $value);
			$this->mc->delete($id);
			return $r;
		}
		return -1;
	}

	/**
	 * counter remove
	 */
	function counter_remove($id){
		$this->mc->delete($id."_value");
	}


}
