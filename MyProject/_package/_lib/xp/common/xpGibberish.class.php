<?php
/**
 * generate some gibberish string to fill the space (when do layout format or testing
 *
 * @author :peter<xpw365@gmail.com>
 * @since   :2008-09-08
 * @example : <a href="http://dev.syntonic.com.au/clients/eplanner/example/gibberish.php">http://dev.syntonic.com.au/clients/eplanner/example/gibberish.php</a>
 *
 */
class xpGibberish {
	/**
	 * chars in use
	 *
	 * @var string	private
	 *
	 */
	static $s = "abcdefghijklmnopqrstuvwxyd";
	/**
	 * punctuation used
	 *
	 * @var string private
	 */
	static $p = ",,....;!???";
	/**
	 * generate string
	 *
	 * @param int $cs		:	rough size of the block
	 * @param boolean $html	:	using html <br/> to \r\n
	 * @return string
	 */
	function generate($cs = 300, $html = true) {
		$a = '<p>';
		$b = '</p>';
		while (strlen($gb) < $cs) {
			if ($html && !rand(0, 20)) $gb.= $a . self::sentence(3, 8, $html) . $b;
			else $gb.= self::sentence(3, 8, $html);
		}
		return $gb;
	}
	private function sentence($n = 3, $m = 8, $html = true) {
		$len = rand($n, $m);
		$st[] = ucwords(self::word(1, 16, $html));
		for ($i = 1;$i < $len;$i++) $st[] = self::word(1, 16, $html);
		return implode(' ', $st) . self::$p{rand(0, 11) } . ' ' . (!rand(0, 6) && $html ? '<br/>' : '');
	}
	private function word($n = 1, $m = 16, $html = true) {
		$len = rand($n, $m);
		if ($html) {
			if (!rand(0, 7)) {
				$a = '<b>';
				$b = '</b>';
			}
		}
		for ($i = 0;$i < $len;$i++) $w.= self::$s{rand(0, 25) };
		return $a . $w . $b;
	}
}
