<?php
/**
 * @author : peter <xpw365@gmail.com>
 * customer control:
 * 		*all controls have default value!
 * 		'length' of code
 * 		'name' of session
 *
 * *must have session_start
 *
 */
class xpCaptcha {
	static $matrix = array(0 => array('0110', '1001', '1001', '1001', '1001', '1001', '0110'), 1 => array('0010', '0110', '0010', '0010', '0010', '0010', '0010'), 2 => array('0110', '1001', '0001', '0010', '0100', '1000', '1111'), 3 => array('0110', '1001', '0001', '0010', '0001', '1001', '0110'), 4 => array('0010', '0110', '0110', '1010', '1111', '0010', '0010'), 5 => array('1111', '1000', '1110', '0001', '0001', '1001', '0110'), 6 => array('0110', '1001', '1000', '1110', '1001', '1001', '0110'), 7 => array('1111', '0001', '0010', '0010', '0100', '0100', '0100'), 8 => array('0110', '1001', '1001', '0110', '1001', '1001', '0110'), 9 => array('0110', '1001', '1001', '0111', '0001', '1001', '0110'),);
	static $dot = array('x' => 6, 'y' => 4);
	static $codelength = 4;
	static $session_name = 'vcode';
	/**
	 * check returned code
	 *
	 * @param string $code		: returned code
	 * @param int $expaired		: ttl
	 * @param array $container	: session container
	 * @return boolean
	 */
	function check($code, $expaired = 300, $container = false) {
		if (!$container) $container = $_SESSION;
		$sn = $container[self::$session_name];
		if ($sn['time'] + $expaired < time()) return false;
		if ($sn['name'] != $code) return false;		
		return true;
	}
	function set($arr) {
		if ($arr['length']) self::$codelength = min(20, $arr['length']);
		if ($arr['dot']) self::$dot = array('x' => min(20, $arr['dot']['x']), 'y' => min(30, $arr['dot']['y']));
	}
	function generate($arr = null, $show = true, $container = array()) {
		if ($arr) self::set($arr);
		$code = self::simpleRandString();
		
		$container[self::$session_name] = array('name' => $code, 'time' => time());
		$margin = array('x' => self::$dot['x'] * 5, 'y' => self::$dot['y'] * 5);
		$padding = array('x' => ceil(self::$dot['x'] / 1), 'y' => ceil(self::$dot['y'] / 1));
		/**
		 * background
		 */
		$x = (self::$codelength) * 5 * (self::$dot['x'] + $padding['x']) - 5 * $padding['x'] + $margin['x'] * 2;
		$y = 7 * self::$dot['y'] + 6 * $padding['y'] + $margin['y'] * 2;
		$im = imagecreate($x, $y);
		imagerectangle($im, 0, 0, $x, $y, $bdcolor = imagecolorallocate($im, 255 - mt_rand(0, 64), 255 - mt_rand(0, 64), 255 - mt_rand(0, 64)));
		$mx = floor($x / (self::$dot['x'] + $padding['x'])) + 1;
		$my = floor($y / (self::$dot['y'] + $padding['y'])) + 1;
		//adding
		for ($i = 0;$i < 6;$i++) {
			$n = mt_rand(0, $x);
			$m = mt_rand(0, $y);
			imagesetthickness($im, mt_rand(2, 6));
			$color = imagecolorallocatealpha($im, mt_rand(50, 150), mt_rand(50, 150), mt_rand(50, 128), 90);
			imageline($im, $n, $m, mt_rand($n, $x), mt_rand($m, $y), $color);
		}
		for ($k = 0;$k < self::$codelength;$k++) {
			for ($m = 0;$m < 7;$m++) {
				$d = (str_split(self::$matrix[$code{$k}][$m]));
				foreach ($d as $n => $v) {
					if ($v) {
						imagechar($im, mt_rand(2,5), $x0 = $margin['x'] + $k * 5 * (self::$dot['x'] + $padding['x']) + $n * (self::$dot['x'] + $padding['x']) + mt_rand(-50, 50) / 100 * self::$dot['x'], $y0 = $margin['y'] + $m * (self::$dot['y'] + $padding['y']) + mt_rand(-50, 50) / 200 * self::$dot['y'], xpAS::password_generator(1, '&@$=02345689DOCHQPS'), $bdcolor = imagecolorallocate($im, mt_rand(0, 64), mt_rand(0, 64), mt_rand(0, 64)));
						if (mt_rand(0, 1)) imagechar($im, mt_rand(2,5), $x0 = $margin['x'] + $k * 5 * (self::$dot['x'] + $padding['x']) + $n * (self::$dot['x'] + $padding['x']) + mt_rand(-50, 50) / 100 * self::$dot['x'], $y0 = $margin['y'] + $m * (self::$dot['y'] + $padding['y']) + mt_rand(-50, 50) / 200 * self::$dot['y'], xpAS::password_generator(1, '&@$=02345689DOCHQPS'), $bdcolor = imagecolorallocate($im, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255)));
					}
				}
			}
		}
		
		imagestring($im, 3, 10, $y-20, "Click this to refresh !", imagecolorallocate($im, 255, 40, 0));
		
		if ($show) {
			header("Content-type: image/png");
			imagepng($im);
		}
		imagedestroy($im);
		
		$_SESSION[self::$session_name] = $container[self::$session_name];
		return $container[self::$session_name];
	}
	function simpleRandString() {
		mt_srand((double)microtime() * 1000000);
		$newstring = "";
		while (strlen($newstring) < self::$codelength) $newstring.= mt_rand(0, 9);
		return $newstring;
	}
}
