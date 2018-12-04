<?php
class sitemin_housekeeping_model_housekeeping {

	function purg_log(){
		$log_keeped =  15;
		$date = xpDate::lastNdate(xpDate::today(), $log_keeped);
		xpTable::load('sitemin_log')->deletes(array("created < '$date'"));
	}

}
