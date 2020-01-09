<?php


define ('_DO_NOT_INCLUDE_MENU', true);
class sitemin_installer_installController extends _system_defaultController{


        function runAction(){
                $q = $_REQUEST;

                sleep(1);
                switch($q['step']){
                        case 'file permission':
                                $r = _factory('sitemin_installer_install')->test_permission($q);
                                die(json_encode(['status'=>$r['failed']?'failed':'success', 'data'=>$r]));
                        break;
                        case 'db connection':
                                $r = _factory('sitemin_installer_install')->test_connection($q);
                                die(json_encode(['status'=>$r?'failed':'success', 'data'=>$r]));
                        break;
                        case "install":
                                $r = _factory('sitemin_installer_install')->install($q);
                                die(json_encode(['status'=>$r?'failed':'success', 'data'=>$r]));
                        break;
                }
                $rs['tpl'] = '_install.phtml';
		$rs['TITLE'] = 'SITEMIN installation';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
        }

}
