<?php
/**
 * @author  peter wang <xpw365@gmail.com>
 * @version  2.2
 *
 * 	some extend file function
 *
 *
 * define DS !!(/ or \)
 */
class xpFile {
	function empty_dir($dir) {
		$files = self::file_in_dir($dir);
		if (is_array($files)) foreach ($files as $k => $v) {
			@unlink(self::path($dir) . $v);
		}
	}
	function hex_name($name) {
		$on = self::name($name);
		$ext = self::ext($name);
		$nn = xpAS::str2hex($on);
		$name = preg_replace('/' . preg_quote("$on.$ext") . '$/', "$nn.$ext", $name);
		return $name;
	}
	function str_name($name) {
		$on = self::name($name);
		$ext = self::ext($name);
		$nn = xpAS::hex2str($on);
		$name = preg_replace('/' . preg_quote("$on.$ext") . '$/', "$nn.$ext", $name);
		return $name;
	}
	
	/**
	 * walk throught a array and apply function to each file
	 *
	 * @param string $directory
	 * @param function  $func
	 * @return
	 */	
	function dir_walk($directory, $file_func = null, $dir_func = null, $max_level = 30, $level = 0) {
		if ($level++ > $max_level) return;
		if ($directory && substr($directory, -1, 1) != '/') $directory.= '/';
		if (false === ($d = dir($directory))) return;
		while (false !== ($r = $d->read())) {
			if ($r == '.' || $r == '..') continue;
			if (is_dir($directory . $r)) {
				if ($dir_func) $dir_func($directory . $r);
				self::dir_walk($directory . $r, $file_func, $dir_func, $max_level, $level);
			} else {
				if ($file_func) $file_func($directory . $r);
			}
		}
		$d->close();
	}
	/**
	 * make_path
	 *
	 * @param string $path
	 * @param int/hax/oct $permissiom
	 *
	 */
	function make_path($path, $permissiom = 0775) {
		$ps = explode(DS, $path);
		$r = array_shift($ps);
		while (count($ps)) {
			$r.= DS . array_shift($ps);
			if (!is_dir($r)) mkdir($r, $permissiom);
		}
	}
	/**
	 * get files in a directory
	 *
	 * @param string path
	 * @return  array of files
	 */
	function file_in_dir($path, $option = array()) { //array('detail'=>false, 'level'=>0,'path'=>true, include=>'/\.php$/i', excluede='/\.png$/i')
		if (!is_dir($path)) return false;
		$entry = scandir($path);
		$files = array();
		$option['level'] -- ;
		foreach ($entry as $f) {
			if ($f == '.') continue;
			if ($f == '..') continue;
			if (is_dir($path . '/' . $f)) {
				if ($option['level'] > 0) {
					$fs = self::file_in_dir($path . '/' . $f, $option);
					$files = xpAS::extend($files, $fs);
				}
				continue;
			}
			if ($option['include'] && !preg_match($option['include'], $f)) continue;
			if ($option['exclude'] && preg_match($option['exclude'], $f)) continue;
			$file = $f;
			if ($option['path']) $file = $path . DS . $f;
			if ($option['detail']) $file = array('name' => $file, 'time' => filectime($path . DS . $f), 'size' => filesize($path . DS . $f));
			$files[] = $file;
		}
		return $files;
	}
	function delete($path) {
		return exec("rm -R " . $path);
	}
	/**
	 * get dirs in a directory
	 *
	 * @param string path
	 * @return  array of files
	 */
	function dir_in_dir($path) {
		if (!is_dir($path)) return false;
		$entry = scandir($path);
		foreach ($entry as $f) {
			if ($f == '.') continue;
			if ($f == '..') continue;
			if (!is_dir($path . '/' . $f)) continue;
			$files[] = $f;
		}
		return $files;
	}
	/**
	 * generate folder tree
	 *
	 * @param string $path
	 * @param boolean $detail
	 * @param boolean $flat
	 * @param string $root		:remove path from directory
	 * @return array
	 */
	function dir_tree($path, $detail = false, $flat = false, $root = '') {
		if (!is_dir($path)) return false;
		$entry = scandir($path);
		foreach ($entry as $f) {
			if ($f == '.') continue;
			if ($f == '..') continue;
			if (!is_dir($path . '/' . $f)) {
				$pp = str_replace($root, '', $path);
				if ($detail) {
					if ($flat) $files[$pp . DS . $f] = array('name' => $f, 'time' => filectime($path . DS . $f), 'size' => filesize($path . DS . $f));
					else $files[] = array('name' => $f, 'time' => filectime($path . DS . $f), 'size' => filesize($path . DS . $f));
				} else {
					if ($flat) $files[$pp][$f] = $f;
					else $files[] = $f;
				}
			} else {
				if ($flat) $files = xpAS::merge($files, self::dir_tree($path . DS . $f, $detail, $flat, $root));
				else $files[] = array($f => self::dir_tree($path . DS . $f, $detail, $flat, $root));
			}
		}
		return $files;
	}
	
	/**
	 * get dir tree
	 *
	 * @param string $directory
	 * @param int $max_level
	 * @param int $level : do not set
	 * @return array
	 */
	function dir_array($directory, $max_level = 30, $level = 0) {
		if ($level++ > $max_level) return;
		if ($directory && substr($directory, -1, 1) != '/') $directory.= '/';
		if (false === ($d = dir($directory))) return false;
		$dirs = $files = array();
		while (false !== ($r = $d->read())) {
			if ($r == '.' || $r == '..') continue;
			if (is_dir($directory . $r)) {
				$dirs[] = $r;
				//$dirs[$r] = self::dir_array($directory.$r,$max_level,$level);
				
			} else {
				$files[] = $r;
			}
		}
		natcasesort($files);
		//	$files = array_flip($files);
		$files = array_values($files);
		natcasesort($dirs);
		foreach ($dirs as $r) {
			$rs[$r] = self::dir_array($directory . $r, $max_level, $level);
		}
		$d->close();
		return xpAS::merge($rs, $files);
	}	
	
	function image_out($image, $type = 'jpg') {
		if (is_string($image)) $image = self::image_get($image); //from a file
		self::show($image, $type);
	}
	/**
	 * get file extension
	 *
	 * @param string $n	: path
	 * @return string file extension
	 */
	function ext($n, $pi = null) {
		/**
		 * Array
		 * (
		 *     [dirname] => http:sss.com/as/asdf
		 *     [basename] => sssc.ds
		 *     [extension] => ds
		 *     [filename] => sssc
		 * )
		 */
		$pi = $pi ? $pi : pathinfo($n);
		return $pi['extension'];
	}
	/**
	 * get file name
	 *
	 * @param string $n	: path
	 * @return string filename
	 */
	function name($n, $pi = null) {
		$pi = $pi ? $pi : pathinfo($n);
		return $pi['filename'];
	}
	/**
	 * read line by line untile str appeared
	 *
	 * @param resource  $fp 	: file path/name
	 * @param string $str 		: search needle
	 * @return string contains
	 */
	function read_line_until($fp, $str) {
		$content = '';
		while (strpos(($content.= fgetc($fp)), $str) === false && !feof($fp));
		if (feof($fp)) return false;
		return $content;
	}
	/**
	 *  reading info between <tag>...</tag>
	 *  tag included
	 *
	 * @param resource $fp	: file path/name
	 * @param string $tag		: tag name
	 * @return string contains
	 */
	function read_block_by_tag($fp, $tag) {
		$start = "<$tag>";
		$end = "</$tag>";
		$con = self::read_line_until($fp, $start);
		if (!$con) return false;
		$con = self::read_line_until($fp, $end);
		if (!$con) return false;
		return $start . $con;
	}
	/**
	 * save upload file
	 *
	 * @param string $fieldname 	: form field name: xxx - <input type="file" name="xxx" ..../>
	 * @param string $path		: path to save
	 * @param string  $new_filename : save name  or auto generated if i=-1 or use upload file's banename if is null
	 * @return  strin saved name
	 */
	function upload($fieldname, $path, $new_filename = null) {
		if ($_FILES[$fieldname]['name'] != '') {
			self::make_path($path);
			if (is_null($new_filename)) $new_filename = basename($_FILES[$fieldname]['name']);
			if ($new_filename === - 1) $new_filename = uniqid();
			if (!move_uploaded_file($_FILES[$fieldname]['tmp_name'], $path . DS . $new_filename)) return false;
			@chmod($path . DS . $new_filename, 0777); //
			return $new_filename;
		}
		return false;
	}
	/**
	 * save one upload file  in groups
	 *
	 * @param int 		$i				: index in group
	 * @param string 	$fieldname 		: form field name: xxx - <input type="file" name="xxx[]" ..../>
	 * @param string 	$path			: path to save
	 * @param string  	$new_filename 	: save name  or auto generated if i=-1 or use upload file's banename if is null
	 * @return  	string saved name
	 */
	function uploadi($i, $fieldname, $path, $new_filename = null) {
		if ($_FILES[$fieldname]['name'][$i] != '') {
			self::make_path($path);
			if (is_null($new_filename)) $new_filename = basename($_FILES[$fieldname]['name'][$i]);
			if ($new_filename === - 1) $new_filename = uniqid();
			if (!move_uploaded_file($_FILES[$fieldname]['tmp_name'][$i], $path . DS . $new_filename)) return false;
			@chmod($path . DS . $new_filename, 0777); //
			return $new_filename;
		}
		return false;
	}
	/**
	 * return upload file as  data string
	 *
	 * @param string $fieldname 	: form field name: xxx - <input type="file" name="xxx" ..../>
	 * @param int $i	: 	index of group file if i != null
	 * @return string  file contains
	 */
	function read_upload($fieldname, $i = null) {
		if (is_null($i)) if ($_FILES[$fieldname]['name'] != '') return file_get_contents($_FILES[$fieldname]['tmp_name']);
		else if ($_FILES[$fieldname][$i]['name'] != '') return file_get_contents($_FILES[$fieldname][i]['tmp_name']);
		return false;
	}
	/**
	 * save group upload files
	 *
	 * @param string $fieldname 	: form field name: xxx - <input type="file" name="xxx" ..../>
	 * @param string $path		: path save to
	 * @param array $new_filename : save name  or auto generated if i=-1 or use upload file's banename if is null
	 * @return  array  saved file name
	 */
	function uploads($fieldname, $path, $new_filename = null) {
		if (!$_FILES[$fieldname]['name']) return false;
		self::make_path($path, 0775);
		foreach ($_FILES[$fieldname]['name'] as $k => $v) {
			$nf[$k] = $new_filename[$k];
			if (is_null($new_filename)) $nf[$k] = basename($_FILES[$fieldname]['name'][$i]);
			if (!is_array($new_filename)) $nf[$k] = uniqid();
			if (move_uploaded_file($_FILES[$fieldname]['tmp_name'][$k], $path . DS . $nf[$k])) chmod($path . DS . $new_filename, 0777);
			else $nf[$k] = 'error';
		}
		return $nf;
	}
	/**
	 * test upload image size
	 *
	 * @param string $fieldname 	: form field name: xxx - <input type="file" name="xxx" ..../>
	 * @param int  $width	: width
	 * @param int  $height : height
	 * @return  boolen
	 */
	function test_image_size($fieldname, $width, $height) {
		$size = getimagesize($_FILES[$fieldname]['tmp_name']);
		$img_height = $size[1];
		$img_width = $size[0];
		if ($img_height != $height) return false;
		if ($img_width != $width) return false;
		return true;
	}
	/**
	 * resize a image
	 * type = 'fill,crop,stretch,limit'
	 * autorotate	will rotate canvas to the original image ratio; this used for if you only concern about image file size.
	 * $background : filled background;
	 * @return image
	 */
	function image_resize($image, $nx = null, $ny = null, $type = 'crop', $autorotate = false, $background = '#ffffff', $file_dest = '', $QUALITY = 100) {
		if (is_string($image)) {
			$image_delete = 1;
			$image = self::image_get($image);
		}
		$ox = imagesx($image);
		$oy = imagesy($image);
		switch (true) {
			case !($ox && $oy):
				return;
			case !$nx && !$ny:
				return;
			case !$nx:
				$nx = $ny * $ox / $oy;
			break;
			case !$ny:
				$ny = $nx * $oy / $ox;
			break;
		}
		//auto rotate
		if ($autorotate && (($ox > $oy) xor ($nx > $ny))) list($nx, $ny) = array($ny, $nx);
		if (!is_array($background)) {
			$f = xpAS::preg_get($background, '/[0-9a-fA-F]+/');
			$background = array();
			$background['r'] = hexdec(substr($f, 0, 2));
			$background['g'] = hexdec(substr($f, 2, 2));
			$background['b'] = hexdec(substr($f, 4, 2));
		}
		switch ($type) {
			case 1:
			case 'fill':
				$ratio = min($nx / $ox, $ny / $oy);
				$sx = $sy = 0;
				$ow = $ox;
				$oh = $oy;
				$nw = $ox * $ratio;
				$nh = $oy * $ratio;
				$dx = ($nw - $nx) / 2;
				$dy = ($ny - $nh) / 2;
			break;
			case 2:
			case 'crop':
				$ratio = min($ox / $nx, $oy / $ny);
				$dx = $dy = 0;
				$nw = $nx;
				$nh = $ny;
				$sx = ($ox - $nx * $ratio) / 2;
				$sy = ($oy - $ny * $ratio) / 2;
				$ow = $nx * $ratio;
				$oh = $ny * $ratio;
			break;
			case 3:
			case 'stretch':
				$dx = $dy = $sx = $sy = 0;
				$ow = $ox;
				$oh = $oy;
				$nw = $nx;
				$nh = $ny;
			break;
			case 4:
			case 'limit':
				$r = min($nx / $ox, $ny / $oy);
				$nw = $ox * $r;
				$nh = $oy * $r;
				$ow = $ox;
				$oh = $oy;
				$dx = $dy = $sx = $sy = 0;
				//				die(" $nx, $ny, $ox,$oy");
				//				$sx = max(0,($ox - $nx)/2);
				//				$sy = max(0,($oy - $ny)/2);
				//				$dx = max(0,($nx - $ox)/2);
				//				$dy = max(0,($ny - $oy)/2);
				//				$nw = $ow= $ox < $nx ? $ox : $nx;
				//				$nh = $oh= $oy < $ny ? $oy : $ny;
				
			break;
		}
		$img = imagecreatetruecolor($nw, $nh);
		$bg = imagecolorallocate($img, $background['r'], $background['g'], $background['b']); // create grey background
		imagefill($img, 0, 0, $bg);
		//	echo "	imagecopyresampled($img, $image, (int)$dx, (int)$dy,(int)$sx,(int)$sy, (int)$nw, (int)$nh, (int)$ow, (int)$oh);	";
		//	die();
		imagecopyresampled($img, $image, (int)$dx, (int)$dy, (int)$sx, (int)$sy, (int)$nw, (int)$nh, (int)$ow, (int)$oh);
		if ($file_dest) {
			imagejpeg($img, $file_dest, $QUALITY);
			imagedestroy($img);
			if ($image_delete) imagedestroy($image);
			return true;
		} else {
			if ($image_delete) imagedestroy($image);
			return $img;
		}
	}
	/**
	 * get image from file
	 *
	 * @param string $filename
	 * @return image image
	 */
	function image_get($filename) {
		return imagecreatefromstring(file_get_contents($filename));
	}
	function show($img, $type = 'jpg') {
		switch ($type) {
			default:
			case 'jpg':
				header('Content-Type: image/jpg');
				imagejpeg($img);
			break;
			case 'png':
				header('Content-Type: image/png');
				imagepng($img);
			break;
			case 'gif':
				header('Content-Type: image/gif');
				imagegif($img);
			break;
		}
	}
	function image_put($img, $filename, $type = 'jpg') {
		switch (strtolower($type)) {
			default:
			case 'jpeg':
				$type = 'jpg';
			case 'jpg':
				imagejpeg($img, $filename . '.jpg');
			break;
			case 'png':
				imagepng($img, $filename . '.png');
			break;
			case 'gif':
				imagegif($img, $filename . '.gif');
			break;
		}
	}
	function path($str) {
		return preg_replace('/(?<!:)\/\//', '/', $str . '/');
	}
	function rotate($src, $angle, $dest = null) {
		$im = self::image_get($src);
		$imr = imagerotate($im, $angle, 0);
		if ($dest) imagejpeg($imr, $dest);
		return $imr;
	}
	function captcha($setting = array()) {
		$default = array('codelength' => 5, 'dot' => 8, 'margin' => 3, 'padding' => 6, 'return' => false);
		$setting = xpAS::setting($default, $setting);
		$codelength = $setting['codelength'];
		$dot = $setting['dot'];
		$margin = $setting['margin'];
		$padding = $setting['padding'];
		$code_matrix = array(0 => array('0110', '1001', '1001', '1001', '1001', '1001', '0110'), 1 => array('0010', '0110', '0010', '0010', '0010', '0010', '0010'), 2 => array('0110', '1001', '0001', '0010', '0100', '1000', '1111'), 3 => array('0110', '1001', '0001', '0010', '0001', '1001', '0110'), 4 => array('0010', '0110', '0110', '1010', '1111', '0010', '0010'), 5 => array('1111', '1000', '1110', '0001', '0001', '1001', '0110'), 6 => array('0110', '1001', '1000', '1110', '1001', '1001', '0110'), 7 => array('1111', '0001', '0010', '0010', '0100', '0100', '0100'), 8 => array('0110', '1001', '1001', '0110', '1001', '1001', '0110'), 9 => array('0110', '1001', '1001', '0111', '0001', '1001', '0110'),);
		$code = xpAS::password_generator($codelength, '0123456789'); // self::simpleRandString();
		$saved = array('name' => $code, 'time' => time());
		$tx = $codelength * (4 + $margin) + $margin;
		$ty = 7 + 2 * $margin;
		for ($i = 0;$i < $tx;$i++) {
			$i_index = $i % (4 + $margin);
			$i_code = $code{floor($i / (4 + $margin)) };
			if (0 <= $i_index && $i_index < $margin) continue;
			for ($j = 0;$j < $ty;$j++) {
				if ($j < $margin || $j >= $margin + 7) continue;
				$matrix[$i][$j] = $code_matrix[$i_code][$j - $margin] {
					$i_index - $margin
				};
			}
		}
		$im = imagecreatetruecolor($lx = $tx * ($dot + $padding) + $padding + $dot * 1.2, $ly = $ty * ($dot + $padding) + $padding + $dot * 1.2);
		$r = mt_rand(0, 1) ? mt_rand(0, 64) : 255 - mt_rand(0, 64);
		$g = mt_rand(0, 1) ? mt_rand(0, 64) : 255 - mt_rand(0, 64);
		$b = mt_rand(0, 1) ? mt_rand(0, 64) : 255 - mt_rand(0, 64);
		$bk_no = imagecolorallocate($im, $r, $g, $b);
		$bk_yes = imagecolorallocate($im, 255 - $r, 255 - $g, 255 - $b);
		imagefilledrectangle($im, 0, 0, $lx, $ly, $bk_yes);
		for ($i = 0;$i < $tx;$i++) {
			for ($j = 0;$j < $ty;$j++) {
				if (!$matrix[$i][$j]) imagefilledrectangle($im, $x = $i * ($dot + $padding) + rand(80, 120) / 100 * $dot, $y = $j * ($dot + $padding) + rand(80, 120) / 100 * $dot, $x + $dot, $y + $dot, $bk_no);
			}
		}
		if ($setting['return']) {
			$saved['img'] = $im;
		} else {
			header("Content-type: image/png");
			imagepng($im);
			imagedestroy($im);
		}
		return $saved;
	}
	function captcha1($option = array('length' => 5, 'background' => array('r' => '', 'g' => '', 'b' => ''), 'foreground' => array('r' => '', 'g' => '', 'b' => ''))) {
		$codelangth = $option['length'];
		$str = xpAS::password_generator($codelangth, "34678abcdefhjkmnpqrtuvwxy#@&*?");
		$saved = array('vcode' => $str, 'vtime' => time(),);
		$w = 120;
		$h = 20;
		$x = $codelangth * 31;
		$y = 92;
		$im = imagecreate($x, $y); //size
		$r = $option['brackground']['r'] ? $option['brackground']['r'] : mt_rand(190, 222);
		$g = $option['brackground']['g'] ? $option['brackground']['g'] : mt_rand(180, 220);
		$b = $option['brackground']['b'] ? $option['brackground']['b'] : mt_rand(175, 230);
		$alpha = 0;
		$bg = imagecolorallocatealpha($im, $r, $g, $b, $alpha);
		$arc = array();
		for ($i = 0;$i < $codelangth;$i++) {
			mt_srand((double)microtime() * 1000000);
			$y1 = mt_rand(10, 40);
			$x1 = 10 + 29 * $i;
			$r1 = $option['forekground']['r'] ? $option['forekground']['r'] : mt_rand(15, 155);
			$g1 = $option['forekground']['g'] ? $option['forekground']['g'] : mt_rand(15, 155);
			$b1 = $option['forekground']['b'] ? $option['forekground']['b'] : mt_rand(15, 155);
			$textcolor = imagecolorallocate($im, $r1, $g1, $b1);
			imagechar($im, 5, $x1, $y1, $str{$i}, $textcolor);
			$jm = imagecreate(10, 18);
			$bgj = imagecolorallocatealpha($jm, $r, $g, $b, 0);
			$textcolorj = imagecolorallocate($jm, $r1, $g1, $b1);
			imagechar($jm, 4, 1, 1, $str{$i}, $textcolorj);
			imagecopyresized($im, $jm, $x1, $y1, 0, 0, mt_rand(13, 33), mt_rand(20, 45), 10, 17);
			$s = mt_rand(-10, 10);
			$t = mt_rand(-5, 5);
			imagedashedline($im, $x1 + $s, 0, $x1 - $s, $y, $textcolor);
			$h = mt_rand($codelangth * 5, $codelangth * 30);
			$w = mt_rand($codelangth * 10, $codelangth * 90);
			if ($i % 2) array_push($arc, array(mt_rand(0, 1) ? $x : 0, mt_rand(0, 1) ? $y : 0, $w, $h, 0, 360, $textcolor));
		}
		foreach ($arc as $k => $v) {
			imagearc($im, $v[0], $v[1], $v[2], $v[3], $v[4], $v[5], $v[6]);
		}
		//exit;
		//imageantialias ( $im,true );
		//imagefilter($im, IMG_FILTER_GRAYSCALE); not in lib
		header("Content-type: image/png");
		imagepng($im);
		imagedestroy($im);
		return $saved;
	}
}
