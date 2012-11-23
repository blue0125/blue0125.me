<?php

//$Id$

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

//显示错误页面

/**
 * 手动加载类
 */
function & load_class($class, $directory = 'system') {
	static $_classes = array();

	if (isset($_classes[$class])) {
		return $_classes[$class];
	}

	if (file_exists(DIR . '/' . $directory . '/' . $class . '.php')) {
		require(DIR . '/' . $directory . '/' . $class . '.php');
	}

	if ( ! class_exists($class)) {
		exit('找不到：' . $class . '.php');
	}

	is_load_class($class);
	$_classes[$class] = new $class();

	return $_classes[$class];
}

/**
 * 是否已经加载
 */
function & is_load_class($class = '') {
	static $_is_loaded = array();

	if ($class != '') {
		$_is_loaded[strtolower($class)] = $class;
	}

	return $_is_loaded;
}

function remove_invisible_characters($str, $url_encoded = TRUE) {
	$non_displayables = array();

	// every control character except newline (dec 10)
	// carriage return (dec 13), and horizontal tab (dec 09)

	if ($url_encoded)
	{   
		$non_displayables[] = '/%0[0-8bcef]/';  // url encoded 00-08, 11, 12, 14, 15
		$non_displayables[] = '/%1[0-9a-f]/'; // url encoded 16-31
	}   

	$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127

	do  
	{   
		$str = preg_replace($non_displayables, '', $str, -1, $count);
	}   
	while ($count);

	return $str;

}
