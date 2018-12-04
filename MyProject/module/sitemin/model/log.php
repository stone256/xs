<?php
class sitemin_model_log {
	function __construct() {
		$this->table = 'sitemin_log';
	}

	/**
	 * insert a log
	 */
	function insert($brr = array()){
		$u = _factory('sitemin_model_login')->current();
		$arr = $brr ? $brr : array(
				'user_id' => $u['id'],
				'router' => _url(),
				'data'=>json_encode(array('user'=>$u, 'request'=>xpAS::round_off($_REQUEST, 'password'), 'cookie'=>$_COOKIE, 'server'=>$_SERVER)),
			);
		xpTable::load($this->table)->insert($arr);
	}

	/**
	 * this function need be called manually or long cron, it is not designed for daily task. you shouldn't around less the month once.
	 */
	function archive(){
		//get archive deu time from var
		ini_set('memory_limit', -1);
		$days = _factory('sitemin_model_var')->get('sitemin/log/keeped_in_days');
		if(!$days) return;
		$start_date = xpDate::lastNdate(xpDate::today(), $days);
		$fn = _X_LOG . "/sitemin-log.archive.".date("Y-m-d.H-i-s");
		$rs = xpTable::load('sitemin_log')->gets(array("created < '{$start_date}'"));
		if($rs){
			file_put_contents($fn, json_encode($rs));
			xpTable::load('sitemin_log')->deletes(array("created < '{$start_date}'"));
		}

	}

    function login24(){
        $arr = array(
            'router'=>'logged in',
            "created >'" .xpDate::last_date(xpDate::timestamp())."'",
        );
        $rs = xpTable::load($this->table)->gets($arr);
        return $rs;
    }


    function last24(){
        //1>user
        $r = _factory('sitemin_model_user')->gets();
        //$dataset['user'] = ['label'=>'users','data'=>[[1,(int)$r['page']['total']],[3,(int)$r['page']['total']] ]];
        $dataset =[];
	array_push($dataset, ['user', (int)$r['page']['total']] );
        //2>logged in
        //$dataset['login'] = ['label'=>'logged in', 'data'=>[[2,count($this->login24())],[4,count($this->login24())] ]];
        array_push($dataset, ['logged in', count($this->login24())] );
        //3>page access
        $date =  date("Y-m-d H:i:s", strtotime("-1 days"));
        $r = xpTable::load($this->table)->gets(array("created > '{$date}' "), 'count(user_id) as c');
        //$dataset['viewed'] = ['label'=>'page viewed', 'data'=>[[3, (int)$r[0]['c']],[6, (int)$r[0]['c']]]];
        //$dataset[] = [['bar', (int)($r[0]['c']/1000)],[6, (int)($r[0]['c']/1000)]];
	array_push($dataset, ['viewed', (int)($r[0]['c']/1000)] );
	$dataset = [ ["January", 10], ["February", 8], ["March", 4], ["April", 13], ["May", 17], ["June", 9] ];
        return $dataset;
    }

    var $exclude = "('/sitemin/cron/call', '/sitemin/cron', '/sitemin/user/message', 'logged in', '/sitemin/login')";

    function top10url($days=1){
        $date =  date("Y-m-d H:i:s", strtotime("-{$days} days"));
        $sql = "SELECT router, COUNT(*) AS c FROM {$this->table} WHERE router NOT IN {$this->exclude} AND created >'$date' GROUP BY router ORDER BY c DESC LIMIT 10";
        $rs = xpTable::load($this->table)->q($sql);
        return $rs;
    }

    function top5url($days=30){
        $date =  date("Y-m-d H:i:s", strtotime("-{$days} days"));
        $sql = "SELECT router, COUNT(*) AS c FROM {$this->table} WHERE router NOT IN {$this->exclude} AND created >'$date' GROUP BY router ORDER BY c DESC LIMIT 5";
        $rs = xpTable::load($this->table)->q($sql);
	$routers = xpAS::get($rs, '*,router');
	$rs = xpTable::load($this->table)->gets(array('router'=>$routers));

	foreach ($rs as $k=>$v) {
		$brr[$v['router']]['label'] = $v['router'];
		foreach ($routers as $kr => $vr) $brr[$vr]['data'][xpAS::preg_get($v['created'], '/^.{10}/ims')] += ($vr==$v['router'] ? 1 : 0);
		//$brr[$v['router']]['data'][xpAS::preg_get($v['created'], '/^.{10}/ims')] ++ ;

	}
	foreach ($brr as $k => $v) {
		$ds = [];
		foreach($v['data'] as $kd=>$vd) $ds[] = array(str_replace('-','',$kd),$vd*8);
		$brr[$k]['data'] = $ds;
	}
//_dv($brr);
        return $brr;
    }

}
