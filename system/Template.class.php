<?php

/**
 * Template  模板
 * 仿ecshop 
 * 
 * @version $Id$
 */
class Template {
	/**
	 * _config 模板配置
	 * 
	 * @var array
	 * @access private
	 */
	private $_config = array(
		'tpl_dir' => 'tpls',
		'compile_dir' 	=> 'tpls_c',
		'suffix' 				=> '.phtml',
	);

	/**
	 * _tpl 显示的文件
	 * 
	 * @var string
	 * @access private
	 */
	private $_tpl = '';

	/**
	 * _vars 传递的变量
	 *
	 * @var array
	 * @access private
	 */
	private $_vars = array();

	var $_temp_key      = array();  // 临时存放 foreach 里 key 的数组
	var $_temp_val      = array();  // 临时存放 foreach 里 item 的数组




	public function __construct() 
	{
	}

	public function setConfig($config) 
	{
		if(is_array($config)) {
			$this->_config = array_merge($this->_config, $config);
		}

		$this->_config = array_change_key_case($this->_config);

		return $this;
	}

	public function assign($vars, $value=NULL) 
	{
		if (is_array($vars)) {
			foreach($vars as $key=>$val) {
				$this->_vars[$key] = $val;
			}
		} else {
			$this->_vars[$vars] = $value;
		}
	}

	/**
	 * display 
	 * 
	 * @param mixed $tpl 
	 * @access public
	 * @return void
	 */
	public function display($tpl='', $cache_id='') 
	{
		error_reporting(E_ALL ^ E_NOTICE);
		if (empty($tpl)) {
			$this->_tpl = $tpl;
		}

		$output = $this->fetch($tpl);

		echo $output;
	}

	/**
	 * fetch 
	 * 
	 * @access private
	 * @return void
	 */
	public function fetch($tpl) 
	{
		//缓存
		//if ($this->_cache && $cache_id) {
		//}

		error_reporting(E_ALL ^ E_NOTICE);

		$this->_compile($tpl);

		if ($this->_vars) {
			extract($this->_vars);
		}

		ob_start();
		include $this->_getCompileFile($tpl);
		$out = ob_get_clean();
		return $out;
	}

	/**
	 * _getCompileFile 
	 * 
	 * @access private
	 * @return void
	 */
	private function _getCompileFile($tpl) 
	{
		return $this->_config['compile_dir'].'/'.$tpl.'.compile.php';
	}

	/**
	 * _getTemplateFile 
	 * 
	 * @access private
	 * @return void
	 */
	private function _getTemplateFile($tpl) 
	{
		$file = $this->_config['tpl_dir'].'/'.$tpl.$this->_config['suffix'];

		if (is_file($file)) {
			return $file;
		} else {
			self::showError(__CLASS__.'::'.__FUNCTION__.' 没有这个模板文件：'.$file);
		}
	}

	/**
	 * _compile 
	 * 
	 * @access private
	 * @return void
	 */
	private function _compile($tpl) 
	{
		$tplFile = $this->_getTemplateFile($tpl);
		$compileFile = $this->_getCompileFile($tpl);
		//TODO del 1
		if (1 || !is_file($compileFile) || filemtime($tplFile) > filemtime($compileFile)) {
			$out = '';

			$source = file_get_contents($tplFile);
			$out = $this->_parse($source);
			self::mkdir(dirname($compileFile));
			file_put_contents($compileFile, $out);
		}
	}

	static function mkdir($dir, $mode=0777) 
	{
		if (!is_dir($dir)) {
			self::mkdir(dirname($dir), $mode);
			return mkdir($dir, $mode);
		}
		return true;
	}


	public function _parse($source) 
	{
		return preg_replace("/{([^\}\{\\n]*)}/e", "\$this->tags('\\1');", $source);
	}

	public function tags($tag) 
	{
		$tag = htmlspecialchars(trim($tag));

		if ($tag{0} == '$') {
			if ((strncmp($tag, '$lang.', 6) === 0) && strrpos($tag, '$') === 0) {
				return $this->get_lang(substr($tag, 1));
			} else {
				return '<?php echo ' . $this->get_val(substr($tag, 1)) . '; ?>';
			} 
			return $this->_tags_var(substr($tag, 1));
		} elseif ($tag{0} == '*' && substr($tag{0}, -1) == '*') {
			return '';
		} elseif ($tag{0} == '/') {
			switch (substr($tag, 1))
			{
			case 'if':
				return '<?php endif; ?>';
				break;

			case 'foreach':
				$output = '<?php endforeach; endif; unset($_from); ?>';
				$output .= "<?php \$this->pop_vars();; ?>";

				return $output;
				break;
			
			default:
				return '{'. $tag .'}';
				break;
			}
		} else {
			$tags = explode(' ', $tag);
			$tag_cur = array_shift($tags);

			switch ($tag_cur) {
			case 'if':
				break;

			case 'else':
				return '<?php else: ?>';
				break;

			case 'elseif':
				break;

			case 'foreach':
				return $this->_tags_foreach(substr($tag, 8));
				break;

			case 'include':
				return $this->_tags_include(substr($tag, 8));
				break;

			case 'js':
				return $this->_tags_javascript(substr($tag, 3));
				break;

			case 'css':
				return $this->_tags_style(substr($tag, 4));
				break;
			default:
			}
		}

		return '{' . $tag . '}';
	}

	//处理str成$this->_var['str']
	//处理str1.str2成$this->_var['str1']['str2']
	function make_var($str) 
	{
		if (strpos($str, '.') === FALSE) 
		{
			$var = '$this->_vars[\'' . $str. '\']';
		}
		else
		{
			$a = explode('.', $str);
			$prefix = array_shift($a);

			//默认变量
			if ($prefix == 'smarty')
			{
			} 
			else
			{
				$var = '$this->_vars[\'' . $prefix . '\']';
			}

			foreach($a as $v) 
			{
				$var .= '[\'' . $v . '\']';
			}
		}

		return $var;
	}

	function _tags_foreach($str)
	{
		$param_array = $this->get_para($str, 0);
		//$param_array['from']
		//$param_array['key']
		//$param_array['item']
		$from = $param_array['from'];

		$item = $this->get_val($param_array['item']);

		if (!empty($param_array['key']))
		{
			$key = $param_array['key'];
			$key_part = $this->get_val($key).' => ';
		} 
		else
		{
			$key = null;
			$key_part = '';
		}

		if (!empty($param_array['name']))
		{
			$name = $param_array['name'];
		}
		else
		{
			$name = null;
		}

		$output = '<?php ';
		$output .= "\$_from = $from; if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array'); }; \$this->push_vars('$param_array[key]', '$param_array[item]');";

		if (!empty($name))
		{
			$foreach_props = "\$this->_foreach['$name']";
			$output .= "{$foreach_props} = array('total' => count(\$_from), 'iteration' => 0);\n";
			$output .= "if ({$foreach_props}['total'] > 0):\n";
			$output .= "    foreach (\$_from as $key_part$item):\n";
			$output .= "        {$foreach_props}['iteration']++;\n";
		}
		else
		{
			$output .= "if (count(\$_from)):\n";
			$output .= "  foreach (\$_from as $key_part$item):\n";
		}

		$output .= '?>';

		return $output;
	}

	function _tags_include($str="")
	{
		$para_array = $this->get_para($str, 0);
		$file = $para_array['file'];

    return '<?php echo $this->fetch('.$file.');?>';


		//if (!empty($param_array['key']))
		//{
		//	$key = $param_array['key'];
		//	$key_part = $this->get_val($key).' => ';
		//} 
		//else
		//{
		//	$key = null;
		//	$key_part = '';
		//}

		//if (!empty($param_array['name']))
		//{
		//	$name = $param_array['name'];
		//}
		//else
		//{
		//	$name = null;
		//}

		//$output = '<?php ';
		//$output .= "\$_from = $from; if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array'); }; \$this->push_vars('$param_array[key]', '$param_array[item]');";

		//if (!empty($name))
		//{
		//	$foreach_props = "\$this->_foreach['$name']";
		//	$output .= "{$foreach_props} = array('total' => count(\$_from), 'iteration' => 0);\n";
		//	$output .= "if ({$foreach_props}['total'] > 0):\n";
		//	$output .= "    foreach (\$_from as $key_part$item):\n";
		//	$output .= "        {$foreach_props}['iteration']++;\n";
		//}
		//else
		//{
		//	$output .= "if (count(\$_from)):\n";
		//	$output .= "  foreach (\$_from as $key_part$item):\n";
		//}

		//$output .= '?\>';

		//return $output;
	}

	function push_vars($key, $val)
	{
		if (!empty($key))
		{
			array_push($this->_temp_key, "\$this->_vars['$key']='" .$this->_vars[$key] . "';");
		}
		if (!empty($val))
		{
			array_push($this->_temp_val, "\$this->_vars['$val']='" .$this->_vars[$val] ."';");
		}
	}

	function pop_vars()
	{
		$key = array_pop($this->_temp_key);
		$val = array_pop($this->_temp_val);

		if (!empty($key))
		{
			eval($key);
		}
	}

	/**
	 * get_para
	 * 获取{}包含的参数
	 * 
	 * @param string $str ' a =b c= d e = f g=$h ' 
	 * @param bool $type 
	 * @access public
	 * @return void 
	 * array (
	 *  a => b,
	 *  c => d,
	 *  e => f
	 * )
	 */
	function get_para($str='', $type=TRUE)
	{
		$para_str = str_replace(array('= ', ' =', ' = '), '=', trim($str));

		$para_arr = explode(' ', $para_str);

		foreach($para_arr as $val) {
			if (strpos($val, '=') !== FALSE) {
				list($k, $v) = explode('=', $val);
			}

			if ($v{0} == '$') {
				if ($type) {
					eval('$para[\'' . $k . '\']=' . $this->get_val(substr($v, 1)) . ';');
				} else {
					$para[$k] = $this->get_val(substr($v, 1));
				}
			} else {
				$para[$k] = $v;
			}
		}

		return $para;
	}

	function get_val($val)
	{
		if (strrpos($val, '[') !== false)
		{
			$val = preg_replace("/\[([^\[\]]*)\]/eis", "'.'.str_replace('$','\$','\\1')", $val);
		}

		if (strrpos($val, '|') !== false)
		{
			$moddb = explode('|', $val);
			$val = array_shift($moddb);
		}

		if (empty($val))
		{
			return '';
		}

		if (strpos($val, '.$') !== false)
		{
			$all = explode('.$', $val);

			foreach ($all AS $key => $val)
			{
				$all[$key] = $key == 0 ? $this->make_var($val) : '['. $this->make_var($val) . ']';
			}
			$p = implode('', $all);
		}
		else
		{
			$p = $this->make_var($val);
		}

		if (!empty($moddb))
		{
			foreach ($moddb AS $key => $mod)
			{
				$s = explode(':', $mod);
				switch ($s[0])
				{
				case 'escape':
					$s[1] = trim($s[1], '"');
					if ($s[1] == 'html')
					{
						$p = 'htmlspecialchars(' . $p . ')';
					}
					elseif ($s[1] == 'url')
					{
						$p = 'urlencode(' . $p . ')';
					}
					elseif ($s[1] == 'quotes')
					{
						$p = 'addslashes(' . $p . ')';
					}
					elseif ($s[1] == 'input')
					{
						$p = 'str_replace(\'"\', \'&quot;\',' . $p . ')';
					}
					elseif ($s[1] == 'editor')
					{
						$p = 'html_filter(' . $p . ')';
					}
					else
					{
						$p = 'htmlspecialchars(' . $p . ')';
					}
					$test1=true;
					break;

				case 'nl2br':
					$p = 'nl2br(' . $p . ')';
					break;

				case 'default':
					$s[1] = $s[1]{0} == '$' ?  $this->get_val(substr($s[1], 1)) : "'$s[1]'";
					$p = '(' . $p . ' == \'\') ? ' . $s[1] . ' : ' . $p;
					break;

				case 'truncate':
					$p = 'sub_str(' . $p . ",$s[1])";
					break;

				case 'strip_tags':
					$p = 'strip_tags(' . $p . ')';
					break;

				case 'price':
					$p = 'price_format(' . $p . ')';
					break;

				case 'date':
					if (empty($s[1]))
					{
						/* 默认是简单格式 */
						$date_format = Conf::get('time_format_simple');
					}
					else
					{
						if (in_array($s[1], array('simple', 'complete')))
						{
							/* 允许使用简单和完整格式，从配置项中取 */
							$date_format = Conf::get("time_format_{$s[1]}");
						}
						else
						{
							/* 也可以自定义 */
							unset($s[0]); //date格式中可能含有':',所以实际参数要还原下
							$date_format = implode(':', $s);
						}
					}
					$p = 'local_date("' . $date_format . '",' . $p . ')';
					break;
				case 'modifier':
					if (function_exists($s[1]))
					{
						$p = 'call_user_func("' . $s[1] . '",' . $p . ')';
					}

					break;
				default:
					# code...
					break;
				}
			}
		}

		return $p;
	}

	function _tags_javascript($str)
	{
		$para = $this->get_para($str);
	}

	/**
	 * showError 
	 * 
	 * @param mixed $error 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function showError($error) {
		exit($error);
	}

	public function json($res) {
		echo json_encode($res);
	}

}
