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
require_once 'inc/post.php';

class mod extends AWS_CONTROLLER
{
	private $setting_fields;

	public function setup()
	{
		if (!ib_h::is_mod($this->user_info))
		{
			HTTP::error_403();
		}

		$this->setting_fields = array(
			'imageboard_post_replacing_keywords' => '如果貼文中出現以下關鍵詞則替換全文',
			'imageboard_emoticons' => '常用表情',
		);
	}

	public function settings_action()
	{
		$this->crumb('設定');

		TPL::assign('action_url', ib_h::url('/mod/save_settings/'));
		TPL::assign('fields', $this->setting_fields);
		TPL::assign('settings', get_settings());
		TPL::output('settings', 'imageboard');
	}

	public function save_settings_action()
	{
		$fields = array();
		foreach ($this->setting_fields AS $key => $val)
		{
			if (isset($_POST[$key]))
			{
				$fields[$key] = $_POST[$key];
			}
		}
		$this->model('setting')->set_vars($fields);

		H::ajax_json_output(AWS_APP::RSM(null, -1, '已儲存'));
	}

	public function batch_action()
	{
		if (!isset($_POST['ids']) OR !isset($_POST['status']) OR !in_array($_POST['status'], array(0, 1, 2)))
		{
			ib_h::redirect_msg('請選擇');
		}
		ib_post::batch($_POST['ids'], $_POST['status']);

		$thread_id = intval($_POST['thread_id']);
		$page = intval($_POST['page']);
		if ($thread_id > 0)
		{
			$url = '/thread/id-' . $thread_id;
			if ($page > 1)
			{
				$url .= '__page-' . $page;
			}
		}
		else
		{
			$url = "/";
			if ($page > 1)
			{
				$url .= 'page-' . $page;
			}
		}

		ib_h::redirect($url);
	}

	public function post_action()
	{
		
	}

	public function edit_post_action()
	{
	}

}


