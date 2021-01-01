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
			H::error_403();
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
			'imageboard_post_replacing_limit' => '替換全文次數限制',
			'imageboard_anonymous_interval_post' => '匿名用戶發串冷卻秒數',
			'imageboard_captcha_reputation_lt' => '聲望小於多少需要驗證碼 (留空則不開啟)',
			'imageboard_disallow_anonymous_reputation_lt' => '聲望小於多少不允許匿名 (留空則不開啟)',
			'imageboard_thread_expiration_days' => '禁止回覆創建於多少天以前的串 (留空則不開啟)',
			'imageboard_reply_expiration_days' => '禁止回覆最後回覆於多少天以前的串 (留空則不開啟)',
			'imageboard_hcaptcha_sitekey' => 'hcaptcha sitekey',
			'imageboard_hcaptcha_secret' => 'hcaptcha secret',
			'imageboard_hcaptcha_hostname' => 'hcaptcha hostname',
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
			if (!is_null(H::POST_S($key)))
			{
				$fields[$key] = H::POST_S($key);
			}
		}
		$this->model('setting')->set_vars($fields);

		H::ajax_json_output(AWS_APP::RSM(null, -1, '已儲存'));
	}

	public function rebuild_index_action()
	{
		$page = H::GET_I('page');
		if ($page < 1) $page = 1;
		$per_page = H::GET_I('per_page');
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


