<?php
/**
 * system x  class loader
 *
 * @param string$classname
 */

// get enabled modules (global $modules)
foreach(xpFile::file_in_dir(_X_MODEL_ENABLED, array('level'=>5, 'path'=>true)) as $k=>$v)   include $v;

// get overwrite model (global $overwrite)
foreach(xpFile::file_in_dir(_X_MODEL_OVERWRITE, array('level'=>5, 'path'=>true)) as $k=>$v)   include $v;


/**
 * system x class loader
 *
 * @param string$classname
 */
function x_class_autoload($classname){
	
	global $modules;
	static $_module_path;
	if( ! $_module_path) $_module_path = array_combine($modules, $modules);
	$_name = $classname;
	//check class path
	$_enabled = false;
	while(strpos($_name, '_')){
		$_name =preg_replace('/^([^\_].*?)\_/', "$1/", $_name);
		if($_module_path['/'.dirname($_name)]) $_enabled = true;	//all thing under enabled module folder are allowed.
		$_file = _X_MODULE.'/'.$_name.'.php';
		//check class file
		if(file_exists($_file) && $_enabled){
			return require_once($_file);
		}
		
	}
	//check is enabled
	return;
}

spl_autoload_register('x_class_autoload',0,1);
