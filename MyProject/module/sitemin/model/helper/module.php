<?php


class sitemin_helper_model_module {

	function create($q) {

		$name = trim($q['name']);
		$name = strtolower($q['name']);
		$name = preg_replace('/[^a-zA-Z0-9]+/ims', '_', $q['name']);

		//validate key
		$key = trim($q['key']);
		if($key !== _X_SERVER_KEY){
			$msg[] = "Access Denied (key)";
		}

		//check name validity
		if(!preg_match('/^[a-zA-Z][a-zA-Z\_\s]+$/', $name)){
			$msg[] = "Name Format Error : <b>$name</b>";
		}
		//check enablen modile
		$r = xpFILE::file_in_dir(_X_MODULE_ENABLED);
		if(in_array("{$name}.php", $r)){
			$msg[] = "Enabled Modules Existed (enabling) : <b>$name</b>";
		}
		//check path name
		if(file_exists(_X_MODULE."/{$name}")){
			$msg[] = "Modules Existed (path): <b>$name</b>";
		}
		//check for router
		$routers = _router();
		if($routers["/{$name}"]){
			$msg[] = "Url Existed (router): <b>/{$name}</b>";
		}

		if($msg) return $msg;
		//create module folder
		//$msg[] = shell_exec("mkdir -p "._X_MODULE."/{$name} 2>&1");
		$msg[] = shell_exec("mkdir -p "._X_MODULE."/{$name}/model 2>&1");
		$msg[] = shell_exec("mkdir -p "._X_MODULE."/{$name}/view/index 2>&1");
		//create enabled file : {$name}.php" => $module = "/{$name}"
		$str = '<?php'."\n\n".'$module = "/'.$name.'";'."\n\n";
		file_put_contents(_X_MODULE_ENABLED."/{$name}.php", $str);
		//create .config.php
		$str = '<?php'."\n\n".'$module=array("name"=>"'.$name.'","ver"=>"1.0.0.1",);'."\n\n".'$routers=array("/'.$name.'" => "/'.$name.'/index@test",);' ."\n\n" ;
		file_put_contents(_X_MODULE."/{$name}/.config.php", $str);
		//create .setup.0.0.0.1.php.done
		$str = "<?php\n\n\$sql = <<<EOF\n\nEOF;\n\nxpPdo::conn()->exec(\$sql);\n\n" ;
		file_put_contents(_X_MODULE."/{$name}/.setup.0.0.0.1.php.done", $str);
		//create default controller
		$str = "<?php\n\nclass {$name}_indexController extends _system_defaultController {\n\n\tfunction testAction(){\n\n\$q=\$_REQUEST;\nreturn array('data' => array('rs' => \$q));\n\n\t}\n}\n\n" ;
		file_put_contents(_X_MODULE."/{$name}/indexController.php", $str);
		//create default model
		$str = "<?php\n\nclass {$name}_model_{$name} {\n\n\n }" ;
		file_put_contents(_X_MODULE."/{$name}/model/{$name}.php", $str);
		//create default view
		$str = "<h1>View: {$name}/view/test.phtml</h1>" ;
		file_put_contents(_X_MODULE."/{$name}/view/index/test.phtml", $str);
		$msg[]="<h3>module created '$name'</h3><br><a href='/{$name}' target='_blank'>go there</a>";
		return $msg;
	}
}
