<?php

checkAuth();

$actions = array('list', 'add', 'save', 'del');

$view = new MyTemplate();

if (isset($_GET['ac']) && $ac = $_GET['ac']) {
	switch ($ac) {
	case 'add':
		$blogInfo = array();
		if (isset($_GET['bid']) && $bid = $_GET['bid']) {
			$blogModel = new blog_model();
			$blogInfo = $blogModel->get_blog_by_bid($bid);
		}

		$view->assign('info', $blogInfo);
		$view->display('admin/blogadd');
		break;
	case 'save':
		$res = array();
		$res['code'] = 0;
		$res['data'] = array();

		if (isset($_POST)) {
			//var_dump($_POST);
			//验证
			if (empty($_POST['title']) || mb_strlen($_POST['title'], 'UTF-8') > 20 ) {
				$res['data']['error_messages'] = array('title' =>'标题不能为空或超过20个字！');
			}
			if (empty($_POST['content']) || mb_strlen($_POST['content'], 'UTF-8') > 10000 ) {
				$res['data']['error_messages'] = array('content' =>'内容不能为空或超过10000个字！');
			}

			if ( !$res['data']['error_messages']) {
				//$data = array();
				$data['title'] 		= addslashes(trim($_POST['title']));
				$data['content'] 	= addslashes(trim($_POST['content']));
				$data['status'] 	= (int)$_POST['status'];
				$data['updated']  = time();

				$blogModel = new blog_model();

				if ($bid = (int)$_POST['bid']) {
					//更新数据
					$result = $blogModel->update_blog_by_bid($bid, $data);
				} else {
					//插入数据库
					$result = $blogModel->insert_blog($data);
				}
			}

			if ($result) {
				$res['code'] = 200;
				$res['data']['success_message'] = '保存成功！';
			}
		}

		$view->json($res);
		break;

		//删除
	case 'del':
		$res = array();
		$res['code'] = 0;
		$res['data'] = array();

		if ( !empty($_POST)) {
			$bid = (int)$_POST['bid'];
			if ($bid) {
				$blogModel = new blog_model();
				$blogInfo = $blogModel->get_blog_by_bid($bid);

				if ($blogInfo) {
					if ($blogModel->del_blog($bid)) {
						$res['code'] = 200;
					}
				} else {
					$res['code'] = 404;
					$res['data']['error_messages'] = '没有该条博客！';
				}
			} else {
				$res['code'] = 404;
				$res['data']['error_messages'] = '没有传入博客id';
			}
		}

		$view->json($res);
		break;

		//列表
	case 'list':
	default:
		//list
		$result = array();

		if (isset($_GET['pagenow']) && (int)$_GET['pagenow'] > 0) {
			$pageNow = $_GET['pagenow'];
		} else {
			$pageNow = 1;
		}
		$pageSize = 20;

		$blogService = new blog_model();
		$result = $blogService->get_blog_list($pageNow, $pageSize);

		if ($result['count']) {
			foreach ($result['data'] as $key=>$val) {
				if ($result['data'][$key]['status'] == 0) {
					$result['data'][$key]['status'] = '未发布';
				} else {
					$result['data'][$key]['status'] = '已发布';
				}

				$result['data'][$key]['content'] = mb_substr($result['data'][$key]['content'], 0, 10, 'utf-8');

				if ($result['data'][$key]['updated']) {
					$result['data'][$key]['updated'] = date('Y-m-d H:i:s', $result['data'][$key]['updated']);
				}
			}
		}

		//var_dump($result);

		$config_page = array(
			'pageNow' => $pageNow,
			'pageSize' => $pageSize,
			'rowsCount' => $result['count'],
		);
		$page = new Pagination($config_page);

		$result['page'] = $page->creatPagination();

		$view->assign('result', $result);
		$view->display('admin/bloglist');
	}
}


