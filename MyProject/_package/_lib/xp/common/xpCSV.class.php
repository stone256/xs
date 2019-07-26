<?php
/**
 * @author 	: peter <xpw365@gmail.com>
 * @date	: 2009 12 26
 *
 * 	this class is to create a standran html form page's flow
 *
 */
class xpCSV {
	/**
	 * get array form cvs file
	 *
	 * @param mix $handle		: opened file handle or file-path
	 * @param boolean $title	: true if first row is column title
	 * @param int $length		: max row length , 0 = unlimited.
	 * @param  string/char 	$delimiter
	 * @param string/char $enclosure
	 * @return unknown
	 */
	function gets($handle, $title = true, $length = 0, $delimiter = ',', $enclosure = '"') {
		if (is_string($handle)) $handle = fopen($handle, 'r');
		while (($rows[] = fgetcsv($handle, $length, $delimiter, $enclosure))) 1;
		if ($title) {
			reset($rows);
			$title = array_shift($rows);
			$title = xpAS::trim($title);
			foreach ($rows as $k => $v) foreach ($title as $kt => $vt) $brr[$k][$vt] = $v[$kt];
			$rows = $brr;
		}
		return $rows;
	}
	/**
	 * output csv
	 *
	 * @param mix $handle		: opened file handle or (string)filepath: save to file or -1: output string or false,start with - (string) download
	 * @param array $rows
	 * @param boolean $title	: use row[0]'s key as column title
	 * @param string/char $delimiter
	 * @param string/char $enclosure
	 * @return string or boolean
	 */
	function puts($handle, $rows, $title = true, $delimiter = ',', $enclosure = '"') {
		$fname = $handle;
		if ($handle == false || (is_string($handle) && $handle{0} == '-')) { //will create string
			if (is_string($handle)) $fname = substr($handle, 1) . '_' . date('Y_m_d__H_i_s') . ".csv";
			else $fname = 'download_' . date('Y_m_d__H_i_s') . ".csv";
			$handle = tmpfile();
			$tmp = 1;
		} else {
			if (!is_resource($handle) && $handle != - 1) {
				$handle = fopen($handle, 'w');
			} else {
				$handle = tmpfile();
				$tmp = - 1;
			}
		}
		if ($title) {
			$title = array_keys($rows[0]);
			array_unshift($rows, $title);
		}
		foreach ($rows as $row) fputcsv($handle, $row);
		if ($tmp == - 1) {
			fseek($handle, 0);
			return fread($handle, 2000000);
		}
		if ($tmp) {
			fseek($handle, 0);
			$csv = fread($handle, 2000000);
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition:  attachment; filename=" . basename($fname));
			echo $csv;
		}
		return true;
	}
}
