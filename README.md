X-Framework

* note:
	remember copy config/local.sample to config/local.php
		copy config/x2cli.sample to config/x2cli.php
		before you start!! 

	to enable vendor under .package
		uncommet the line in /config/ganarel.php
		define('_LOAD_VENDOR', true);

ENTER POINT
	WEB:
		public/index.php
	
	CLI:
		x2cli 
		($php x2cli id=5\&date=2008-11-11)

CONFIG
	GENERAL:
		config/general.php
	LOCAL:
		config/local.php
		cpnfig/x2cli.php
		
	EDANLED MODULE
		config/enabled/*
		
LAYOUT
	COMMON LAYOUT:
		layout

MODULE
	module/*

VIEW
	module/*/view/[controller]/[method]

	
PACKAGE:	
	THRID PARTY PACKAGE USE PSR LOADER
	.package/_vendor
		
	LIBARAY:	USE LAZY LOADER
	.package/_lib/*
	
	X-FRAMEWORK required : 
	.package/xp/*

DATA STORAGE:
	data/*	
	
SYSTEM FILE	
	.system/*
	

PUBLIC RESOURCE	
	public
	  MEDIA
	  ..
	  ..
	  ..
	
