<?php

require_once 'system/common.php';

checkAuth();

$view = new MyTemplate();
$view->display('admin/manage');
