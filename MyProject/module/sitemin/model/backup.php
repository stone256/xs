<?php
class sitemin_model_backup {

	function __construct() {
	}

	/**
	 * running task @ background
	 */
	function database($q, $db='default,db'){

		$cfg['db'] = _config($db);

		$cmd = "mysqldump -h {$cfg['db']['host']} -p{$cfg['db']['password']} -u {$cfg['db']['user']}  {$cfg['db']['database']} ";

		$segment=$q['database'];

		switch($segment){
			case 'daily':
				$name = date('N').".sql";
		}

		$path = _X_DATA.'/backup/';

		$cmd .= " > {$path}{$name} 2>&1 & ";

		$r = shell_exec($cmd);
		return $r;
	}

}
