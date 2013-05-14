<?php

//$Id$

class Pagination
{
	private $pageName = 'pagenow';
	private $pageNow = 1;
	private $pageSize = 10;
	private $pageCount = 0;
	private $rowsCount = 0;
	private $url = '';

	private $pageDiv=  'pagination';
	private $pageCur =  'page-cur';

	private $pageFirst = '&lsaquo; First';
	private $pageLast =  'Last &rsaquo;';
	private $pagePrev =  '&lt;';
	private $pageNext =  '&gt;';

	private $pageBoth =  5;
	private $pageDesc =  TRUE;
	private $pageGoto =  TRUE;

	function __construct($config)	
	{
		if (is_array($config)) {
			foreach ($config as $key=>$val) {
				if (isset($this->$key)) {
					$this->$key = $val;
				}
			}
		}
    	
		$this->_set_url();

		if (empty($this->url)) {
			self::errorMsg('Pagination.class url miss!');
		}
	}

	public function creatPagination() 
	{
	  //每页显示和搜索结果不能为0
		if ($this->pageSize == 0 || $this->rowsCount == 0) 
		{
	    return '';
	  }
	  
	  //计算总页数
	  $this->pageCount = ceil($this->rowsCount/$this->pageSize);
	  //总页数为0，返回空
	  if ($this->pageCount == 0) {
	    return '';
	  }
    //当前页数大于总页数将显示最后一页
    if ($this->pageNow > $this->pageCount) {
      $this->pageNow = $this->pageCount;
    }
    
    //输出
	  $output = '';
	  
		//div.class
		$output .= '<div class='.$this->pageDiv.'>';

		//第一页
		if ($this->pageFirst != FALSE && $this->pageNow != 1) {
			$output .= "<a href=\"{$this->url}1\">$this->pageFirst</a>"; 
		}

		//上一页
		if ($this->pagePrev != FALSE && $this->pageNow != 1) {
			$output .= '<a href="'.$this->url.($this->pageNow-1).'">'.$this->pagePrev.'</a>'; 
		}

		for ($i=$this->pageNow-$this->pageBoth; $i<=$this->pageNow+$this->pageBoth; $i++) {

			if ($i < 0 || $i > $this->pageCount) {
				continue;
			}

			if ($i == $this->pageNow) {
				$output .= '<span class="'.$this->pageCur.'">'.$i.'</span>'; 
			} elseif ($i > 0 && ($i < $this->pageNow + $this->pageBoth || $i > $this->pageNow - $this->pageBoth)) {
				$output .= "<a href=\"$this->url$i\">$i</a>";
			} else {
				$output .='';
			}

		}

		//下一页
		if ($this->pageNext != FALSE && $this->pageNow != $this->pageCount) {
			$output .= '<a href="'.$this->url.($this->pageNow+1).'">'.$this->pageNext.'</a>'; 
		}

		//最后一页
		if ($this->pageFirst !== FALSE && $this->pageNow != $this->pageCount) {
			$output .= "<a href=\"{$this->url}{$this->pageCount}\">$this->pageLast</a>"; 
		}

		//描述
		if ($this->pageDesc) {
			$output .= '共'.$this->pageCount.'页';
		}

		//跳转
		if ($this->pageGoto) {
			$output .= '&nbsp;到<input onblur="if (this.value) location.href=\''.$this->url.'\'+this.value" type="text" class="goto" value="" />页';
		}
		//</div>
		$output .= '</div>';

		return $output;
	}

	public static function errorMsg($msg="") 
	{
		exit($msg);
	}

	private function _set_url()	
	{
		if (empty($this->url)) {
		  $this->url = $_SERVER["REQUEST_URI"];
		}
		
		//过滤有页数的get请求
  	if (strpos($this->url, $this->pageName) !== FALSE) {
  		$this->url = preg_replace(
  		'/[?|&]' . $this->pageName . '=\\d*/e',
  		'', 
  		$this->url
  		);
		}
		
		//$this->url .= ((strpos($this->url, '?') === FALSE) ? '?' : '&') . $this->pageName . '=';
		$this->url .= '&' . $this->pageName . '=';
	}

}
