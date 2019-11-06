<?php
/**
 * system x default controller
 *
 */
class _system_defaultController{
	
	function __construct($arr){
		$this->query = $arr;
	}
	
	function _404Action(){
		return array(
			'view'=>'/.system/view/default/_404.phtml', 
			'data'=>$this->query['query']
			) ;
	}
	function _500Action(){
		return array(
			'view'=>'/.system/view/default/_505.phtml', 
			'data'=>$this->query['query']
			) ;
		
	}
	
}

