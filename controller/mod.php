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
			H::error_403();
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
			if (!is_null(H::POST_S($key)))
			{
				$fields[$key] = H::POST_S($key);
			}
		}
		$this->model('setting')->set_vars($fields);

		H::ajax_json_output(AWS_APP::RSM(null, -1, '已儲存'));
	}

	public function batch_action()
	{
		if (is_null(H::POST('ids')) OR is_null(H::POST('status')) OR !in_array(H::POST('status'), array(0, 1, 2)))
		{
			ib_h::redirect_msg('請選擇');
		}
		ib_post::batch(H::POST('ids'), H::POST('status'));

		ib_h::redirect($_SERVER['HTTP_REFERER']);
	}

	public function post_action()
	{
		
	}

	public function edit_post_action()
	{
	}

}


