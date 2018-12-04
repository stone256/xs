<?php
class sitemin_housekeepingController extends sitemin_indexController {

	function logarchiveAction(){
		file_put_contents('/tmp/housekeeping-logarchive', date("Y-m-d H:i:s"));
		_factory('sitemin_model_log')->archive('sitemin_model_log');

	}

	function backupAction(){
		file_put_contents('/tmp/housekeeping-backup', date("Y-m-d H:i:s"));
		$cmd = array_keys($this->q)[0];
		switch ($cmd) {
			case 'database':
				_factory('sitemin_model_backup')->database($this->q);
				break;

			default:
				// code...
				break;
		}

	}

}
