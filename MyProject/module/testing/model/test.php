<?php
class testing_model_test {
    
	function __construct($a, $b) {
	    _dd("a=$a");
	    _dd("b=$b");
	}
    
	function t() {
		echo "<h2>".__FILE__."</h2>";
		echo "<h2>".__CLASS__."</h2>";
	    echo "<h2>end</h2>";
	}
}
