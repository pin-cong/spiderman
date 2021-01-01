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

class ib_h
{
	private static function app_dir()
	{
		static $rewrite;
		if (!isset($rewrite))
		{
			$rewrite = S::get('imageboard_url_rewrite') == 'Y';
		}
		if ($rewrite)
		{
			return '';
		}
		return '/imageboard';
	}

	public static function url($s = null)
	{
		$result = url_rewrite() . self::app_dir();
		if (isset($s))
		{
			$result .= $s;
		}
		return $result;
	}

	public static function redirect_msg($message, $url = NULL, $interval = 5)
	{
		if ($url)
		{
			$url = self::app_dir() . $url;
		}
		H::redirect_msg($message, $url, $interval);
	}

	public static function redirect($url)
	{
		if (!$url)
		{
			$url = '/';
		}
		$url = self::app_dir() . $url;
		HTTP::redirect($url);
	}

	public static function is_mod($user_info)
	{
		if (!$user_info['uid'] OR !$user_info['user_group_id'])
		{
			return false;
		}
		if ($user_info['permission']['is_moderator'])
		{
			return true;
		}

		static $group_ids;
		if (!isset($group_ids))
		{
			$group_ids = S::get_array('imageboard_moderator_group_ids');
		}
		return in_array($user_info['user_group_id'], $group_ids);
	}

	public static function need_captcha($user_info)
	{
		static $reputation_lt;
		if (!isset($reputation_lt))
		{
			$reputation_lt = S::get('imageboard_captcha_reputation_lt');
		}
		if (!is_numeric($reputation_lt))
		{
			return false;
		}
		if (!$user_info['uid'])
		{
			return true;
		}
		if (!is_numeric($user_info['reputation']))
		{
			return true;
		}
		return ($user_info['reputation'] < $reputation_lt);
	}

	public static function build_page_title($text = '')
	{
		if ($text)
		{
			$text = htmlspecialchars_decode($text);
			$text = truncate_text($text, 100);
			$text .= ' - ';
		}
		$text .= S::get('imageboard_page_title');
		return $text;
	}

	public static function emoticons()
	{
		static $list;
		if (!isset($list))
		{
			$list = S::get_key_value_pairs('imageboard_emoticons', null, true);
		}
		return $list;
	}

}