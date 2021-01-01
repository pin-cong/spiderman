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

class ib_post
{
	public static function get($id)
	{
		$id = intval($id);
		if ($id < 1)
		{
			return array();
		}

		$where = ['id', 'eq', $id];
		$post = AWS_APP::model()->fetch_row('imageboard_post', $where);
		if (!$post)
		{
			return array();
		}
		return $post;
	}

	public static function update($id, $opts)
	{
		$id = intval($id);
		if ($id < 1)
		{
			return;
		}

		$where = ['id', 'eq', $id];
		AWS_APP::model()->update('imageboard_post', array(
			'uid' => intval($opts['uid']),
			'subject' => htmlspecialchars($opts['subject']),
			'body' => htmlspecialchars($opts['body']),
			'file' => htmlspecialchars($opts['file']),
			'file_type' => intval($opts['file_type']),
			'time' => fake_time(),
		), $where);
	}

	public static function batch($ids, $status)
	{
		if (!is_array($ids) OR !$ids OR count($ids) > 1000)
		{
			return;
		}
		$ids = array_map('intval', $ids);

		$where = ['id', 'in', $ids];
		AWS_APP::model()->update('imageboard_post', array(
			'status' => intval($status),
		), $where);

		$where = ['thread_id', 'in', $ids];
		AWS_APP::model()->update('imageboard_index', array(
			'status' => intval($status),
		), $where);
	}

	public static function get_last_post_id($thread_id)
	{
		$thread_id = intval($thread_id);

		$where = [
			['thread_id', 'eq', $thread_id],
			'or',
			['id', 'eq', $thread_id],
		];
		$order = 'id DESC';

		$id = AWS_APP::model()->fetch_one('imageboard_post', 'id', $where, $order);

		return intval($id);
	}

	public static function get_recent_reply_ids($thread_id, $limit)
	{
		$limit = intval($limit);
		if ($limit < 1)
		{
			return array();
		}
		$thread_id = intval($thread_id);

		$where = ['thread_id', 'eq', $thread_id];

		$reply_ids = AWS_APP::model()->fetch_column('imageboard_post', 'id', $where, 'id DESC', $limit);
		if (!$reply_ids)
		{
			return array();
		}

		return array_reverse($reply_ids);
	}

	public static function upsert_index($thread_id, $recent_replies_per_thread)
	{
		$thread_id = intval($thread_id);

		$last_post_id = self::get_last_post_id($thread_id);
		if (!$last_post_id)
		{
			return;
		}

		$where = ['thread_id', 'eq', $thread_id];

		$data = array(
			'reply_count' => AWS_APP::model()->count('imageboard_post', $where),
			'recent_reply_ids' => implode(',', self::get_recent_reply_ids($thread_id, $recent_replies_per_thread)),
			'last_post_id' => $last_post_id,
		);

		if (AWS_APP::model()->fetch_row('imageboard_index', $where))
		{
			AWS_APP::model()->update('imageboard_index', $data, $where);
		}
		else
		{
			$data['thread_id'] = $thread_id;
			AWS_APP::model()->insert('imageboard_index', $data);
		}
	}

	public static function thread($opts)
	{
		$post_id = AWS_APP::model()->insert('imageboard_post', array(
			'uid' => intval($opts['uid']),
			'subject' => htmlspecialchars($opts['subject']),
			'body' => htmlspecialchars($opts['body']),
			'file' => htmlspecialchars($opts['file']),
			'file_type' => intval($opts['file_type']),
			'time' => fake_time(),
		));
		if (!$post_id)
		{
			return 0;
		}

		$index_id = AWS_APP::model()->insert('imageboard_index', array(
			'thread_id' => $post_id,
			'last_post_id' => $post_id,
		));
		if (!$index_id)
		{
			return 0;
		}

		return $post_id;
	}

	public static function reply($opts)
	{
		$thread_id = intval($opts['thread_id']);

		$post_id = AWS_APP::model()->insert('imageboard_post', array(
			'thread_id' => $thread_id,
			'uid' => intval($opts['uid']),
			'subject' => htmlspecialchars($opts['subject']),
			'body' => htmlspecialchars($opts['body']),
			'file' => htmlspecialchars($opts['file']),
			'file_type' => intval($opts['file_type']),
			'time' => fake_time(),
		));
		if (!$post_id)
		{
			return 0;
		}

		$recent_reply_ids = self::get_recent_reply_ids($thread_id, $opts['recent_replies_per_thread']);
		$data = array(
			'reply_count' => AWS_APP::model()->count('imageboard_post', ['thread_id', 'eq', $thread_id]),
			'recent_reply_ids' => implode(',', $recent_reply_ids),
		);
		if (!$opts['sage'])
		{
			$data['last_post_id'] = $post_id;
		}

		AWS_APP::model()->update('imageboard_index', $data, ['thread_id', 'eq', $thread_id]);

		return $post_id;
	}

}