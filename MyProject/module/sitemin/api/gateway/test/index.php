<?php

/**
 * test class
 *
 */
class sitemin_api_gateway_test_index {

	/**
	 * testing function return "ssss"
	 */
	function a(){
		echo "ssss";
	}

	/**
	 * testing function return php array
	 *
	 */
	function v(){
		var_export(array('a'=>5, 'b'=>6, 'c'=>array(1,5,8)));
	}

	/**
	 * return json(array) string
	 */
	function zxzx(){
		echo json_encode(array('a'=>5, 'b'=>6, 'c'=>array(1,5,8)));
	}
}
