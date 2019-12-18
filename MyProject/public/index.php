<?php
/**
 * X enter file
 * @author peter wang <stone256@hotmail.com>
 * @copyright GPL
 * @version 2.1.0.3
 *
 */

 /** 
 * use flow to get your config/x2cli.php value!
 * /
 echo "<pre>";
 var_export($_SERVER);
 exit();
 /****/

 //fix issue $_SERVER['REQUEST_TIME.. with microtime
define('_X_START_TIME', microtime(1));

//my root
define('_X_INDEX', __DIR__);

//load framework
require_once(__DIR__ . '/../_system/app.php');

//init framework
$app= new app();

//in standard form, app::run() will not make output, but return html string.
//but it can not only do output but also can use die(), exit() or exception to interrupt flow!     
$html = $app->run();

//final output 
echo $html;
