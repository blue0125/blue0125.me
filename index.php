<?php
//错误级别
error_reporting(E_ALL ^ E_NOTICE);

//时间
$timeStart = microtime();

define('ROOT', dirname(__FILE__));
define('CORE', ROOT.'/'.'system');

//通用函数文件
require_once ROOT.'/system/common.php';

//自动加载函数
function __autoload($class) 
{
	list($className, $classPath) = explode('_', $class);

	switch ($classPath) 
	{
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

$timeEnd = microtime();

//耗时计算
list($sm, $ss) = explode(' ', $timeStart);
list($em, $es) = explode(' ', $timeEnd);

echo '<br />'.number_format( ($em+$es)-($sm+$ss), 5);

