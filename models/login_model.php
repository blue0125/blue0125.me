<?php
//$Id$

class login_model {

	public function __construct() {
		require_once 'system/mysql_driver.class.php';
	}

	public function loginCheck($username='', $password='') {

		$db = new mysql_driver();

		$sql = "SELECT username,password FROM root WHERE username = '$username'";
		$result = $db->execute_dql($sql);

		if ($result) {
			if ($result['data'][0]['password'] == md5($password)) {
				return $result['data'][0]['username'];
			}
		}
	}

}
