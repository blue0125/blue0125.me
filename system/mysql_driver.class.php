<?php

class mysql_driver 
{

	public $link = FALSE;
	public $hostname = 'localhost';
	public $port = 3306;
	public $username = 'root';
	public $password = '0125';
	public $pconnect = FALSE;
	public $db_showBug = FALSE;
	public $dbprefix = '';
	public $database = 'blue0125.me';
	public $char_set = 'utf8';
	public $dbcollat = 'utf8_general_ci';

	public $use_set_names;

	public $delete_hack = TRUE;

	//var $dbdriver   = 'mysql';
	//var $swap_pre   = '';
	//var $result_id    = FALSE;
	//var $benchmark    = 0;
	//var $query_count  = 0;
	//var $bind_marker  = '?';
	//var $save_queries = TRUE;
	//var $queries    = array();
	//var $query_times  = array();
	//var $data_cache   = array();
	//var $trans_enabled  = TRUE;
	//var $trans_strict = TRUE;
	//var $_trans_depth = 0;
	//var $_trans_status  = TRUE; // Used with transactions to determine if a rollback should occur
	//var $cache_on   = FALSE;
	//var $cachedir   = '';
	//var $cache_autodel  = FALSE;
	//var $CACHE; // The cache class object

	public function __construct() 
	{
		$this->initialize();
	}

	/*
	public function __construct($params) {
		if (is_array($params)) {
			foreach ($params as $key=>$val) {
				$this->$key = $val;
			}
		}

		$this->initialize();
	}
	 */

	//初始化
	public function initialize() 
	{
		if (is_resource($this->link) || is_object($this->link)) {
			return TRUE;
		}

		$this->link = $this->pconnect ? $this->db_pconnect() : $this->db_connect();

		if ( ! $this->link) {
			$this->db_showBug();
			//log_message($this->db_showBug);
			//@TODO 错误页面
		}

		if ($this->database != '') {
			if ( ! $this->db_select()) {
				$this->db_showBug();
				//log_message('error', 'Unable to select database: '.$this->database);  
				return FALSE;
			} else {
				if ( ! $this->db_set_charset($this->char_set, $this->dbcollat)) {
					return FALSE;
				}
			}
		}

		return TRUE;
	}

	//收集错误
	public function db_showBug($message='') 
	{
		return $message ? $message : $this->db_showBug = mysql_error();
	}

	//数据库连接
	public function db_connect() 
	{
		if ($this->port != '') {
			$server = $this->hostname.':'.$this->port;
		} else {
			$server = $this->hostname;
		}

		return @mysql_connect($server, $this->username, $this->password, TRUE);
	}

	//持久数据库连接
	public function db_pconnect() 
	{
		if ($this->port != '') {
			$server = $this->hostname.':'.$this->port;
		} else {
			$server = $this->hostname;
		}

		return @mysql_pconnect($server, $this->username, $this->password, TRUE);
	}

	//数据库选择
	function db_select() 
	{
		return mysql_select_db($this->database, $this->link);
	}

	//设置字符集
	function db_set_charset($charset, $collation) 
	{
		if ( ! isset($this->use_set_names)) {
			// mysql_set_charset() requires PHP >= 5.2.3 and MySQL >= 5.0.7, use SET NAMES as fallback
			$this->use_set_names = (version_compare(PHP_VERSION, '5.2.3', '>=') && version_compare(mysql_get_server_info(), '5.0.7', '>=')) ? FALSE : TRUE;
		}

		$version = mysql_get_server_info($this->link);
		if ($version > '5.0.1') {
			mysql_query("SET sql_mode=''", $this->link);
		}

		if ($this->use_set_names === TRUE) {
			return @mysql_query("SET NAMES '".$this->escape_str($charset)."' COLLATE '".$this->escape_str($collation)."'", $this->link);
		} else {
			return @mysql_set_charset($charset, $this->link);
		}
	}

	function escape_str($str, $like = FALSE) 
	{
		if (is_array($str)) {
			foreach ($str as $key => $val) {
				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}

		if (function_exists('mysql_real_escape_string') AND is_resource($this->link)) {
			$str = mysql_real_escape_string($str, $this->link);
		} else {
			$str = addslashes($str);
		}

		// escape LIKE condition wildcards
		if ($like === TRUE) {
			$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
		}

		return $str;
	}

	function _execute($sql) 
	{
		$sql = $this->_prep_query($sql);
		return @mysql_query($sql, $this->link);
	}

	function _prep_query($sql) 
	{
		// "DELETE FROM TABLE" returns 0 affected rows This hack modifies
		// the query so that it returns the number of affected rows
		if ($this->delete_hack === TRUE)
		{
			if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql))
			{
				$sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
			}
		}

		return $sql;

	}

	public function query($sql) 
	{
		return mysql_query($sql, $this->link) or die(mysql_error());
	}

	public function execute_dql($sql, $type=0, $count=1) 
	{
		$type_array = array(MYSQL_BOTH, MYSQL_ASSOC, MYSQL_NUM);

		if (empty($type_array[$type])) {
			$this->db_showBug('错误参数：type');
		}

		$result = array();
		$res = mysql_query($sql, $this->link) or die ($this->db_showBug());

		if ($res) {
			while ($row = mysql_fetch_array($res, $type_array[$type])) {
				$result['data'][] = $row;
			}
		}

		if ($count == 1)
		{
			$result['count'] = mysql_affected_rows($this->link); 
		}

		$this->db_free_result($res);
		$this->db_close();

		return $result;
	}

	public function execute_dml($sql, $type='INSERT', $count=1) 
	{
		$type_array = array('INSERT', 'UPDATE', 'DELETE');
		
		if (in_array($type, $type_array)) {
			$this->db_showBug('错误参数：type');
		}
		
		$result = array();
		
		if ($type=='DELETE') {
			$sql = $this->_prep_query($sql);
		}

		$res = mysql_query($sql, $this->link) or die ($this->db_showBug());
		
		if ($res && $type=='INSERT') {
			$result['insert_id'] = $this->insert_id();
		}

		if ($res && ($type == 'DELETE' || $type == 'UPDATE')) {
			$result['affected_rows'] = $this->affected_rows();
		}
		
		$this->db_close();
		
		return $result;
	}

	public function execute_dql_getOne($sql, $type=0, $count=1) 
	{
		$type_array = array(MYSQL_BOTH, MYSQL_ASSOC, MYSQL_NUM);

		if (empty($type_array[$type])) {
			$this->db_showBug('错误参数：type');
		}

		$result = array();
		$res = mysql_query($sql, $this->link) or die ($this->db_showBug());

		if ($res) {
			if ($row = mysql_fetch_array($res, $type_array[$type])) {
				$result = $row;
			}
		}

		if ($count == 1)
		{
			$result['count'] = mysql_affected_rows($this->link); 
		}

		$this->db_free_result($res);
		$this->db_close();

		return $result;
	}

	public function db_free_result($res) 
	{
		mysql_free_result($res);
	}

	public function db_close() 
	{
		mysql_close($this->link);
	}

	public function affected_rows() 
	{
		return mysql_affected_rows($this->link);
	}

	public function page($sql, $pageNow, $pageSize) 
	{
		$result = array();
		
		$sqlCount = preg_replace('/^SELECT (.*?) FROM/i', 'SELECT COUNT(1) as count FROM', $sql);

		$resCount = mysql_query($sqlCount, $this->link) or die ($this->db_showBug());
		
		if ($resCount) 
		{
			if ($rowCount = mysql_fetch_array($resCount)) 
			{
			  $result['count'] = $rowCount['count'];
			}
		}
    $this->db_free_result($resCount);
    
		if ( !empty($result['count'])) 
		{
			if ($result['count']/$pageSize < $pageNow) 
			{
				$pageNow = 1;
			}
		  $sql .= ' LIMIT ' . ($pageNow - 1)*$pageSize . ', ' . $pageSize;
		 
		  $res = mysql_query($sql, $this->link) or die ($this->db_showBug());
		  
			while ($row = mysql_fetch_array($res)) 
			{
			  $result['data'][] = $row;
			} 
		}

		$this->db_free_result($res);
		$this->db_close();
		
		return $result;
	}
	
	function insert_id()
	{
		return @mysql_insert_id($this->link);                                                                                               
	}
	
	
}
