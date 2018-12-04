<?php
use Philo\Blade\Blade; //from https://packagist.org/packages/philo/laravel-blade
//check /package/_vendor/composer.json
//"philo/laravel-blade": "3.*"
class examples_blade {
	/**
	 * setting default path
	 *
	 * @param string  $vPath	//view root
	 * @param string $cPath		//cache
	 */
	function __construct($vPath = null, $cPath = null) {
		$this->viewPath = $vPath ? $vPath : _X_LAYOUT;
		$this->cachePath = $cPath ? $cPath : _X_CACHE;
	}
	/**
	 * render a view
	 *
	 * @param string $viewName	: view name 'bar' =>bar.blade.php'
	 * @param string $viewPath	: views' folder
	 */
	function run($viewName, $viewPath = null, $data=array()) {
		$blade = new Blade($viewPath ? $viewPath : $this->viewPath, $this->cachePath);
		echo $blade->view()->make($viewName,$data)->render();
	}
}
