<?php

session_start();

if (isset($_SESSION['root']) && $_SESSION === TRUE) {
	header('Location: adminManage.php');
	exit();
}

if ($_POST) {
	$username = $_POST['username'];
	$password = $_POST['password'];

	$login = new login_model();

	if ($login->loginCheck($username, $password)) {
		$_SESSION['root'] = TRUE;
		header('Location: admin');
		exit();
	} else {
		header('Location: login/error=1');
		exit();
	}
}

$view = new MyTemplate();
$view->display('login');
