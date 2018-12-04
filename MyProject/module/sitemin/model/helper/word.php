<?php

class sitemin_helper_model_word {

	static $words;
	static $pfname = 'plural.txt';
	function plural($word, $arr, $func=null){
		if(!self::$words){
			$words = unserialize(file_get_contents(__DIR__ .'/'. self::$pfname));
		}

		$count = is_array($arr) ? count($arr) : $arr;

		if($count > 1){
			$w = $words[$word] ?  $words[$word] : $this->find_plural($word);
		}else{
			$w =  $word;
		}
		if($func) $w = $func($w);

		return $w;

	}
	function find_plural($word){

		$r = xpAS::curlOut('http://tools.dehumanizer.com/plural/index2.php', "texto=".$word);

		$n = new simple_html_dom($r);

		$m = trim($n->find('#main h3 pre', 0)->plaintext);

		self::$words[$word] = $m;

		file_put_contents(__DIR__ .'/'. self::$pfname, serialize(self::$words));

		return $m;
	}

}
