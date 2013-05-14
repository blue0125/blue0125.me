<?php
require_once 'Template.class.php';

class MyTemplate extends Template {
	public $config = array(
		'tpl_dir' => 'views',
		'compile_dir'	=> 'tmp/tpl_c',
	);
	public function __construct() {
		parent::setConfig($this->config);
	}
}
