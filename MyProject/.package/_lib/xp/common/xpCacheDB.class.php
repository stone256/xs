<?php
/**
 * logging on file
 * @author 	:xpw365@gmail.com
 * @since 	:2006-03-02
 * @example :
 * @note		: ";
 *
 */
class xpCacheDB {
	static $table = 'xp_cache';
	function void($hash) {
		$db = xpTable::load(self::$table);
		$arr = array('TTL' => 0, 'hash' => $hash);
		$db->write($arr, array('hash' => $hash));
	}
	/**
	 * get data from cache
	 *
	 * @param string $hash		: cache id
	 * @param int $ttl		: time to live in seconds : default 0 : live for ever, never expire.
	 * @return string		: data or  false
	 */
	function get($hash, $ttl = 300) {
		$db = xpTable::load(self::$table);
		if ($ttl) {
			$ttl = "AND TTL <" . (time() - $ttl);
		}
		$q = "SELECT * FROM " . self::$table . " WHERE hash='$hash' $ttl LIMIT 1";
		$rs = $db->q($q);
		return $rs[0]['value'];
	}
	/**
	 * save new cache data
	 *
	 * @param string $hash	: cache id
	 * @param mix $data	: cache data
	 */
	function put($hash, $data) {
		$db = xpTable::load(self::$table);
		$arr = array('hash' => $hash, 'value' => $data, 'TTL' => time());
		$db->write($arr, array('hash' => $hash));
	}
	/**
	 * generate hash / cache id
	 *
	 * @param  string $prefix	: name prefix
	 * @param  mix $seed	: seed info
	 */
	function hash($seed, $prefix = '') {
		return $prefix . "_" . md5(serialize($seed));
	}
}
