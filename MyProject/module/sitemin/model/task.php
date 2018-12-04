<?php
class sitemin_model_task {

	function __construct() {
	}

	/**
	 * running task @ background
	 */
	function run($task, $get=null ){

		$root =_X_ROOT;
		if($get && is_array($get)){
			$get = http_build_query($get);
		}
		if($get) $get = preg_replace('/\&/ims', '\\&', $get);
		if(preg_match('/^\$/', $task)){ //system cmd
			$task{0}=' ';
			$cmd = "$task $get >/dev/null 2>&1 &";
		}else{ //site cmd
			$cmd = "cd $root && php x2cli $task $get >/dev/null 2>&1 &";
		}
		//$cmd = "cd $root && php x2cli $task $get";

		//echo "\n\n $cmd \n\n";
		$r = shell_exec($cmd);
		return $r;
	}

}
