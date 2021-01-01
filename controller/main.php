<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

require_once 'inc/helper.php';
require_once 'inc/thread.php';
require_once 'inc/format.php';

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = "white";
		$rule_action['actions'] = array(
			'index',
			'thread',
		);
		return $rule_action;
	}

	public function index_action()
	{
		$per_page = S::get_int('imageboard_threads_per_page');
		if ($per_page < 1) $per_page = 50;

		$items = ib_thread::list(array(
			'page' => $_GET['page'],
			'per_page' => $per_page,
			'is_mod' => ib_h::is_mod($this->user_info),
			'show_all' => $_GET['show_all'],
			'recent_replies_per_thread' => S::get('imageboard_recent_replies_per_thread'),
		));

		if (!$items AND !!$_GET['page'])
		{
			H::error_404();
		}

		TPL::assign('pagination', AWS_APP::pagination()->create(array(
			'base_url' => ib_h::url('/') . ($_GET['show_all'] ? 'show_all-' . intval($_GET['show_all']) : ''),
			'total_rows' => ib_thread::count(),
			'per_page' => $per_page,
			'num_links' => 19
		)));

		TPL::assign('page_title', ib_h::build_page_title());
		TPL::assign('items', $items);
		TPL::output('index', 'imageboard');
	}

	public function thread_action()
	{
		$per_page = S::get_int('imageboard_replies_per_page');
		if ($per_page < 1) $per_page = 50;

		$item = ib_thread::get(array(
			'id' => $_GET['id'],
			'page' => $_GET['page'],
			'per_page' => $per_page,
			'is_mod' => ib_h::is_mod($this->user_info),
			'show_all' => $_GET['show_all'],
		));

		if (!$item)
		{
			H::error_404();
		}

		TPL::assign('pagination', AWS_APP::pagination()->create(array(
			'base_url' => ib_h::url('/thread/id-') . intval($_GET['id']) . ($_GET['show_all'] ? '__show_all-' . intval($_GET['show_all']) : ''),
			'total_rows' => intval($item['reply_count']),
			'per_page' => $per_page,
			'num_links' => 19
		)));

		if (!$item['thread']['status'])
		{
			TPL::assign('page_title', ib_h::build_page_title($item['thread']['body']));
		}
		else
		{
			TPL::assign('page_title', ib_h::build_page_title());
		}
		TPL::assign('thread_id', $item['thread_id']);
		TPL::assign('item', $item);
		TPL::output('thread', 'imageboard');
	}
}


