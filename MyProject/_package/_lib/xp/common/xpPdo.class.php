<?php
/**
 * @author  peter wang <xpw365@gmail.com>
 * @version  0.7
 * @package  pxpdo
 * 2015-07-28
 * get result
 *
 * @param string $q
 * @return array of  records
 function test($q, $data=array(), $raw = false){
 	$ct =5000;
 	if(!$raw){
 		$sth = $this->pdo->prepare($q);
 		for($i=0; $i<$ct; $i++){
 			$sth->execute($data);
 			$rs = $sth->fetchAll();
 		}
 		return $rs;
 	}else{
 		for($i=0; $i<$ct; $i++){
 			$rs =  $this->pdo->query($q);
 			foreach ($rs as $row) {
 				$rows[] = $row
 			}
 		}
 		return $rows;
 	}
 }
 $result: * this is test on same query, so statemnet can be perpered once, but  who's the moron use same query for more than 1000 time !!!
 ct	300	1000 	1500	7000,	17000 30000
 false = 1	1 	1	2 	9	8
 true = 	0	0        	1	2  	9	10
 */
/*start class */
class xpPdo {
	static $connections = array();
	var $statement = array();
	/**
	 * multi connection / singleton
	 *
	 * @param array $cfg
	 * @return  xpPdo class object
	 */
	function conn($cfg = null) {
		$c = self::get_default_config($cfg);
		$id = md5(json_encode($c));
		if (!self::$connections[$id]) self::$connections[$id] = new xpPDO($c, $id);
		return self::$connections[$id];
	}
	/**
	 * get cfg in default way
	 *
	 * @param array $cfg
	 * @return array of  database settings
	 */
	function get_default_config($cfg = null) {
		if (!$cfg) global $cfg;
		global $xp_config, $CFG;
		switch (true) {
			case $cfg['db']: $c = $cfg['db']; break;
			case $xp_config['db']: $c = $xp_config['db']; break;
			default: $c = xpAS::object2array($CFG->db); break;
		}
		return $c;
	}
	// Connect to an ODBC database using driver invocation
	//$dsn = 'uri:file:///usr/local/dbconnect';
	//$user = '';
	//$password = '';
	// Just an exemple of an odbc connection string to MSSQL 2005 :
	//$cnx = new PDO("odbc:Driver={SQL Native Client};Server=250.156.0.1;Database=myDataBase; Uid=userName;Pwd=thePassword;");
	// On MS SQL Server there is a convenient way to help troubleshooting database server performance problems is to use the APP attribute, like this:
	//$dsn = 'DRIVER=FreeTDS;SERVERNAME=server1;DATABASE=testdb;APP=My PHP Application;UID=user;';
	// To connect to SQL Server 2005 Express on Windows, do it like this:
	//$pdo = new PDO ('mssql:host=localhost,1433;dbname=[redacted]', '[redacted]', '[redacted]');
	// localhost,  localhost\SQLEXPRESS,  localhost\1433,  localhost:1433
	// will not work on Windows.!!
	//connect to sqlite
	//$pdo = new PDO( 'sqlite::memory:',  null,   null,  array(PDO::ATTR_PERSISTENT => true));
	//sqlite:/opt/databases/mydb.sq3
	//sqlite::memory:
	//sqlite2:/opt/databases/mydb.sq2
	//sqlite2::memory:
	function __construct($c, $id) {
		$params['cfg'] = $c;
		$params['id'] = $id ? $id : md5(json_encode($c));
		$params['host'] = xpAS::priority_get($c['host'], $c['server'], 'localhost');
		$params['user'] = xpAS::priority_get($c['user'], $c['username']);
		$params['password'] = xpAS::priority_get($c['pwd'], $c['pass'], $c['password']);
		$params['driver'] = xpAS::priority_get($c['driver'], 'mysql'); //mssql use dblib
		$params['database'] = xpAS::priority_get($c['db'], $c['database'], $c['dbname']);
		$params['prefix'] = xpAS::priority_get($c['prefix'], '');
		$params['port'] = xpAS::priority_get($c['port'], '');
		$databse_str = ":dbname={$params['database']}";
		$params['dsn'] = xpAS::priority_get($c['dsn'], "{$params['driver']}{$databse_str};host={$params['host']}" . ($params['port'] ? ":" . $params['port'] : ''));
		$params['options'] = xpAS::priority_get($c['pdo-options'], $c['options'], array(PDO::ATTR_TIMEOUT => 5,PDO::ATTR_ERRMODE =>  PDO::ERRMODE_EXCEPTION));
		$params['log'] = xpAS::merge(array ('path'=>null, 'size'=>4000000), $c['log']); //logging
		//		$params['flat'] = 'json';
		$this->params = $params;
		//make someone else handle catah error
		try{
			$this->pdo = new PDO($params['dsn'], $params['user'], $params['password'],  $params['options']) ;
		}catch (PDOException $e) {
	//echo  'Connection failed: ' . $e->getMessage();
			//convert to general exception for caller to catch
			throw new Exception("xPDO {$params['dsn']} connection failed. ". $e->getMessage());
			return false;
		}
		$this->pdo->exec('SET NAMES utf8');
		$this->pdo->exec('SET CHARACTER_SET_CLIENT=utf8');
		$this->pdo->exec('SET CHARACTER_SET_RESULTS=utf8');
		$this->pdo->exec('SET CHARACTER_SET_CONNECTION=utf8');
	}
	/**
	 * close conection
	 *
	 */
	function disconnect() {
		$this->pdo = null;
		self::$connections[$this->params['id']] = null;
	}
	/**
	 * get/set flat methon
	 * @param string $json : get: false ; "json" , "serialize" , true = json , all other = 1
	 */
	function flat($type = false) {
		if ($type === false) return $this->params['flat'];
		$this->params['flat'] = is_bool($type) ? "json" : (in_array($type, array("json", "serialize")) ? $type : "serialize");
	}
	/**
	 * get/set tables prefix
	 *
	 * @param string $prefix :  if false return current
	 * @return  string
	 */
	function prefix($prefix = false) {
		if ($prefix == false) return $this->params['prefix'];
		return $this->params['prefix'] = $prefix;
	}
	/**
	 * set log flag
	 *
	 * @param  boolean $on
	 * @param string $path
	 * @param int $max_size
	 */
	function log($on = false, $path = null, $max_size = 4000000) { //4mb
		$this->params['log']['path'] = $path ? $path : __DIR__ . '/../../var/logs/mysql';
		mkdir($this->params['log']['path'], 0777, 1);
		$this->params['log']['on'] = $on;
		$this->params['log']['size'] = $max_size;
		return $this;
	}
	/**
	 * save log
	 *
	 * @param string $q
	 */
	function _log($q) {
		$this->params['last'] = $q;
		if ($this->params['log']['path']) {
			$file = $this->params['log']['path'] . '/' . $this->parems["database"] . ".log";
			$log = file_get_contents($file);
			if (strlen($log) > $this->params['log']['size']) $log = substr($log, 0 - ($max));
			$timestamp = date('Y_m_d__H_i_s_u');
			file_put_contents($file, $log . "\n" . $timestamp . ": " . $q);
		}
	}
	/**
	 * create a new database
	 *
	 * @param string $db : name
	 * @return boolen of query
	 */
	function db_create($db) {
		$db = xpAS::preg_get($db, '|[A-Za-z][A-Za-z0-9\_]+|');
		return $this->exec("CREATE DATABASE IF NOT EXISTS {$db}");
	}
	/**
	 * select database
	 *
	 */
	function db_select($db) {
		$db = $db = xpAS::preg_get($db, '|[A-Za-z][A-Za-z0-9\_]+|');
		return $this->exec("use {$db}");
	}

	/**
	 * get list of database
	 *
	 * @return  array  of database names
	 */
	function db_list() {
		return $this->q('show databases');
	}
	/**
	 * get list of table
	 *
	 * @return  array  of table names
	 */
	function table_list() {
		$rs = xpAS::column($this->q('show tables'), 'Tables_in_' . $this->params['database']);
		return $rs;
	}
	/**
	 * get table info
	 *
	 * @param  string table name
	 * @return   table info
	 */
	function table_info($table) {
		$table = $this->_table_name($table);
		if (!$this->params['table'][$table]['info']) {
			$this->params['table'][$table]['info'] = $this->q('Describe `' . ($table) . '`');
			$this->params['table'][$table]['fields'] = xpAS::get($this->params['table'][$table]['info'], '*,Field');
		}
		return $this->params['table'][$table]['info'];
	}
	function table_key($table) {
		return xpAS::get(xpAS::key($this->table_info($table), 'Key'), 'PRI,Field');
	}
	/**
	 * empty/TRUNCATE  table
	 *
	 * @param string  $table
	 * @return  boolean
	 */
	function table_empty($table) {
		return $this->q('TRUNCATE TABLE `' . $this->_table_name($table) . '`');
	}
	/**
	 * create new table
	 *
	 * @param string  $table		: table name without prefix
	 * @param array $fields			: field eg.
	 *					fields=>array(
	 * 							'cid INT NOT NULL AUTO_INCREMENT',
	 * 							'cname VARCHAR(20) NOT NULL',
	 * 							'cemail VARCHAR(50) NOT NULL',
	 * 							'csubject VARCHAR(30) NOT NULL ',
	 * 							'cmessage TEXT NOT NULL',
	 * 							'PRIMARY KEY(cid)'
	 * 							)
	 * @return  boolean
	 */
	function table_new($table, $fields) {
		$table = $this->_table_name($table);
		$fields = xpAS::escape($fields);
		$fields = implode(', ', "`$fields`");
		$query = " CREATE TABLE IF NOT EXISTS `$table` ( $fields ) ; ";
		return $this->q($query);
	}
	/**
	 * DUPLICATE TABLE
	 *
	 * @param string $old 			: old table name
	 * @param string $new 		: new table name
	 * @param boolean $withdata 	: copy with data
	 */
	function table_duplicate($old, $new, $withdata = false) {
		$old1 = $this->_table_name($old);
		$new1 = $this->_table_name($new);
		$this->q("CREATE TABLE `$new1` LIKE `$old1`");
		if ($withdata) $this->q("INSERT INTO `$new1`  SELECT * FROM `$old1`");
	}
	/**
	 * search field name
	 */
	function table_field_search($name) {
		$tables = $this->table_list();
		foreach ($tables as $kt => $vt) {
			$fields = $this->_table_fields($vt);
			foreach ((array)$fields as $kf => $vf) {
				switch (true) {
					case $kf == $name:
						$b['matched'][$vt][] = $vf;
					break;
					case preg_match('/' . preg_quote($name) . '/', $kf):
						$b['similar'][$vt][] = $vf;
					break;
				}
			}
		}
		return $b;
	}
	/**
	 * returns number of fields
	 *
	 * @return int
	 */
	function table_field_count($table) {
		return count($this->table_fields($table));
	}
	/**
	 * get table field names
	 *
	 * @param  string $table
	 * @return array
	 */
	function table_fields($table) {
		$_table = $this->_table_name($table);
		if (!$this->params['table'][$_table]['fields']) {
			$this->table_info($table);
		}
		return $this->params['table'][$_table]['fields'];
	}
	/**
	 * get table primary key field name
	 *
	 * @param string $table
	 * @return string
	 *   SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'
	 * array (    'Table' => 'xxxbbb',
	 *     'Non_unique' => '0',
	 *     'Key_name' => 'PRIMARY',
	 *     'Seq_in_index' => '1',
	 *     'Column_name' => 'ID',
	 *     'Collation' => 'A',
	 *     'Cardinality' => '3',
	 *     'Sub_part' => NULL,
	 *     'Packed' => NULL
	 * ,    'Null' => '',
	 *     'Index_type' => 'BTREE',
	 *     'Comment' => '',
	 *    'Index_comment' => '',  )
	 */
	function table_primary_key($table) {
		$table = $this->_table_name($table);
		$q = "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY' ";
		$a = $this->q($q);
		return $a[0]['Column_name'];
	}
	/**
	 * count total
	 *
	 * @param table $table
	 * @param string/mix $con
	 * @return int
	 */
	function table_count($table, $cond = null) {
		$table = $this->_table_name($table);
		$cond = $this->_condition($table, $cond);
		$q = "select count(*) as total from `$table` where $cond";
		$rs = $this->q($q);
		return $rs[0]['total'];
	}
	/**
	 * satrt transaction
	 *
	 */
	function Tstart() {
		$this->pdo->beginTransaction();
	}
	/**
	 * commit data
	 *
	 * @return  boolean
	 */
	function Tcommit() {
		try {
			return $this->pdo->commit();
		}catch(Exception $e) {
			return false;
		}
	}
	/**
	 * roll back data
	 *
	 * @return  boolean
	 */
	function Trollback() {
		try {
			$this->pdo->rollBack();
		}catch(Exception $e) {
			return false;
		}
	}
	/**
	 * direct run sql no wrapper
	 *
	 * @param string $sql
	 * @return whatever returns
	 */
	function exec($sql){
		return $this->pdo->exec($sql);
	}
	/**
	 * executes query use perpare or not
	 *
	 * @param string $sql
	 * @return query id
	 */
	function query($q, $data = array(), $option = array()) {
		$this->_log($q);
		$id = md5($q);
		$this->lastquery = $q;
		$this->statement[$id] = $this->statement[$id] ? $this->statement[$id] : $this->pdo->prepare($q, $option);
		$this->statement[$id]->execute($data);
		try{
			$r =  $this->statement[$id]->fetchAll(PDO::FETCH_ASSOC);
		}catch (PDOException $e) {
			$r = false;
		}
		return $r;
	}
	/**
	 * get result
	 * @param string $q
	 * @return array of  records
	 */
	function q($q, $raw = false) {
		$this->_log($q);
		$this->lastquery = $q;
		$rs = $this->pdo->query($q, PDO::FETCH_ASSOC);
		if ($raw) return $rs;
		foreach ($rs as $row) $rows[] = $this->_unflat($row);
		return $rows;
	}
	/**
	 * query and get result for one record
	 *
	 * @param  string $table: table name
	 * @param mic $cond : condition
	 * @param mix  $fields : fileds to get
	 * @param mix  $order : if query return multi-record
	 * @return array (one row)
	 */
	function get($table, $cond = ' 1 ', $fields = '', $order = '') {
		$table = $this->_table_name($table);
		$cond = $this->_condition($table, $cond);
		$fields = $this->_fields($table, $fields);
		$order = $this->_order($table, $order);
		$q = "select $fields from `$table` where $cond  $order limit 1";
		$rs = $this->q($q);
		return $rs[0];
	}
	/**
	 * query and get result for one record
	 *
	 * @param  string $table: table name
	 * @param mic $cond : condition
	 * @param mix  $fields : fileds to get
	 * @param mix  $order : if query return multi-record
	 * @param mix  $limit : limit
	 * @return array (rows)
	 */
	function gets($table, $cond = ' 1 ', $fields = '', $order = '', $limit = '') {
		$table = $this->_table_name($table);
		$cond = $this->_condition($table, $cond);
		$fields = $this->_fields($table, $fields);
		$order = $this->_order($table, $order);
		$limit = $this->_limit($table, $limit);
		$q = "select $fields from `$table` where $cond $order $limit";
		$rs = $this->q($q);
		return $rs;
	}
	/**
	 * delete reocrds
	 *
	 * @param string $table
	 * @param mix $cond
	 * @return int
	 */
	function deletes($table,$cond, $order='', $limit=null){
		$table = $this->_table_name($table);
		$cond = $this->_condition($table,$cond) ;
		$order = $this->_order($table,$order);
		$limit = $this->_limit($table,$limit);
		$q = "delete from `$table` where $cond $order $limit";
		$this->_log($q);
		return $this->pdo->exec($q);
	}
	/**
	 * insert a record
	 *
	 * @param string $table : name
	 * @param array $data	: data array
	 * @return boolean
	 */
	function insert($table, $data) {
		$table = $this->_table_name($table);
		$str = $this->_set_string($table, $data);
		$q = "INSERT INTO `$table` SET $str ";
		$this->query($q);
		return $this->pdo->lastInsertId();
	}
	/**
	* update records
	*
	* @param string $table	: table name
	* @param array $data	: data array
	* @param mix $cond		: conditions
	* @return boolen
	*/
	function updates($table,$data,$cond, $order='', $limit=''){
		$table = $this->_table_name($table);
		$str = $this->_set_string($table,$data);
		$cond = is_array($cond) ? $this->_condition($table,$cond) : $cond;
		$order = $this->_order($table,$order);
		$limit = $this->_limit($table,$limit);
		$q="UPDATE `$table` SET $str WHERE $cond $order $limit";
		return $this->query($q);
	}
	/**
	 * write record back if no exist, create new one;
	 *  return insert or update(first one) primary key
	 */
	function write($table, $data, $cond = null) {
		$key = $this->table_primary_key($table);
		$key = $key ? $key : 'id';
		if (!$cond) { //use table primary key
			$cond = array($key => $data[$key]);
		}
		$table = $this->_table_name($table);
		$cond1 = is_array($cond) ? $this->_condition($table, $cond) : $cond;
		$q = "SELECT * FROM `$table`  WHERE $cond1";
		$rs = $this->query($q);
		if (count($rs) < 1) {
			return $this->insert($table, $data);
		} else {
			$this->updates($table, $data, $cond);
			return $rs[0][$key];
		}
	}
	/**
	 * query and get result for one record
	 *
	 * @param  string $table: table name
	 * @param mix $cond : condition
	 * @param mix  $fields : fileds to get
	 * @param scale  status0 :
	 * @param scale  status1 :
	 * @return array (one row)
	 */
	function toggle($table, $cond = ' 1 ', $field , $s0=0, $s1=1) {
		$table = $this->_table_name($table);
		$cond = $this->_condition($table, $cond);
		$field = $this->_fields($table, $field);
		$s0 = addslashes($s0);
		$s1 = addslashes($s1);
		$q = "UPDATE $table set $field= IF($field='$s0', '$s1', '$s0') where $cond ";
		$rs = $this->query($q);
		return $rs;
	}
	/**
	 * convet normal wild card ? * to sql's _ %
	 * note select * will became select %
	 *
	 * @param string $str
	 * @return string
	 */
	function wildcard_sql($str) {
		return preg_replace('/(?<![\\\])[\?]/m', '_', preg_replace('/(?<![\\\])[\*]/m', '%', $str));
	}


////////////////////////////////////////

////////////////////////////////////////
/////////////pravite method/////////////
////////////////////////////////////////

////////////////////////////////////////

	/**
	* simple locking 	:lock  a row
	*
	* @param $table		:  	table name
	* @param $cond 		:  	search condition of which row can be multiple
	* @param $until		:  	local for how long;	default = 5 minutes / 300 seconds
	* @param $type			: 	locking type array(0=>r/w,1=r-only , 2=>none) 1:readonly 3: no access
	* @param $try			:  	how many time to try;
	* @param $max			:  	max value of random milisecond to next try;
	* @return locker id	or false can not lock a row
	* @note	:	lock_field [type=char(20)] and lock_time [type=big-int]
	*/
	function _lock($table,$cond , $lock = 'lock',  $lock_period=300, $lock_try=256, $lock_step_back_max=25){
		//xxx ,xxx_ttl, xxx_type for locker field
		$table = $this->_table_name($table);
		$lock_id = uniqid();
		$cond = $this->_condition($table, $cond);

		while( $lock_try-- > 0){
			$time = time();
			$t = $time + $lock_period;
			$q = "
				UPDATE `$table`
				SET  `$table`.`{$lock}`='$lock_id', `$table`.`{$lock}_ttl`='$t'
				WHERE $cond AND ( `$table`.`{$lock}`='' OR `$table`.`{$lock}` IS NULL OR `$table`.`{$lock}_ttl` <= '$time' OR `$table`.`{$lock}_ttl` = 0 OR `$table`.`{$lock}_ttl` IS NULL )
				LIMIT 1
			";
			$this->query($q);
			$q = "select * from `$table` where `$table`.`{$lock}`= '$lock_id' limit 1";
			if($rs = $this->q($q))  return $rs[0];// $lock_id;	//I got it ! let's go. return record
			usleep(rand(1,$lock_step_back_max));			//TSMA/CD - Thread Sense Multiple Access/Collision Detect ,step back

		}
		return false;	//can not lock it within $try times
	}
	/**
	 * force to unlock a locked(by '_lock()') row
	 *
	 * @param  $table	: table name
	 * @param  $locker	: locker number
	 */
	function _unlock($table, $lock_id, $lock = 'lock') {
		$this->updates($table, array($lock => 0, "{$lock}_ttl" => 0), array($lock => $lock_id));
	}
	/**
	 * setting condition
	 *	[a, b] = a AND b;   [[a],[b]] =  a OR b
	 * @param  string $table
	 * @param mix $crr
	 * @return  condition string
	 */
	function _condition($table, $crr = false) {
		static $tnt = '/\s+IS\s+NOT\s+NULL\s*|\s+IS\s+NOT\s+|\s+IS\s+NULL\s+|\s+IS\s+|\s+NOT\s+LIKE\s+|\s+LIKE\s+|\s+NOT\s+IN\s+|\s+IN\s+|\s+\<\>\s+|\s+\!\=\s+|\s+\<\=\s+|\s+\>\=\s+|\s+\=\s+|\s+\>\s+|\s+\<\s+/i';
		$table1 = $this->_table_name($table);
		if (!$crr) return ' 1 ';
		if (!is_array($crr)) $crr = array($crr);
		if (is_string(xpAS::get(xpAS::first($crr, 0), 'key')) || !is_array(xpAS::first($crr))) $crr = array($crr);
		//or array
		foreach ($crr as $kor => $vor) {
			$and = array(); //clear
			foreach ($vor as $kand => $vand) {
				switch(true){
					case is_numeric($kand) && !is_array($vand):
						$cs = xpAS::split($vand);
						foreach ($cs AS $kcs => $vcs) {
							$vcs = trim($vcs);
							if (count($t = xpAS::clean(preg_split($tnt, $vcs))) != 2) {
								$and[] = $vcs;
								continue; //donot deal with mal-format
							}
							$t[0] = addslashes('' . $t[0]);
							$t[1] = trim($t[1]);
							$t[2] = xpAS::preg_get($vcs, $tnt);
							$t[1] = xpAS::is_quoted($t[1]) ? xpAS::de_quote($t[1], '()') : $t[1];
							if (!preg_match('/in/i', $t[2])) $t[1] = "'" . addslashes('' . $t[1]) . "'";
							else $t[1] = str_replace("'", '\\' . "'", $t[1]);
							$and[] = "`{$table}`.`{$t[0]}` {$t[2]} " . " {$t[1]} ";
						}
						break;
					case is_numeric($kand) && is_array($vand):
						$and[] = ' ( ' . self::_condition($table, $vand) . ' ) ';
						break;
					case is_array($vand) && count($vand):
						//use in
						$vand = xpAS::escape($vand);
						$and[] = "`$table`.`$kand` in ('" . implode("','", $vand) . "') ";
						break;
					default:
						$kand = addslashes('' . $kand);
						$vand = addslashes('' . $vand);
						$and[] = "`$table`.`$kand` ='$vand' ";
						break;
				}
			}
			$cond_and[] = ' ( ' . implode("\n AND ", $and) . ' ) ';
		}
		$cond = implode("\n OR ", $cond_and);
		return $cond ? $cond : ' 0 ';
	}
	/**
	 * set select fields
	 *
	 * @param string $table
	 * @param mix $arr
	 * @return string
	 */
	function _fields($table, $arr = null) {
		$table = $this->_table_name($table);
		if (!$arr || $arr == '*') return ' * ';
		$fields = $this->table_fields($table);
		$arr = is_array($arr) ? $arr : xpAS::split($arr);
		foreach ($arr as $ka => $va) {
			if ($a = xpAS::preg_get($va, '|\((.*?)\)|', 1)) {
				$b = xpAS::split($a, '/[^A-Za-z0-9_][^A-Za-z0-9_]*/');
				$c = array();
				foreach ($b as $kb => $vb) {
					if (in_array($vb, $fields)) $c[xpAS::padl_length(strlen($vb), 20, '0') . '|' . $vb] = "`$table`.`$vb`";
				}
				krsort($c);
				foreach ($c as $kc => $vc) {
					$old = xpAS::preg_get($kc, '/\d+\|(.*)/', 1);
					$arr[$ka] = str_replace($old, $vc, $arr[$ka]);
				}
			} else {
				if (in_array($va, $fields)) $arr[$ka] = "`$table`.`$va`";
			}
		}
		return implode(', ', $arr);
	}
	/**
	 * make order
	 *
	 * @param string $table
	 * @param mix $arr
	 * @return string
	 */
	function _order($table, $arr = null) {
		if (!$arr) return '';
		if($arr == 'rand()' || $arr == 'RAND()') return "ORDER BY RAND()";
		$fields = $this->table_fields($table);
		$arr = is_array($arr) ? $arr : preg_split('/\s*\,\s*/', $arr);
		foreach ($arr as $k => $f) {
			if (in_array(str_replace('-', '', $f), $fields))
				$brr[] = "`$table`.`" . (preg_match('/\-/', $f) ? str_replace('-', '', $f) . "` DESC " : $f . "`");
		}
		return ' ORDER BY ' . implode(',', $brr) . ' ';
	}
	/**
	 * setup limit
	 *
	 * @param mix $arr
	 * @return string
	 */
	function _limit($table, $arr = null) {
		if (!$arr) return '';
		$arr = is_array($arr) ? $arr : preg_split('/\s*\,\s*/', $arr);
		$arr[0] = (int)$arr[0];
		if ($arr[1] = (int)$arr[1]) return " LIMIT {$arr[0]},{$arr[1]}";
		return " LIMIT {$arr[0]}";
	}
	/**
	 *  set sql set string
	 */
	function _set_string($table, $data, $dm = ', ') {
		if (!is_array($data)) return;
		$data = $this->_field_filter($table, $data);
		$data = $this->_flat($data);
		$pair = array();
		foreach ($data AS $n => $v) {
			$pair[] = '`' . $table . '`.`' . $n . "`='" . addslashes('' . $v) . "' ";
		}
		return implode($dm, $pair);
	}
	/**
	 * create in range like (1,4,55,6)
	 *
	 * @param mix $arr 	: in value
	 * @return  string	: in string
	 */
	function _in($arr) {
		if (is_array($arr)) {
			if (count($arr) < 1) return '()';
			$arr = array_values($arr);
			$arr = xpAS::escape($arr);
			$arr = " ('" . implode("', '", $arr) . "') ";
		} else {
			$arr = " (" . $arr . ") ";
		}
		return $arr;
	}
	/**
	 * flat to database record
	 *
	 * @param  array   $arr : record to be saved to database
	 * @return  $arr
	 */
	function _flat($arr) {
		if (!is_array($arr)) return $arr; //leaf string
		foreach ($arr AS $k => $v) {
			if (is_array($v)) {
				$arr[$k] = $this->flat() == 'json' ? @json_encode($v, true) : @serialize($v);
			}
		}
		return $arr;
	}
	/**
	 * un flat database record
	 *
	 * @param database rec  $arr
	 * @return array
	 */
	function _unflat($arr) {
		if (!is_array($arr)) return $arr; //leaf string
		foreach ($arr AS $k => $v) {
			$c = $this->flat() == 'json' ? @json_decode($v, true) : @unserialize($v);
			$arr[$k] = is_array($c) ? $c : $v;
		}
		return $arr;
	}
	/**
	 * add prefix to table name
	 *
	 * @param unknown_type $table
	 * @return unknown
	 */
	function _table_name($table) {
		$table = xpAS::preg_get($table, '|[A-Za-z][A-Za-z0-9\_]+|');
		if (substr($table, 0, strlen($this->params['prefix'])) == $this->params['prefix']) return $table;
		return $this->params['prefix'] . $table;
	}
	/**
	 * filter out none field field
	 *
	 * @param string $table
	 * @param array $arr
	 */
	function _field_filter($table, $arr) {
		$fields = $this->table_fields($table);
		foreach ($fields as $k => $f) if (isset($arr[$f])) $brr[$f] = $arr[$f];
		return $brr;
	}
}
