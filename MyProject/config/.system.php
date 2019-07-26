<?php
/**
 * @author 	 peter wang<stone256@hotmail.com>
 * @copyright MIT;
 * system config
 * @// NOTE:  DO NOT change this unless you know what you're doing	
 *
 */
error_reporting(E_ERROR);
ini_set('display_errors', 1);

// DIRECTORY_SEPARATOR
define('DS', '/');	// for linux only

//site root
define('_X_ROOT', preg_replace('/\/public$/', '', _X_INDEX));
define('_X_OFFSET', STR_REPLACE(realpath($_SERVER['DOCUMENT_ROOT']), '', _X_INDEX));
define('_X_SYSTEM', _X_ROOT . '/.system');
define('_X_CONFIG', _X_ROOT . '/config');
define('_X_MODEL_ENABLED', _X_CONFIG . '/enabled');
define('_X_MODEL_OVERWRITE', _X_CONFIG . '/overwrite');

define('_X_MODULE', _X_ROOT . '/module');
define('_X_PACKAGE', _X_ROOT . '/_package');
define('_X_PACKAGE_LIB', _X_PACKAGE . '/_lib');
define('_X_PACKAGE_VENDOR', _X_PACKAGE . '/_vendor');
define('_X_DATA', _X_ROOT . '/data');
define('_X_SYSTEM_DATA', _X_DATA . '/system');
define('_X_CACHE', _X_DATA . '/cache');
define('_X_HISTORY' , _X_DATA . '/history' );
define('_X_BATCH' , _X_DATA . '/batch' );
define('_X_TMP' , _X_DATA . '/tmp' );
define('_X_LOG' , _X_DATA . '/log' );
define('_X_LOG_MYSQL' , _X_LOG. '/mysql' );
define('_X_VAR' , _X_DATA.'/var' );
define('_X_POOL' , _X_DATA.'/pool' );
// session if need
define('_X_SESSION_PATH', _X_DATA . '/session');
define('_X_PUBLIC', _X_ROOT . '/public');
define('_X_MEDIA', _X_PUBLIC . '/media');
define('_X_UPLOAD', _X_MEDIA . '/upload');
define('_X_LAYOUT', _X_ROOT . '/layout');


//root of the URL,  e.g. service.page.com.au:85/myclient/project3
// * http:// or https://
define('_X_URL_P', $_SERVER['HTTPS'] ? 'https://' : 'http://');
define('_X_URI', $_SERVER['HTTP_HOST'] . _X_OFFSET);
define('_X_URL', _X_URL_P._X_URI);
define('_X_URL_REQUEST', _X_URL_P._X_URI.$_SERVER['REQUEST_URI']);


define('_X_URL_CSS', _X_URL . '/css');
define('_X_URL_JS', _X_URL . '/js');
define('_X_URL_MEDIA', _X_URL . '/media');
define('_X_URL_IMAGE', _X_URL_MEDIA . '/image');
define('_X_URL_VIDEO', _X_URL_MEDIA . '/video');
define('_X_URL_UPLOAD', _X_URL_MEDIA . '/upload');
