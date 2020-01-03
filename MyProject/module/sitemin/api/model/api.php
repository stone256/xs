<?php

/**sitemin_api_
 * Enter description here...
 *
 */

class sitemin_api_model_api {

	static $query;

	function __construct() {
		$this->get_query();

	}

	function get_query($n=null){
		$query_string = urldecode($_SERVER['QUERY_STRING']);
		if(!$this->query) {
			$q = $query_string ? $query_string : file_get_contents("php://input");
			// if not raw format:
			$q = preg_match('/(\%40|\%23|\%24|\%25|\%5E|\%26|\%2B|\%7D|\%7B|\%22|\%3A|\%3F|\%3E|\%3C|\%60|\%5C|\%5D|\%5B|\%27|\%3B|\%2F|\%2C)/',$q) ? urldecode($q) : $q;
			if($r = json_decode( $q, 1) ) $this->query =$r;
			if($r = json_decode( urldecode($q), 1)) $this->query =$r;
			if(!$this->query)  parse_str($q, $this->query);

		}
		return $n? $this->query[$n] : $this->query;
	}

	/**
	 * retrieve currrent api
	 *
	 */
	function get_gateway($url=''){
		static $_gateway;
		if(!$_gateway) {
	            $path = 'sitemin/api/gateway/';
	            $lp = _X_MODULE;
	            $gateway = xpFile::file_in_dir("$lp/$path", array('level' => '50', 'path' => 1, 'include' => '/index\.php/ims'));
	            $batch = uniqid();
	            $old = xpAS::key(xpTable::load('api')->gets(), 'id');
	            foreach ($gateway as $k => $v) {
	                $rs = explode("$path", $v);
	                $rs = preg_replace('/\/index\.php/ims', '', $rs[1]);
	                $name = str_replace('/', '_', $rs);
	                $class_name = 'sitemin_api_gateway' . $name . '_index';
	                $class = _factory($class_name);
	                $rf = new ReflectionClass($class_name);
	                $g[$name]['desc'] = $rf->getDocComment();
	                $g[$name]['desc'] = preg_replace('/^\/\*\*[^\*]*?|\n[^\n]*?\*\/$/ims', '', $g[$name]['desc']);
	                $g[$name]['desc'] = trim(preg_replace('/\n\s+\*/ims', "\n", $g[$name]['desc']));

	                $g[$name]['path'] = $rf->getFileName();
	                $g[$name]['path'] = str_replace(_X_MODULE, '', $g[$name]['path']);
	                $g[$name]['L'] = str_replace('/sitemin/api/gateway', '/api', $g[$name]['path']);
	                $g[$name]['L'] = str_replace('/index.php', '', $g[$name]['L']);
	                foreach (get_class_methods($class) as $km => $vm) {
	                    if (preg_match('/^\_/', $vm)) continue;
	                    $vn = $rf->getMethod($vm)->getDocComment();
	                    $vn = preg_replace('/^\/\*\*[^\*]*?|\n[^\n]*?\*\/$/ims', '', $vn);
	                    $vn = trim(preg_replace('/\n\s+\*/ims', "\n", $vn));
	                    $g[$name]['method'][] = array('name' => "$vm", 'desc' => $vn);

	                    $frr = array(
	                        'id' => md5($name . '-' . $vm),
	                        'batch' => $batch,
	                        'gateway' => $name,
	                        'method' => $vm,
	                        'path' => $g[$name]['path'],
	                        'url' => $g[$name]['L'] . '/' . $vm,
	                        'description' => $vn,
	                    );
	                    $r = xpTable::load('api')->write($frr, array('id' => $frr['id']));
	                    $new[] = $r;
	                    unset($old[$r]);
	                }
	            }
	        }
	        //remove old entry
	        $r = xpTable::load('api')->deletes(array('id' => array_keys($old)));
	        $r = xpTable::load('api_acl')->deletes(array('api_id' => array_keys($old)));
	        $_gateway = $g;
	        if($url){
	            foreach($_gateway as $g) if($q['L'] == $url) return $g;
	        }else {
	            return $_gateway;
	        }
	}
	/**
	 *search for matched url: for ajax autocompleting
	 */
	function search($value, $key='url'){
		$value = addslashes($value);
		$key = addslashes($key);
		/**
		 * $arr =array(
		 * 	limit=>1,25,
		 *  order=>name,-age,mms // -: DESC
		 *  fields=name,age,email or * //default * if empty
		 *  search=>array(array(name="peter",id<12,syatus is not_null, ql <> 121),
		 * 				array(email like "peter%")
		 * 				)
		 * 				* inside array is AND condition
		 * 				*between array is OR condition
		 *  or search=>name="peter",id<12,status is not_null, ql <> 121
		 * 	status=>0,1,2,3, *=all//default =1,
		 * 	count=1 ; return total counts
		 * )
		 */

		$arr=array(
					'search'=>array(" {$key} like '%{$value}%'"),
					'limit'=>12,
				);
		$rs = xpTable::load('api')->lists($arr);
		return $rs;
	}
	/**
	 *find api entry from gateway's url
	 */
	function get_acl_by_url($url){
		return xpTable::load('api')->get(array('url'=>$url));
	}

}
