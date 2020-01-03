<?php

/**
 * default api controller
 *
 */
class sitemin_api__apiController extends _system_defaultController {


	function __construct(){
		session_start();

		$this->query = _factory('sitemin_api_model_api')->get_query();

	}

	function loginAction(){
		$q = $this->query;
		if($user = _factory('sitemin_api_model_user')->login_check($q['id'], $q['key'])){
				/**
				 *to do: user quota
				 */
				$q['token'] = md5(uniqid(), false);
				defaultHelper::data_set('sitemin_api_token', $q['token']);
				defaultHelper::data_set('sitemin_api_user', $user);
		}
		$this->_return(array( "status"=>$q['token'] ? "success" : "failed", 'token'=>$q['token']));
	}

	function dispatchAction(){

		$q = $this->query;

		//login used;
		if($q['key']  && $user = _factory('sitemin_api_model_user')->login_check($q['id'], $q['key'])){
				/**
				 *to do: user quota
				 */
				$q['token'] = md5(uniqid(), false);
				defaultHelper::data_set('sitemin_api_token', $q['token']);
				defaultHelper::data_set('sitemin_api_user', $user);
		}
		$token = defaultHelper::data_get('sitemin_api_token');
		$user = defaultHelper::data_get('sitemin_api_user');
		//check token
		if(!$token || $token != $q['token']) $this->_return(json_encode(array('status'=>'failed', 'msg'=>'server open failed')));


		//find gateway
		$url = $_SERVER['REDIRECT_URL'];
		$api = _factory('sitemin_api_model_api')->get_acl_by_url($url);

		//check acl
		if(!_factory('sitemin_api_model_acl')->check_acl($user, $api)){
			$this->_return(json_encode(array('status'=>'failed', 'msg'=>'sector open failed')));
		}
		$path = $api['path'];
		$p = preg_replace('/(^\/|\.php$)/ims', '', $path);
		$class_name = str_replace('/', '_', $p);
		$action = xpAS::preg_get($url, '/[^\/]+$/ims');

		$r = _factory($class_name)->$action($q);

		$r['token'] = $token;
		$this->_return($r);
	}

	function _return($arr){
		$arr['token'] = $arr['token'] ? $arr['token'] : md5(mt_rand(1000,10000));
		if($this->query['debug']) $arr['query'] = $this->query;
		die(json_encode($arr));
	}

}
/**
 * array (
  'REDIRECT_STATUS' => '200',
  'HTTP_HOST' => 'data.invigorinsights.com',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:53.0) Gecko/20100101 Firefox/53.0',
  'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,* / *;q=0.8',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.5',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
  'HTTP_COOKIE' => '_ga=GA1.2.1676258583.1487741294; optimizelyEndUserId=oeu1494811813047r0.02984622425311667; optimizelySegments=%7B%222753952551%22%3A%22direct%22%2C%222764172390%22%3A%22ff%22%2C%222794160307%22%3A%22false%22%2C%223184761018%22%3A%22none%22%2C%223519232639%22%3A%22false%22%2C%223523031494%22%3A%22direct%22%2C%223536961287%22%3A%22ff%22%2C%223542780199%22%3A%22none%22%2C%227527430098%22%3A%22true%22%7D; optimizelyBuckets=%7B%7D; AMCV_1124C2D754E497DC0A4C98C6%40AdobeOrg=-1330315163%7CMCIDTS%7C17354%7CMCMID%7C11747937311312501403475820134510443966%7CMCAAMLH-1499923728%7C8%7CMCAAMB-1499923730%7CNRX38WO0n5BH8Th-nqAG_A%7CMCOPTOUT-1499326128s%7CNONE%7CMCSYNCSOP%7C411-17361%7CMCAID%7C2C3E0E47052C303A-600000C0C00015AA; _vwo_uuid_v2=63E10BB2B22E3CB4A8EB58C1ADC15234|f67cb6375e5c5b55de8c2139dcbf2c5a; _vwo_uuid=AB448D37237B49D2E22DC793A995595E; rr_rcs=eF5jYSlN9khKNDI3NTW21E1JTDLUNbFIMdRNTDVK0jW1NDU3szC1SE5LteTKLSvJTOEzMzPQNdQ1BACPGw5C; utag_main=v_id:015de3a5d3ef000300071cff35490004c005600900bd0$_sn:1$_ss:1$_st:1502764682034$ses_id:1502762882034%3Bexp-session$_pn:1%3Bexp-session$dc_visit:1$dc_event:1%3Bexp-session$dc_region:ap-southeast-2%3Bexp-session; __zlcmid=iMgiu49dLlZjbv; _x_debug_=1; PHPSESSID=1pke24nggefti6cc5l4m64vhp3',
  'HTTP_CONNECTION' => 'keep-alive',
  'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
  'HTTP_CACHE_CONTROL' => 'max-age=0',
  'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
  'SERVER_SIGNATURE' => 'Apache/2.4.7 (Ubuntu) Server at data.invigorinsights.com Port 80',
  'SERVER_SOFTWARE' => 'Apache/2.4.7 (Ubuntu)',
  'SERVER_NAME' => 'data.invigorinsights.com',
  'SERVER_ADDR' => '172.31.29.28',
  'SERVER_PORT' => '80',
  'REMOTE_ADDR' => '61.68.21.86',
  'DOCUMENT_ROOT' => '/var/www/data/public',
  'REQUEST_SCHEME' => 'http',
  'CONTEXT_PREFIX' => '',
  'CONTEXT_DOCUMENT_ROOT' => '/var/www/data/public',
  'SERVER_ADMIN' => 'webmaster@localhost',
  'SCRIPT_FILENAME' => '/var/www/data/public/index.php',
  'REMOTE_PORT' => '36396',
  'REDIRECT_QUERY_STRING' => '{%22aaaa%22:0}',
  'REDIRECT_URL' => '/api/v2/reports/price-history',
  'GATEWAY_INTERFACE' => 'CGI/1.1',
  'SERVER_PROTOCOL' => 'HTTP/1.1',
  'REQUEST_METHOD' => 'GET',
  'QUERY_STRING' => '{%22aaaa%22:0}',
  'REQUEST_URI' => '/api/v2/reports/price-history?{%22aaaa%22:0}',
  'SCRIPT_NAME' => '/index.php',
  'PHP_SELF' => '/index.php',
  'REQUEST_TIME_FLOAT' => 1510873170.402,
  'REQUEST_TIME' => 1510873170,
)
 */
