<?php


class mymodule_indexController extends _system_defaultController {
       function myfunctionAction() {
	       $rs = _factory('mymodule_model_mymodel')->get_data();
	       return array('data' => array('rs' => $rs));
       }
}
