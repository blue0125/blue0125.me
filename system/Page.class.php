<?php

//$Id$

class Page 
{

	private $pageName = 'pagenow';
	public $pageNow = 1;
	public $pageSize = 10;
	public $pageCount;
	public $url;

	function __construct($config)
	{
		if (is_array($config)) {
			foreach ($config as $key=>$val) {
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}

		$this->_set_url();
		if (empty($this->url))
		{
			self::errorMsg('Page.class url miss！');
		}
	}

	public function show()
	{
		var_dump($this);
		return 'page________';
	}

	public static function errorMsg($msg='')
	{
		exit($msg);
	}



	function _s1et_url($url="")
	{
		if(!empty($url))
		{
			$this->url=$url.((stristr($url,'?'))?'&':'?').$this->page_name."=";
		}
		else
		{
			//自动获取
			if(empty($_SERVER['QUERY_STRING'])){
				//不存在QUERY_STRING时
				$this->url=$_SERVER['REQUEST_URI']."?".$this->page_name."=";
			}else{
				//
				if(stristr($_SERVER['QUERY_STRING'],$this->page_name.'=')){
					//地址存在页面参数
					$this->url=str_replace($this->page_name.'='.$this->nowindex,'',$_SERVER['REQUEST_URI']);
					$last=$this->url[strlen($this->url)-1];
					if($last=='?'||$last=='&'){
						$this->url.=$this->page_name."=";
					}else{
						$this->url.='&'.$this->page_name."=";
					}
				}else{
					$this->url=$_SERVER['REQUEST_URI'].'&'.$this->page_name.'=';
				}
			}
		}
	}

	private function _set_url()
	{
		if ( ! empty($this->url))
		{
			$this->url = $url . ((stristr($url, '?')) ? '&' : '?') . $this->pageName;
		}
		//if (!empty($this->url))
		//{
		//	$this->url = $url . ((stristr($url, '?')) ? '&' : '?') . $this->pageName;
		//}
		//else
		//{
		//	if ($_SERVER['QUERY_STRING'])
		//	{
		//		if (strpos($_SERVER['QUERY_STRING'], $this->pageName) !== FALSE)
		//		{
		//			$this->url = str_replace(
		//				array('?' . $this->pageName . '=' . $this->pageNow, '&' . $this->pageName . '=' . $this->pageNow), 
		//				array(((count(explode('&', $_SERVER['QUERY_STRING'])=== 1)) ? '' : '?' ), ''), 
		//				$_SERVER['REQUEST_URI']);
		//			echo $this->url;
		//		}
		//	}
		//	else 
		//	{
		//		$this->url = $_SERVER['REQUEST_URI'] . '?' . $this->pageName . '=';
		//	}

		//	
		//	if (strpos($this->url, $this->pageName) === FALSE) 
		//	{
		//		$this->url=$_SERVER['REQUEST_URI'].'&'.$this->pageName.'=';
		//	}
		//}
	}

}
