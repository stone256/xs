<?php
/**
 * @name 	: local config
 * @author 	: peter<stone256@hotmail.com>
 *
 *  this file will be included in cli calls.
 *  	setup according to you needs 
 */


$apache_data=array (
	'REDIRECT_STATUS' => '200',
	'HTTP_HOST' => 'xs.local',
	'HTTP_CONNECTION' => 'keep-alive',
	'HTTP_CACHE_CONTROL' => 'max-age=0',
	'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
	'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36',
	'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
	'HTTP_REFERER' => 'http://xs.local/?in=/0-quick-start/3-run.html',
	'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
	'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
	'PATH' => 'C:\\Program Files (x86)\\Intel\\Intel(R) Management Engine Components\\iCLS\\;C:\\Program Files\\Intel\\Intel(R) Management Engine Components\\iCLS\\;C:\\Windows\\system32;C:\\Windows;C:\\Windows\\System32\\Wbem;C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\;C:\\Windows\\System32\\OpenSSH\\;C:\\Program Files (x86)\\Intel\\Intel(R) Management Engine Components\\DAL;C:\\Program Files\\Intel\\Intel(R) Management Engine Components\\DAL;C:\\Program Files (x86)\\Intel\\Intel(R) Management Engine Components\\IPT;C:\\Program Files\\Intel\\Intel(R) Management Engine Components\\IPT;C:\\Program Files\\Intel\\WiFi\\bin\\;C:\\Program Files\\Common Files\\Intel\\WirelessCommon\\;C:\\Users\\pwang.HFM\\WinSCPInstall\\;C:\\Users\\pwang.HFM\\GitInstall\\cmd;C:\\Program Files\\Git\\cmd;C:\\Windows\\system32\\config\\systemprofile\\AppData\\Local\\Microsoft\\WindowsApps',
	'SystemRoot' => 'C:\\Windows',
	'COMSPEC' => 'C:\\Windows\\system32\\cmd.exe',
	'PATHEXT' => '.COM;.EXE;.BAT;.CMD;.VBS;.VBE;.JS;.JSE;.WSF;.WSH;.MSC',
	'WINDIR' => 'C:\\Windows',
	'SERVER_SIGNATURE' => 'Apache/2.4.37 (Win32) PHP/7.2.14 Server at xs.local Port 80',
	'SERVER_SOFTWARE' => 'Apache/2.4.37 (Win32) PHP/7.2.14',
	'SERVER_NAME' => 'xs.local',
	'SERVER_ADDR' => '127.0.0.1',
	'SERVER_PORT' => '80',
	'REMOTE_ADDR' => '127.0.0.1',
	'DOCUMENT_ROOT' => 'C:/wamp/www/xs/MyProject/public',
	'REQUEST_SCHEME' => 'http',
	'CONTEXT_PREFIX' => '',
	'CONTEXT_DOCUMENT_ROOT' => 'C:/wamp/www/xs/MyProject/public',
	'SERVER_ADMIN' => 'wampserver@wampserver.invalid',
	'SCRIPT_FILENAME' => 'C:/wamp/www/xs/MyProject/public/index.php',
	'REMOTE_PORT' => '59692',
	'REDIRECT_URL' => '/',
	'REDIRECT_QUERY_STRING' => 'in=/1-structure-and-flow/index.html',
	'GATEWAY_INTERFACE' => 'CGI/1.1',
	'SERVER_PROTOCOL' => 'HTTP/1.1',
	'REQUEST_METHOD' => 'GET',
	'QUERY_STRING' => 'in=/1-structure-and-flow/index.html',
	'REQUEST_URI' => '/?in=/1-structure-and-flow/index.html',
	'SCRIPT_NAME' => '/index.php',
	'PHP_SELF' => '/index.php',
	'REQUEST_TIME_FLOAT' => 1576707683.749,
	'REQUEST_TIME' => 1576707683,

	'PHP_AUTH_USER' => 'abcd1234',
	'PHP_AUTH_PW' => 'abcd1234',
	'X2CLI_CALL'=>true,
);

define('__X_DEBUG', true);
