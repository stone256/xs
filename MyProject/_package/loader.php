<?php

/**
 * classical loader
 */
$_p = __DIR__.'/_lib/';
foreach (scandir($_p) as $_n) {
	if($_n == '.') continue;
	if($_n == '..') continue;
	if(file_exists($_p.$_n."/loader.php")) include_once($_p.$_n."/loader.php");
}


/**
 * composer vendor loaders, disabled by default for performance
 */
//use vendor /composer
if(_LOAD_VENDOR === true){
	define('_X_VENDOR_PSR', true);
	include __DIR__.'/_vendor/vendor/autoload.php';
}else{
	define('_X_VENDOR_PSR', false);
}
