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
	public static function subject(&$post)
	{
		if ($post['status'] == 2)
		{
			$post['file_type'] = 0;
			$post['body'] = '';
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
			return safe_text($post['body']);
		}
		return '無本文';
	}

	public static function time(&$post)
	{
		return date_friendly($post['time']);
	}
}