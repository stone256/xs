<?php
/**
 * need install tesseract-ocr
 * apt-get install tesseract-ocr
 */
class tesseract{
	

	function __construct(){
		"psm value :
		0 = Orientation and script detection (OSD) only.
		1 = Automatic page segmentation with OSD.
		2 = Automatic page segmentation, but no OSD, or OCR
		3 = Fully automatic page segmentation, but no OSD. (Default)
		4 = Assume a single column of text of variable sizes.
		5 = Assume a single uniform block of vertically aligned text.
		6 = Assume a single uniform block of text.
		7 = Treat the image as a single text line.
		8 = Treat the image as a single word.
		9 = Treat the image as a single word in a circle.
		10 = Treat the image as a single character.";
	}
	
	function ocr($arr){
		$id = uniqid() ;		
		$image = $arr['image'];
		if(preg_match('/^http/', $image)){
			$ext = xpAS::preg_get($image, '/\.(...)$/', 1);
			$ext = $ext ? $ext : "png";
			file_put_contents("/tmp/{$id}.png", file_get_contents($image));
			$image = "/tmp/{$id}.{$ext}";
		}
		
		$lng = $arr['lng'] ? $arr['lng'] : 'eng';
		$psm = $arr['psm'] ? $arr['psm'] : '7';		//single line
		
		shell_exec("tesseract /tmp/{$id}.png /tmp/{$id}  -l $lng -psm $psm");
		
		$text = file_get_contents("/tmp/{$id}.txt");
		unlink("/tmp/{$id}.{$ext}");
		unlink("/tmp/{$id}.txt");
		return $text;
		
	}
}