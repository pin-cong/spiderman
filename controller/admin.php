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

class admin extends AWS_CONTROLLER
{
	private $setting_fields;

	public function setup()
	{
		if (!$this->user_info['permission']['is_moderator'])
		{
			HTTP::error_403();
		}

		$this->setting_fields = array(
			'imageboard_recent_replies_per_thread' => '每個主題顯示最近回覆數',
			'imageboard_threads_per_page' => '每頁顯示主題數',
			'imageboard_replies_per_page' => '每頁顯示回覆數',
			'imageboard_body_length_limit' => '內容長度限制',
			'imageboard_page_title' => '網頁標題',
			'imageboard_title' => '標題',
			'imageboard_subtitle' => '小標題',
			'imageboard_claim' => '頁腳聲明',
			'imageboard_hyperlinks' => '超連結',
			'imageboard_custom_meta' => '自訂 meta',
			'imageboard_custom_head' => '自訂 head',
			'imageboard_url_rewrite' => '打開 URL Rewrite (Y/N)',
			'imageboard_moderator_group_ids' => '普通管理用戶組 ID(s) ","分隔',
			'imageboard_captcha_reputation_lt' => '聲望小於多少需要驗證碼 (留空則關閉)',
		);
	}

	public function settings_action()
	{
		$this->crumb('設定');

		TPL::assign('action_url', ib_h::url('/admin/save_settings/'));
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

	public function rebuild_index_action()
	{
		$page = intval($_GET['page']);
		if ($page < 1) $page = 1;
		$per_page = intval($_GET['per_page']);
		if ($per_page < 1) $per_page = 100;

		$recent_replies_per_thread = S::get('imageboard_recent_replies_per_thread');

		if ($threads = $this->model()->fetch_page('imageboard_post', 'thread_id = 0', 'id ASC', $page, $per_page))
		{
			foreach ($threads as $key => $val)
			{
				ib_post::upsert_index($val['id'], $recent_replies_per_thread);
			}
			$msg = "正在重建索引, 批次: {$page}";
			$page += 1;
			$url = "/admin/rebuild_index/page-{$page}__per_page-{$per_page}";
			ib_h::redirect_msg($msg, $url, 0);
		}
		else
		{
			ib_h::redirect_msg('重建索引完成');
		}
	}

}

