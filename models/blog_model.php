<?php
//$Id$

class blog_model
{
	
	protected $table = 'blogs';
	public $db = '';

	public function __construct() 
	{
		require_once 'system/mysql_driver.class.php';
		
		$this->db = new mysql_driver();
	}

	function get_blog_list($pageNow, $pageSize) 
	{
		$sql = "SELECT bid, title, content, status, updated FROM $this->table ORDER BY bid DESC";
		$result = $this->db->page($sql, $pageNow, $pageSize);

		return $result;
	}
	
	public function insert_blog($data) 
	{
		$sql = "INSERT INTO $this->table (title, content, status, updated) VALUES ('$data[title]', '$data[content]', $data[status], $data[updated])";
		
		$result = $this->db->execute_dml($sql);
		
		return $result;
	}
	
	public function get_blog_by_bid($bid, $field = '*')
	{
		$sql = "SELECT $field FROM $this->table WHERE bid = $bid";

		$result = $this->db->execute_dql_getOne($sql, 1, 0);

		return $result;
	}
	
	public function update_blog_by_bid($bid, $data)
	{
		$data_tmp = array(); 
		$data_str = '';
		if (is_array($data)) {
			foreach ($data as $k=>$v) {
				$data_tmp[] = $k . '=' . '\'' . $v .'\'';
			}
		}
		if (is_array($data_tmp)) {
			$data_str = join(',', $data_tmp);
		}

		$sql = "UPDATE $this->table set $data_str WHERE bid = $bid";

		$db = new mysql_driver();
		$result = $db->execute_dml($sql, 'UPDATE');

		return $result;
	}
	
	public function del_blog($bid)
	{
		$db = new mysql_driver();
		$sql = "DELETE FROM $this->table WHERE bid = $bid";

		$result = $db->execute_dml($sql, 'DELETE');

		return $result;
	}
	
}
