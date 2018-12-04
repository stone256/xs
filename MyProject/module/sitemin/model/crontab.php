<?php

/**
* @author : peter<stone256@hotmail.com>
* cron control module
*
* 1> /sitemin/housekeeping/backup/batabase=daily
*	this will trigger local call  $php x2cli /sitemin/housekeeping/backup/batabase=daily
* 2> $ls -l
*	this will call syatem command .
*	by default this is disabled you need turn _X_CRON_SYSTEN = true;
*
*/

class sitemin_model_crontab {

	//cron job table
	var $table = 'sitemin_crontab';

	//max times try to find next
	var $max_calculate = 200;
	function __construct() {
	}

	function save($q){
		$id = $q['id'];
		$data = xpAS::round_up($q, 'minute,hour,date,month,year,weekday,task,next,note');
		$id = xpTable::load($this->table)->write($data, array('id'=>$id));
		return $id;
	}

	function status_toggle($q){
		$crr = ['id'=>$q['id']];
		$rs = xpTable::load($this->table)->toggle($crr, 'status', 'active', 'inactive');
	}

	function gets($crr=array()){
		$rs = xpTable::load($this->table)->gets($crr, '*', 'status');

		return $rs;
	}

	function trigger(){

		$time = date("Y-m-d H:i:s");
		//find task has executing("next" filed) time less then now
		$crr = array("next < '$time'", "status"=>'active', );
		$rs = xpTable::load($this->table)->gets($crr);
		foreach ((array)$rs as $kc => $r) {
			//stop system command if not allowed
			if(_X_CRON_SYSTEN !== true && preg_match('/^\$|\&\&|\;|(?<!(\>|\\))\&/ims',$r['task']) ) continue;
			//doing task:
			_factory('sitemin_model_task')->run($r['task']);
			//get next
			if($next = $this->_next($r, $time)){
				xpTable::load($this->table)->updates(array('next'=>$next),array('id'=>$r['id']));
			};
		}
	}

	function _next($r, $time){
		$end = false;
		$date_object = xpDate::dateOBJ($time);
		$date_object['second'] = 59;
		while(!$end && $ct ++ < $this->max_calculate){
			$time_map = $this->_time_map($date_object);
			$time_slots = $this->_get_slots($time_map, $r, $this->_iso_time_stamp($date_object));
			//minute
			$found['minute'] = true;
			if(false === ($date_object['minute'] = xpAS::least_great($time_slots['minute'], $date_object['minute']))){
				$date_object['minute'] = $time_slots['minute'][0];
				$found['minute'] = false;
			}
			//hour
			$found['hour'] = true;
			if(false === ($hour = xpAS::least_great($time_slots['hour'], (int)$date_object['hour'], $found['minute']))){
				$date_object['minute'] = $time_slots['minute'][0];
				$date_object['hour'] = $time_slots['hour'][0];
				$found['hour'] = false;
			}
			if($date_object['hour'] != $hour){
				$date_object['minute'] = $time_slots['minute'][0];
			}
			$date_object['hour'] = $hour;
			//date
			$found['date'] = true;
			$reset['date'] = false;
			if(false === ($date = xpAS::least_great($time_slots['date'], $date_object['date'], $found['hour']))){
				$date_object['minute'] = $time_slots['minute'][0];
				$date_object['hour'] = $time_slots['hour'][0];
				$date_object['date'] = $time_slots['date'][0];
				$reset['date'] = true;
				$found['date'] = false;
			}
			if($date_object['date'] != $date){
				$date_object['minute'] = $time_slots['minute'][0];
				$date_object['hour'] = $time_slots['hour'][0];
			}
			$date_object['date'] = $date;

			$found['month'] = true;
			$reset['month'] = false;
			if(false === ($month = xpAS::least_great($time_slots['month'], $date_object['month'], $found['date']))){
				$date_object['minute'] = $time_slots['minute'][0];
				$date_object['hour'] = $time_slots['hour'][0];
				$date_object['date'] = $time_slots['date'][0];
				$date_object['month'] = $time_slots['month'][0];
				$reset['month'] = true;
				$found['month'] = false;
			}
			if($date_object['month'] != $month){
				$date_object['minute'] = $time_slots['minute'][0];
				$date_object['hour'] = $time_slots['hour'][0];
				$date_object['date'] = $time_slots['date'][0];
			}
			$date_object['month'] = $month;

			if($found['month'] && $reset['date']){
				$date_object['minute'] = $time_slots['minute'][0];
				$date_object['hour'] = $time_slots['hour'][0];
				$date_object['date'] = $time_slots['date'][0];
				continue;	//reset time map as new month may have different date
			 }
			$year = xpAS::least_great($time_slots['year'], $date_object['year'], $found['month']);

			if($date_object['year'] != $year){
				$date_object['minute'] = $time_slots['minute'][0];
				$date_object['hour'] = $time_slots['hour'][0];
				$date_object['date'] = $time_slots['date'][0];
				$date_object['month'] = $time_slots['month'][0];
			}
			$date_object['year'] = $year;

			if($reset['month']) continue;

			//check Minutes
			$end = ture;
		}

		if($ct == $this->max_calculate) return false;

		return $this->_iso_time_stamp($date_object);

	}

	function _iso_time_stamp($date_object){
		return 	"{$date_object['year']}-".sprintf("%02d",$date_object['month'])."-".sprintf("%02d",$date_object['date'])." " .sprintf("%02d",$date_object['hour']).":". sprintf("%02d",$date_object['minute']).":". sprintf("%02d",$date_object['second']);
	}

	function _time_map($date, $years=10){
                return array(
                                'weekday'   =>array(1,7),
                                'minute'=>array(0,59),
                                'hour'	=>array(0,23),
                                'date'	=>array(1, $date['mdays']),
                                'month' =>array(1,12),
                                'year'	=>array($date['year'], $date['year']+$years), //will add manually here
                        //        'timestamp'=> "{$date['year']}-{$date['month']}-{$date['date']} {$date['hour']}:{$date['minute']}",
                        );
        }

        function _get_slots($map, $r, $today){
                 $slots = array();
                 foreach ($map as $k=>$v){
			 if(trim($r[$k]) =='*') $r[$k] = implode(',', range($v[0],$v[1]));
                        $marks = preg_split('/\s*\,\s*/ims',$r[$k]);
                 	foreach ((array)$marks as $km=> $vm) {
                                 $ms = range($v[0],$v[1], $n);
				 if($n = xpAS::preg_get($vm, '/\*\/(\d+)/ims', 1)){
					$ms = range($v[0] ? $n : 0, $v[1], $n);
                                        unset($marks[$km]);
                                        $marks = xpAS::extend($marks, (array)$ms);

                                 }
                         }
                         //apply(week day filter if is date
                         if($k == 'date') foreach ($marks as $km => $vm) if(!in_array(xpDate::weekDay("{$today['year']}-{$today['month']}-{$vm}") , $slots['weekday'])) unset($marks[$km]);
                         sort($marks);
                         $slots[$k] = $marks;
                 }
                 return $slots;
         }

}
