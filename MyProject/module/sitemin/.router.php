<?php
/**
* this router info used for none standard pathway other than normal controller located
* rule of router path
* 	1> standard router map to /modules. e.g. /x/a/b  => will resolve to /modules/[x]/[a]Controller.php :: [b]Action
*	2> customized routers,
* 		 * 		'/router'=>'topview/ycon@zact' will map y to modules/[topview]/[ycon]Controller::[zact]Action()
* 	 3> custom router always overwrites defaultone
*/

define('_X_SITEMIN', true);

//error_reporting(E_ALL);

$routers=[
 //for test controller
'/sitemin/test' => '/sitemin/login@test',
'/sitemin/helper/vcode'=>'/sitemin/helper@vcode',

'/sitemin/keepalive' => '/sitemin/index@keepalive',

'/sitemin/mail/queue' => '/sitemin/mail@queue',

'/sitemin/requestpassword' => '/sitemin/login@requestpassword',
'/sitemin/resetpassword' => '/sitemin/login@resetpassword',
'/sitemin/login' => '/sitemin/login@login',
'/sitemin/loginajax' => '/sitemin/login@loginajax',
'/sitemin/logout' => '/sitemin/login@logout',
'/sitemin/dashboard' => '/sitemin/login@dashboard',
'/sitemin' => '/sitemin/login@dashboard',


'/sitemin/user/profile' => '/sitemin/user@profile',
'/sitemin/user/message' => '/sitemin/user@message',

'/sitemin/user' => '/sitemin/user@list',
'/sitemin/user/password' => '/sitemin/user@password',
'/sitemin/user/edit' => '/sitemin/user@edit',
'/sitemin/user/active' => '/sitemin/user@active',
'/sitemin/user/suspend' => '/sitemin/user@suspend',
'/sitemin/user/search' => '/sitemin/user@search',


'/sitemin/acl/menutree' => '/sitemin/acl@menutree',
'/sitemin/acl/menutree/item_move_1' => '/sitemin/acl@menutreeitemmove1',
'/sitemin/acl/menutree/item_save' => '/sitemin/acl@menutreeitemsave',
'/sitemin/acl/menutree/item_delete' => '/sitemin/acl@menutreeitemdelete',
'/sitemin/acl/menutree/item_move_2' => '/sitemin/acl@menutreeitemmove2',

'/sitemin/acl/router' => '/sitemin/acl@router',
'/sitemin/acl/router/change' =>'/sitemin/acl@routerchange',
'/sitemin/acl/router/search' =>'/sitemin/acl@routersearch',

'/sitemin/acl/role' => '/sitemin/acl@role',
'/sitemin/acl/role/edit' => '/sitemin/acl@roleedit',
'/sitemin/acl/role/delete' => '/sitemin/acl@roledelete',
'/sitemin/acl/role/search' => '/sitemin/acl@rolesearch',


//sitemin config var
'/sitemin/var' => '/sitemin/var@index',
'/sitemin/status' => '/sitemin/index@status',

'/sitemin/cron' => '/sitemin/cron@index',
//called by system cron or external cron
'/sitemin/cron/call' => '/sitemin/cron@run',
//called by internal cron (via cron@run and sitemin_crontab table!)
'/sitemin/housekeeping/logarchive' => '/sitemin/housekeeping@logarchive',
'/sitemin/housekeeping/backup' => '/sitemin/housekeeping@backup',


/** api gateway/webservice first contact point **/
'/api' => '/sitemin/api/_api@dispatch',
'/api/login' => '/sitemin/api/_api@login',



/** list apis **/
'/api/list'=>'/sitemin/api/api@list',
'/api/search'=>'/sitemin/api/api@search',



/** api users **/
'/api/user'=>'/sitemin/api/user@index',
'/api/user/password' => '/sitemin/api/user@password',

'/api/user/edit' => '/sitemin/api/user@edit',
'/api/user/idcheck' => '/sitemin/api/user@idcheck',
'/api/user/search' => '/sitemin/api/user@search',

'/api/user/status_change' => '/sitemin/api/user@status_change',


/** api acl **/
'/api/acl' => '/sitemin/api/acl@index',
'/api/acl/edit' => '/sitemin/api/acl@edit',


];


//first time will start install screen
include "installer.php";
