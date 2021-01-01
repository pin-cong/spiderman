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

class ib_fmt
{
	private static function get_kb_item($id)
	{
		static $cache;

		if (!$cache[$id])
		{
			if ($item = AWS_APP::model('kb')->get($id))
			{
				$cache[$id] = $item;
			}
		}

		return $cache[$id];
	}

	private static function get_kb_id($post_id)
	{
		$key = 'imageboard_post_' . $post_id;

		static $cache;
		if ($cache[$key])
		{
			return $cache[$key];
		}

		$kb_count = AWS_APP::model('kb')->size();
		if (!$kb_count)
		{
			return 0;
		}

		$num = checksum($key);
		$kb_id = $num % $kb_count + 1;
		$cache[$key] = $kb_id;
		return $kb_id;
	}

	private static function get_kb($post_id)
	{
		$id = self::get_kb_id($post_id);
		if (!$item = self::get_kb_item($id))
		{
			return '';
		}
		return safe_text($item['title'] . "\r\n\r\n" . $item['message']);
	}

	public static function subject(&$post)
	{
		if (!$post['status'])
		{
			if (S::content_contains('imageboard_post_replacing_keywords', $post['subject'] . $post['body'], true))
			{
				$post['status'] = 2;
			}
		}

		if ($post['status'] == 2)
		{
			$post['file_type'] = 0;
			$post['body'] = self::get_kb($post['id']);
			return '<i>已替換</i>';
		}
		else if ($post['status'] == 1)
		{
			$post['file_type'] = 0;
			$post['body'] = '';
			return '<s>已刪除</s>';
		}

		if (!!$post['subject'])
		{
			return safe_text($post['subject']);
		}
		return '無題';
	}

	public static function name(&$post)
	{
		if (!!$post['uid'])
		{
			if (!!$post['user_info'] AND !!$post['user_info']['user_name'])
			{
				return safe_text($post['user_info']['user_name']);
			}
		}
		return '無名';
	}

	public static function body(&$post)
	{
		if (!!$post['body'])
		{
			if (!!$post['status'])
			{
				return $post['body'];
			}
			else
			{
				return safe_text($post['body']);
			}
		}
		return '無本文';
	}

	public static function time(&$post)
	{
		return date_friendly($post['time']);
	}
}