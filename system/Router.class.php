<?php
// $Id$

class Router 
{

	public static $uri = NULL;
	
	public function __construct()
	{

	}

	public static function getRouter()
	{
		$q = isset($_GET['q']) ? explode('/', rtrim($_GET['q'], '/')) : array();
		//index
		if (empty($q)) {
			$controller = ROOT.'/controllers/index.php';
			include_once($controller);
		} 
		else 
		{
			if (count($q) > 1) 
			{
				$controller = ROOT.'/controllers/' . $q[0] . '/' . $q[1] . '.php';
				if (file_exists($controller)) 
				{
					include_once($controller);
				}
			} 
			elseif (count($q) == 1) 
			{
				$controller = ROOT.'/controllers/'.$q[0].'.php';
				if (file_exists($controller)) 
				{
					include_once($controller);
				}
			} 
			else 
			{
				echo 'The controller is not exists.';
			}
		}

		//$url = $_SERVER['REQUEST_URI'];
		//echo $url;

	}

}

