<?php

//$Id$

function checkAuth() {
	session_start(); 
	if (empty($_SESSION['id']) && ! $_SESSION['root']) {
		header('Location: /login');
		exit();
	}

}

function stripPageNow($url)
{
	$url = preg_replace('/page=(\d)/', '', $url);

	return $url;
}

/**
 * 加载基本配置
 */
function & get_config() {
	static $_config;
	$config = array();

	if (isset($_config)) {
		return $_config[0];
	}

	$file_config = DIR . '/config/config.php';
	if ( ! file_exists($file_config)) {
		exit('配置文件不存在！');
	}

	require($file_config);

	if ( ! isset($config) && ! is_array($config)) {
		exit('配置文件设置不正确！');
	}

	return $_config[0] =& $config;
}

function log_message($msg, $php_error = FALSE) {
	$log_path = 'log/';

	$file = $log_path.'log-'.date('Y-m-d').'.log';
	$message = '';

	$message .= date('Y/m/d H:i:s'). ' --> '.$msg."\n";

	file_put_contents($file, $message, FILE_APPEND);

	return TRUE;
}

function site_set_offline() {
	header('Content-Type: text/html; charset=utf-8');
	if ($output = @file_get_contents(DIR . '/site_offline_html.lang')) {
		echo $output;
	} else {
		echo '站点维护中…';
	}
	exit;
}

