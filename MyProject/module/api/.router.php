<?php
/**
 * this router info used for none standard pathway other than normal controller located
 * rule of router path
 * 	1> standard router map to /modules. e.g. /x/a/b  => will resolve to /modules/[x]/[a]Controller.php :: [b]Action
 *	2> customized routers,
 * 		 * 		'/router'=>'topview/ycon@zact' will map y to modules/[topview]/[ycon]Controller::[zact]Action()
 * 	 3> custom router always overwrites defaultone
 */
$routers = array(
/** api gateway/webservice first contact point **/
'/api' => '/api/_api@dispatch',
'/api/login' => '/api/_api@login',



/** list apis **/
'/api/list'=>'/api/api@list',
'/api/search'=>'/api/api@search',



/** api users **/
'/api/user'=>'/api/user@index',
'/api/user/password' => '/api/user@password',

'/api/user/edit' => '/api/user@edit',
'/api/user/idcheck' => '/api/user@idcheck',
'/api/user/search' => '/api/user@search',

'/api/user/status_change' => '/api/user@status_change',


/** api acl **/
'/api/acl' => '/api/acl@index',
'/api/acl/edit' => '/api/acl@edit',



);
