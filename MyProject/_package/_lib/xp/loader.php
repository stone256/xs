<?php
error_reporting(E_ERROR);
if (!defined(XP_ROOT)) define(XP_ROOT, __DIR__);
function xp_class_autoload($classname) {
	if (file_exists(XP_ROOT . '/common/' . $classname . '.class.php')) {
		require_once (XP_ROOT . '/common/' . $classname . '.class.php');
		return;
	}
	if (file_exists(XP_ROOT . '/class/' . $classname . '.class.php')) {
		require_once (XP_ROOT . '/class/' . $classname . '.class.php');
		return;
	}
	if (file_exists(XP_ROOT . '/class/' . $classname . '/' . $classname . '.class.php')) {
		require_once (XP_ROOT . '/class/' . $classname . '/' . $classname . '.class.php');
		return;
	}
	//die("class $classname not found");
	
}
spl_autoload_register('xp_class_autoload');
require_once (XP_ROOT . '/common/xpDebug.php');
