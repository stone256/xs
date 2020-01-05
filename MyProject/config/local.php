<?php
/**
 * @name 	: local config
 * @author 	: peter<stone256@hotmail.com>
 *
 *  this is the place you put all your settings concern the server locally (DEV TESTING .. LIVE).
 */
define('__X_SERVER__', 'DEV');

//do not turn this on before you first run
define('_X_SITEMIN_LOG', false);

//if you use xpPdo class, set true will log query to _data/log/mysql/xxxxx
define('_XP_MYSQL_LOG', false);



$config['DATABASE']['a'] = array( //for testing database
		'host'		=> 'localhost',
		'database'	=> '----test----',
		'user'		=> '----test----',
		'password'	=> '----test----',
);

$cfg = ['db' => $config['DATABASE']['a']];

//change with care
define(_X_SERVER_KEY, 'mSpPzv8GyiJM9yb84YxMImlFmxoUGKmf4rDSFgsfGsdfGdsfesgvXB+UCrM5sTYZ26DSl5ADx39aErqzCa');
define(_API_SALT, 'aSDfaW34Aw3er@Q#QRe3FQ#4rF0V1bYXBzc5TC9aErzCqRfQWRw432qrASrwerqwerq23r23afwE');

ini_set('memory_limit', '256M');

//google bot check
$config['google']['bot check']['api'] = 'https://www.google.com/recaptcha/api/siteverify';
$config['google']['bot check']['key'] = '6LdVH2EUAAAAAD_CVXDvOmRZ3IQvM60XkBy_Kke_';
$config['google']['bot check']['secret'] = '6LdVH2EUAAAAAOuilzwTZko59-xxMP5s30WBO_Mz';
define( '_X_GCAPTCHA', false);	//set to true when you got google captche key (v2.invisible)


//set cookie _x_captcha to this value, will skip captcha bot detection
$config['login']['captcha']['key'] = '7feda8c64c2ea4246c58472818d41c18cb28fe8c';

//for ie P3P
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

