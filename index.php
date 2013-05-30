<?php

error_reporting(E_ALL ^ E_NOTICE);
define('ROOT', dirname(__FILE__));
define('CORE', ROOT.'/'.'system');

require_once ROOT.'/system/common.php';

function __autoload($class) 
{
	list($className, $classPath) = explode('_', $class);

	switch ($classPath) {
		//TODO 控制器

	case 'model':
		$file = ROOT.'/'.$classPath.'s/'.$class.'.php';
		if (file_exists($file)) 
		{
			require_once $file;
		}
		break;

	default:
		$file = CORE.'/'.$className.'.class.php';
		if (file_exists($file)) 
		{
			require_once $file;
		}
		break;
	}
}

Router::getRouter();

