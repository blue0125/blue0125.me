<?php

class mysql_driver {

	public $link = FALSE;
	public $hostname;
	public $port = '';
	public $username;
	public $password;
	public $pconnect = FALSE;
	public $db_debug = FALSE;
	public $dbprefix = '';
	public $database;
	public $char_set = 'utf8';
	public $dbcollat = 'utf8_general_ci';

	public $use_set_names;
	public $delete_hack = FALSE;

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

	public function __construct($params) {
		if (is_array($params)) {
			foreach ($params as $key=>$val) {
				$this->$key = $val;
			}
		}
	}

	//初始化
	public function initialize() {
		if (is_resource($this->link) || is_object($this->link)) {
			return TRUE;
		}

		$this->link = $this->pconnect ? $this->db_pconnect() : $this->db_connect();

		if ( ! $this->link) {
			$this->db_debug();
			log_message($this->db_debug);
			//@TODO错误页面
		}

		if ($this->database != '') {
			if ( ! $this->db_select()) {
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
	public function db_debug() {
		$this->db_debug = mysql_error();
	}

	//数据库连接
	public function db_connect() {
		if ($this->port != '') {
			$server = $this->hostname.':'.$this->port;
		} else {
			$server = $this->hostname;
		}

		return @mysql_connect($server, $this->username, $this->password, TRUE);
	}

	//持久数据库连接
	public function db_pconnect() {
		if ($this->port != '') {
			$server = $this->hostname.':'.$this->port;
		} else {
			$server = $this->hostname;
		}

		return @mysql_pconnect($server, $this->username, $this->password, TRUE);
	}

	//数据库选择
	function db_select() {
		return @mysql_select_db($this->database, $this->link);
	}

	//设置字符集
	function db_set_charset($charset, $collation) {
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

	function escape_str($str, $like = FALSE) {
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

	function _execute($sql) {
		$sql = $this->_prep_query($sql);
		return @mysql_query($sql, $this->link);
	}

	function _prep_query($sql) {
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



}
