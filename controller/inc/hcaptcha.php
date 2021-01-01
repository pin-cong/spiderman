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

class ib_hcaptcha
{
	public static function check()
	{
		$token = H::POST_S('h-captcha-response');
		if (!$token)
		{
			return false;
		}

		if (!$secret = S::get('imageboard_hcaptcha_secret'))
		{
			return false;
		}

		$postdata = http_build_query(array(
			'secret' => $secret,
			'response' => $token,
		));

		$body = @file_get_contents('https://hcaptcha.com/siteverify', false, stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header'  => 'Content-Type: application/x-www-form-urlencoded',
				'content' => $postdata,
			)
		)));

		if (!$body)
		{
			return false;
		}

		$body = json_decode($body, true);
		if (!is_array($body))
		{
			return false;
		}

		if ($body['success'] !== true)
		{
			return false;
		}

		if ($hostname = S::get('imageboard_hcaptcha_hostname'))
		{
			return ($body['hostname'] == $hostname);
		}

		return true;
	}

}