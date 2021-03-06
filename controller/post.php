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
require_once 'inc/post.php';
require_once 'inc/hcaptcha.php';

class post extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = "white";
		$rule_action['actions'] = array(
			'thread',
			'reply',
		);
		return $rule_action;
	}

	public function thread_action()
	{
		$data = array();
		$this->get_content($data);

		$this->check_interval();
		$this->check_duplicate($data);

		$this->check_captcha();
		$this->get_uid($data);
		$this->upload_file($data);

		$this->cache_interval();
		$this->cache_duplicate($data);
		$this->cache_captcha();

		ib_post::thread($data);

		ib_h::redirect('/');
	}

	public function reply_action()
	{
		$data = array();
		$this->get_content($data);

		$this->check_interval();
		$this->check_duplicate($data);

		$item = ib_thread::info(array(
			'id' => H::POST('thread_id'),
			'is_mod' => ib_h::is_mod($this->user_info),
		));
		if (!$item)
		{
			ib_h::redirect_msg('串不存在');
		}
		if ($item['locked'])
		{
			ib_h::redirect_msg('串已鎖');
		}

		$this->check_thread_time($item['thread_id']);
		$this->check_reply_time($item['thread_id']);

		$this->check_captcha();
		$this->get_uid($data);
		$this->upload_file($data);

		$data['thread_id'] = $item['thread_id'];
		$data['sage'] = $item['sage'];
		$data['recent_replies_per_thread'] = S::get('imageboard_recent_replies_per_thread');

		ib_post::reply($data);

		$this->cache_interval();
		$this->cache_duplicate($data);
		$this->cache_captcha();

		ib_h::redirect('/');
	}

	private function get_content(&$data)
	{
		$data['subject'] = H::POST_S('subject');
		if (iconv_strlen($data['subject']) > 150)
		{
			ib_h::redirect_msg('標題太長');
		}
		$data['body'] = H::POST_S('body');
		if (!$data['body'])
		{
			ib_h::redirect_msg('請填寫');
		}
		if (iconv_strlen($data['body']) > S::get_int('imageboard_body_length_limit'))
		{
			ib_h::redirect_msg('太長了');
		}
	}

	private function get_uid(&$data)
	{
		$data['uid'] = intval($this->user_id);
		if (!$data['uid'])
		{
			return;
		}
		if (H::POST_I('anonymous'))
		{
			$reputation_lt = S::get('imageboard_disallow_anonymous_reputation_lt');
			if (is_numeric($reputation_lt) AND $this->user_info['reputation'] < $reputation_lt)
			{
				return;
			}
			$data['uid'] = 0;
		}
	}

	private function upload_file(&$data)
	{
		$data['file_type'] = 0;
		$data['file'] = '';
	}

	private function check_interval()
	{
		if (!$this->user_id)
		{
			$interval = S::get('imageboard_anonymous_interval_post');
		}
		else
		{
			$interval = $this->user_info['permission']['interval_post'];
		}
		if (!check_user_operation_interval('imageboard_post', $this->user_id, $interval))
		{
			ib_h::redirect_msg('太快了');
		}
	}

	private function cache_interval()
	{
		set_user_operation_last_time('imageboard_post', $this->user_id);
	}

	private function check_duplicate($data)
	{
		if (!check_repeat_submission(0, $data['body']))
		{
			ib_h::redirect_msg('重複了');
		}
	}

	private function cache_duplicate($data)
	{
		set_repeat_submission_digest(0, $data['body']);
	}

	private function check_captcha()
	{
		if (!ib_h::need_captcha($this->user_info))
		{
			return;
		}
		if (!ib_hcaptcha::check())
		{
			ib_h::redirect_msg('驗證碼');
		}
	}

	private function cache_captcha()
	{

	}

	private function check_thread_time($post_id)
	{
		$days = S::get_int('imageboard_thread_expiration_days');
		if (!$days)
		{
			return;
		}

		if ($post = ib_post::get($post_id))
		{
			$seconds = $days * 24 * 3600;
			$time_before = real_time() - $seconds;

			if (intval($post['time']) < $time_before)
			{
				ib_h::redirect_msg('已失去時效性');
			}
		}
	}

	private function check_reply_time($post_id)
	{
		$days = S::get_int('imageboard_reply_expiration_days');
		if (!$days)
		{
			return;
		}

		if ($post = ib_post::get_last_post($post_id))
		{
			$seconds = $days * 24 * 3600;
			$time_before = real_time() - $seconds;

			if (intval($post['time']) < $time_before)
			{
				ib_h::redirect_msg('已失去時效性');
			}
		}
	}

}

