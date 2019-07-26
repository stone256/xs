<?php
/**
 * @author  peter<xpw365@gmail.com>
 *@todo   class to cache certain infomation with time limited or not!
 *
 * @example : http://dev.syntonic.com.au/clients/eplanner/example/cache.php
 * 	..
 * 	..
 * 	if(!($con = cache::get('country_info_'.'au')))
 * 	{
 * 		..
 * 		$db->get(country('au');
 * 		..
 *
 * 		$con = ..;
 * 		cache::put( 'country_info_'.'au', $con);
 *  * 	}
 *
 */
class xpCacheFile {
	/**
	 * cache directory
	 * @var string $dir :	default :  "cache/"  -- related to current script directory.
	 */
	var $path = 'cache/';
	function __construct($path = null) {
		if ($path) $this->path = $path;
	}
	/**
	 * set cache directory
	 * @param string $dir	:	directory
	 */
	function path($path = null) {
		if (!$path) return $this->path;
		if ($path{strlen($path) - 1} != '/') $path.= '/';
		$this->path = $path;
	}
	/**
	 * get data from cache
	 *
	 * @param string $hash		: cache id
	 * @param int $ttl		: time to live in seconds : default 0 : live for ever, never expire.
	 * @return string		: data or  false
	 */
	function get($key, $ttl = 0) {
		$fn = $this->name($key);
		if (!is_file($fn) || (int)$ttl && time() - filectime($fn) > $ttl) return false;
		return unserialize(file_get_contents($fn));
	}
	/**
	 * save new cache data
	 *
	 * @param string $hash	: cache id
	 * @param mix $data	: cache data
	 */
	function put($key, $data) {
		$fn = $this->name($key);
		file_put_contents($fn, serialize($data), LOCK_EX);
	}
	/**
	 * cache id to file name
	 * only put here for in case of different file system needs
	 * @param string $hash
	 * @return string : cache file name
	 */
	function name($key) {
		$hash = $this->hash('page', $key);
		return ($this->path) . "c_$hash.txt";
	}
	/**
	 * generate hash / cache id
	 *
	 * @param  string $prefix	: name prefix
	 * @param  mix $seed	: seed info
	 */
	function hash($prefix, $seed) {
		return $prefix . "_" . md5(serialize($seed));
	}
}
