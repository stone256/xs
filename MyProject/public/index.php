<?php

/**
 * X enter file
 * @author peter wang <stone256@hotmail.com>
 * @copyright GPL
 * @version 2.1
 *
 */
//fix issue $_SERVER['REQUEST_TIME.. with microtime
define('_X_START_TIME', microtime(1));
//my root
define('_X_INDEX', __DIR__);

//load framework
require_once(__DIR__.'/../.system/app.php');


//init framework
$app= new app();


$html = $app->run();
//
echo $html;
