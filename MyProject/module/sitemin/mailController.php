<?php

/**
 * default sitemin controller
 *
 */
class sitemin_mailController extends sitemin_indexController {


	function queueAction(){
        $q = $_REQUEST;
        $_token = defaultHelper::page_hash_get('sitemin,mail,queue');
		switch(true){
			case $q['delete']:
                if($q[_token] != $_token) break;
                _factory('sitemin_model_mail')->delete($q['delete']);
				die('deleted');
            case $q['send']:
                if($q[_token] != $_token) break;
                _factory('sitemin_model_mail')->send($q['send']);
                sleep(2);
                die('sent');
			case $q['search']:
				$rs = _factory('sitemin_model_mail')->search($q['term'], $q['search']);
				$rs = xpAS::get($rs, "rows,*,{$q['search']}");
				die(json_encode($rs));
			default:
		}
            $rs =_factory('sitemin_model_mail')->gets($q);
	        $rs['tpl'] = '_mailqueue.phtml';
	        $rs['TITLE'] = 'SITEMIN MAIL';
            $rs['_token'] = defaultHelper::page_hash_set('sitemin,mail,queue');
	        return array('view'=>'/sitemin/view/index.phtml','data' => array('rs' => $rs));
	}



}
