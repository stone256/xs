<?php
class testing_model_testa extends testing_model_test{
	
	function __construct($b, $a) {
	    _dd("a=$a");
	    _dd("b=$b");
	}

	function t() {
		echo "<h2>".__FILE__."</h2>";
		echo "<h2>".__CLASS__."</h2>";
	    echo "<h2>end</h2>";
	}
}
