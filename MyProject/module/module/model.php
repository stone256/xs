<?php


class module_model {
       function gets() {
	    $enrr = $this->_enabled_modules(); 
	    //all modules
	    foreach(xpFile::file_in_dir(_X_MODULE, array('level'=>10, 'path'=>1)) as $k=>$v){
		if(!preg_match('/\.router\.php$/ims', $v)) continue;
		$path = str_replace(array(_X_MODULE.'/', '/.router.php'), array('', ''), $v);
		$crr[] = array(
			'name'=> $enrr['/'.$path] ? $enrr['/'.$path] : str_replace('/', '-', $path), 
			'path'=> '/'.$path,
			'enabled'=> $enrr['/'.$path] ? 'YES' : 'NO',
		);
	    }
	    return $crr;
       }
       
       function status($q){
	    $enrr = $this->_enabled_modules(); 
	    
	    //remove backpoint
	    $path = str_replace('../', '', $q['p']);

	    $_p = _X_MODULE_ENABLED.$path;
	    $_n = preg_replace('/^\-/', '', str_replace('/', '-', $path)); 

	    if($enrr[$path]){	//enabled module
		//disabled it by remove enabled xx.php
		$this->_disable_module($path);
	    }else{
		//enabled it if the module existed by adding enabled.php to config/enabled
		//create path;
		mkdir($_p, 0777, 1);
		$enable = "<?php"
			. "\n\n\n"
			. '$modules["'.$_n.'"] = "'.$path.'";'
			. "\n\n\n";
		$file = "$_p/$_n.php";
		file_put_contents($file, $enable);
		chmod($file, 0664); 
	    }
       }
       
       function _disable_module($path){
	   //find the file and remove it, also remove folder if is empty
	   foreach(xpFile::file_in_dir(_X_MODULE_ENABLED, array('level'=>15, 'path'=>true)) as $k=>$v) {
	       $modules =null;
	       include $v;
	       if(!in_array($path, $modules)) continue;
	       //remove the file
	       unlink($v);
	       //remove empty folder until _X_MODULE_ENABLED
	       $this->__remove_empty_folder(dirname($v), _X_MODULE_ENABLED);
	   }
       }
       
       function __remove_empty_folder($path, $until=null){
	   if($path == $until) return;	//  done!
	   if(xpFile::file_in_dir($path)) rmdir($path);
	   $this->__remove_empty_folder(dirname($path), $until);
       }
       
       function _enabled_modules(){
	    global $modules;
	   
	    foreach($modules as $k=>$v){
	       $enrr[$v] =  !is_numeric($k) ? $k : xpAS::preg_get($v, '/[^\/]+$/ims');
	    }
	    return $enrr;	   
       }
       
}
