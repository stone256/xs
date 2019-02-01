What is
=======
xs framework is simple and fast php framework with minimum code. 


License: MIT 
=======================
[https://en.wikipedia.org/wiki/MIT_License]


Requirements
============

* PHP >= 5
* Pdo Extension for mysql, only for sitemin and api
* apache2 + rewrite, only for webapp

Installation
============ 
* clone or just download to your project folder

Usage
=====
* remember before your start:
	*under your project folder,
	*	copy "config/general.sample" to "config/general.php"
	*	copy "config/local.sample" to "config/local.php"
	*	copy "config/x2cli.sample" to "config/x2cli.php"
* to enable vendor under .package/:
	*uncommet the line in "config/general.php"
	*	define('_LOAD_VENDOR', true);

* ENTER POINT WEB are handled by "public/index.php"
	*	e.g. http://www.myproject.com
	*		or 
	*		 http://www.mydomain.com/myproject

* CLI are handled by file "x2cli" under the project folder
	*$php x2cli foo/bar id=5\&date=2008-11-11
	*e.g. $php x2cli [ROUTER] [PARAMETERS]

* CONFIG 
	*general: "config/general.php" 
	*local:   "config/local.php"
	*		 "config/x2cki.php"

* EDANLED MODULE
	* "config/enabled/YOURMODULE.php"
	* e.g. "config/enabled/foo.php" 
	    <?php
			$modules[] = "/foo";
		?>

* MODEL OVERWRITE :
	*"config/overwrite/MODEL_2_NEWMODEL.php"
	*e.g. "config/enabled/foo_2_bar.php" 
	    <?php
            $overwrites['foo']= 'bar';
		?>

* LAYOUT: 
	COMMON LAYOUT: "layout/" #this is recommand, not enforced. 

* MODULE: 
	"module/YOURMODULE"	#all module have to be in there!
	e.g. "module/foo"

* VIEW: 
	"module/YOURMODEL/view/[controller]/[method].phtml"
	e.g. "module/foo/view/index/bar.phtml"

* ROUTER MAPPING:
	router file is under your module path, wihch defined when you put in your enabled module
	e.g. $modules[] = "/foo";
		router file is : "foo/.router.php
		<?php
			$routers = array(
						"/foo/bar" => "/foo/index@bar",
						#"FRONT-URI" => "MODULEPATH/CONTROLLERNAME@METHODNAME"
					);
		?>

* CONTROLLER:
		controller is defined in the router file
		e.g. "/foo/bar" => "/foo/index@bar",
			this defined controller is "foo/indexController.php"	
			so "http://www.xxx.com/foo/bar" will call
				the method "bar()" in "indexController.php"

* PACKAGE: 
	1. composed PACKAGE : ".package/_vendor"
	2. just use lazyloader: ".package/_lib/*"

* X-FRAMEWORK required : ".package/xp/*"

* DATA STORAGE: "data/*"

* SYSTEM core ".system/*"

* PUBLIC RESOURCE: "public" 
		MEDIA, JS.. .. ..
		..
		"index.php"	#system file donot touch unless you know what you doing.
		".htaccess"	#system file donot touch unless you know what you doing.	
		"maintenance.html"	#for maintenance model [option]
		"robots.txt"	#robot file [option]
