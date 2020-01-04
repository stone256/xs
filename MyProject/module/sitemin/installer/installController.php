<?php


define ('_DO_NOT_INCLUDE_MENU', true);
class sitemin_installer_installController extends _system_defaultController{
        var $access_file = [
                _X_INSTALL_FILE0,
                _X_INSTALL_FILE1,
                _X_INSTALL_FILE2,
                _X_INSTALL_FILE3,
        ];

        function runAction(){
                $q = $_REQUEST;

                switch($q['step']){
                        case 'file permission':
                                foreach ($this->access_file as $f) {
                                        $p[ is_writable($f) ? 'ok' : 'failed'][] = $f;
                                }
                                die(json_encode(['status'=>$p['failed']?'failed':'success', 'data'=>$p]));
                                break;
                        case 'databse connection':


                        break;
                }
                $rs['tpl'] = '_install.phtml';
		$rs['TITLE'] = 'SITEMIN installation';
		return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
        }
//check file permission

//ask db credential

//check database connection and the contents



}
//install done, will not start next time.
//to reinstall, please rename this file to installer.php
//   and make sure database is clean.
//rename( __FILE__,  __FILE__ . '.done') ;
