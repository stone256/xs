<?php
/**
 * @name 	: local config
 * @author 	: peter<stone256@hotmail.com>
 *
 *  this file will be included in cli calls.
 *  	setup according to you needs 
 */


$apache_data=array (
		  'REDIRECT_UNIQUE_ID' => 'd0kswwbixVze9J38AAQEAAE6LPisAAAAD',
		  'REDIRECT_STATUS' => '200',
		  'UNIQUE_ID' => 'bixVze9J38AAQEA0lqwe0sWfdsdAE6LPisAAAAD',
		  'HTTP_HOST' => 'host.my.local',
		  'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:38.0) Gecko/20100101 Firefox/38.0',
		  'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.5',
		  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
		  'HTTP_REFERER' => '',			
		  'HTTP_COOKIE' => '_xp_debug_=1; _x_debug_=1; PHPSESSID=8tmnt6sdfn5sthmb921a7rigd5',
		  'HTTP_CONNECTION' => 'keep-alive',
		  'HTTP_CACHE_CONTROL' => 'max-age=0',
		  'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
		  'CONTENT_LENGTH' => '1042',
		  'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
		  'LD_LIBRARY_PATH' => '/opt/lampp/lib:/opt/lampp/lib',
		  'SERVER_SIGNATURE' => '',
		  'SERVER_SOFTWARE' => 'Apache/2.4.10 (Unix) OpenSSL/1.0.1i PHP/5.5.15 mod_perl/2.0.8-dev Perl/v5.16.3',
		  'SERVER_NAME' => 'test.my.local',
		  'SERVER_ADDR' => '127.0.0.1',	//local ip
		  'SERVER_PORT' => '80',
		  'REMOTE_ADDR' => '127.0.0.1', //from local,
		  'DOCUMENT_ROOT' => '/var/www/local/xs/MyProject',
		  'REQUEST_SCHEME' => 'http',
		  'CONTEXT_PREFIX' => '',
		  'CONTEXT_DOCUMENT_ROOT' => '/var/www/local/xs/MyProject/public',
		  'SERVER_ADMIN' => 'stone256#hotmail.com',
		  'SCRIPT_FILENAME' => '/var/www/local/xs/MyProject/public/index.php',
		  'REMOTE_PORT' => '4768',
		  'REMOTE_USER' => 'abce1234',
		  'AUTH_TYPE' => 'Basic',
		  'GATEWAY_INTERFACE' => 'CGI/1.1',
		  'SERVER_PROTOCOL' => 'HTTP/1.1',
		  'REQUEST_METHOD' => 'POST',
		  'QUERY_STRING' => '',
		  'SCRIPT_NAME' => '/index.php',
		  'PHP_SELF' => '/index.php',
		  'PHP_AUTH_USER' => 'abcd1234',
		  'PHP_AUTH_PW' => 'abcd1234',
		  'X2CLI_CALL'=>true,
		);
//		'X2CLI_CALL'=>true,

define('__X_DEBUG', true);
