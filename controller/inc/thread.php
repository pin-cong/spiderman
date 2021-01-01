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

class ib_thread
{
	private static $_total_threads = 0;

	private static function intval_gt0($val)
	{
		$val = intval($val);
		if ($val < 1)
		{
			$val = 1;
		}
		return $val;
	}

	private static function &combine_post_data(&$post, &$users)
	{
		if (isset($post))
		{
			$post['user_info'] = $users[$post['uid']];
		}
		return $post;
	}

	public static function count()
	{
		return self::$_total_threads;
	}

	public static function list($opts)
	{
		$where = [];
		if (!$opts['is_mod'])
		{
			$where[] = ['masked', 'eq', 0];
		}

		$page = self::intval_gt0($opts['page']);
		$per_page = self::intval_gt0($opts['per_page']);

		$list = AWS_APP::model()->fetch_page('imageboard_index', $where, 'sort DESC, status ASC, last_post_id DESC', $page, $per_page);
		self::$_total_threads = AWS_APP::model()->total_rows();
		if (!$list)
		{
			return array();
		}

		$recent_replies_per_thread = intval($opts['recent_replies_per_thread']);

		foreach ($list as $key => $val)
		{
			// 收集用于一次查询
			$thread_id = intval($val['thread_id']);
			if ($thread_id > 0)
			{
				$post_ids[] = $thread_id;
			}
			if ($recent_replies_per_thread < 1 OR !$val['recent_reply_ids'])
			{
				$recent_reply_ids = array();
			}
			else
			{
				$recent_reply_ids = array_map('intval', explode(',', $val['recent_reply_ids']));
				if (count($recent_reply_ids) > $recent_replies_per_thread)
				{
					$recent_reply_ids = array_slice($recent_reply_ids, -$recent_replies_per_thread);
				}
			}
			foreach ($recent_reply_ids as $reply_id)
			{
				if ($reply_id > 0)
				{
					$post_ids[] = $reply_id;
				}
			}
			$list[$key]['recent_reply_ids'] = $recent_reply_ids;
		}

		if (!$post_ids)
		{
			return array();
		}
		//$post_ids = array_unique($post_ids);

		$post_list = AWS_APP::model()->fetch_all('imageboard_post', ['id', 'in', $post_ids]);
		if (!$post_list)
		{
			return array();
		}

		foreach ($post_list AS $key => $val)
		{
			// 收集用于一次查询
			$uid = intval($val['uid']);
			if ($uid > 0)
			{
				$uids[] = $uid;
			}
			// 转换为 map
			$posts[$val['id']] = $val;
		}

		$users = AWS_APP::model('account')->get_user_info_by_uids($uids);
		if (!is_array($users))
		{
			$users = array();
		}

		foreach ($list as $key => $val)
		{
			$list[$key]['thread'] = &self::combine_post_data($posts[$val['thread_id']], $users);
			foreach ($val['recent_reply_ids'] as $reply_id)
			{
				$list[$key]['replies'][] = &self::combine_post_data($posts[$reply_id], $users);
			}
		}

		return $list;
	}

	public static function get($opts)
	{
		$item = self::info($opts);
		if (!$item)
		{
			return array();
		}

		$page = self::intval_gt0($opts['page']);
		$per_page = self::intval_gt0($opts['per_page']);

		$thread_id = $item['thread_id'];

		if ($page == 1)
		{
			// 第一页同时获取主串和回复
			$per_page += 1;
			$where = [
				['id', 'eq', $thread_id],
				'or',
				['thread_id', 'eq', $thread_id],
			];
			$post_list = AWS_APP::model()->fetch_page('imageboard_post', $where, 'thread_id ASC, id ASC', $page, $per_page);
			if (!$post_list)
			{
				return array();
			}
			if ($post_list[0]['id'] == $thread_id)
			{
				$item['thread'] = array_shift($post_list);
			}
			else
			{
				$item['thread'] = array();
			}
			$item['replies'] = $post_list;
		}
		else
		{
			$where = ['thread_id', 'eq', $thread_id];
			$replies = AWS_APP::model()->fetch_page('imageboard_post', $where, 'id ASC', $page, $per_page);
			if (!$replies)
			{
				return array();
			}
			$where = ['id', 'eq', $thread_id];
			$thread = AWS_APP::model()->fetch_row('imageboard_post', $where);
			if (!$thread)
			{
				$thread = array();
			}
			$item['thread'] = $thread;
			$item['replies'] = $replies;
		}

		foreach ($item['replies'] AS $key => $val)
		{
			// 收集用于一次查询
			$uid = intval($val['uid']);
			if ($uid > 0)
			{
				$uids[] = $uid;
			}
		}
		$uid = intval($item['thread']['uid']);
		if ($uid > 0)
		{
			$uids[] = $uid;
		}

		$users = AWS_APP::model('account')->get_user_info_by_uids($uids);
		if (!is_array($users))
		{
			$users = array();
		}

		foreach ($item['replies'] AS $key => &$value)
		{
			self::combine_post_data($value, $users);
		}
		//unset($value);
		self::combine_post_data($item['thread'], $users);

		return $item;
	}

	public static function info($opts)
	{
		$id = intval($opts['id']);
		if ($id < 1)
		{
			return array();
		}

		$where[] = ['thread_id', 'eq', $id];
		if (!$opts['is_mod'])
		{
			$where[] = ['masked', 'eq', 0];
		}

		$item = AWS_APP::model()->fetch_row('imageboard_index', $where);
		if (!$item)
		{
			return array();
		}
		return $item;
	}
}