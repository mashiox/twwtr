<?php
/**
 * 
 */
namespace App\Service;

use OpenTHC\Service\Redis;
use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter
{

	static $connection;

	private $res_list;

	public function __construct()
	{

		if (empty(self::$connection)) {
			$redis = Redis::factory();

			// You can find them at: https://dev.twitter.com/apps > your app
			// API Key
			// @see lastpass
			$consumerKey = $redis->get('twitter/consumer-key');
			$consumerSecret = $redis->get('twitter/consumer-secret');
			$accessToken = $redis->get('twitter/access-token');
			$accessTokenSecret = $redis->get('twitter/access-token-secret');
			
			$connection = new TwitterOAuth(
				$consumerKey,
				$consumerSecret,
				$accessToken,
				$accessTokenSecret
			);
			$connection->setDecodeJsonAsArray(true); // Return results as associatve array, rather than stdObject

			$content = $connection->get("account/verify_credentials");

			if (!empty($content)) {
				self::$connection = $connection;
			}
		}
		
	}

	static function factory()
	{
		return new self();
	}

	/** 
	 * Search for tweets
	 */
	function search($arg0)
	{
		$arg1 = array(
			"q" => '',
		);

		$arg = array_merge($arg1, $arg0);
		return self::$connection->get("search/tweets", $arg);
	}

	/**
	 * Post a tweet
	 */
	function post($arg0)
	{
		$arg1 = array(
			'status' => '',
		);

		$arg = array_merge($arg0, $arg1);
		self::$connection->post('statuses/update', $arg);
	}

	/**
	 * Find users
	 */
	function find($arg0)
	{
		$arg1 = array(
			'q' => '',
			// 'page' => null,
			// 'count' => null,
			// 'include_entities' => false,
		);

		$arg = array_merge($arg1, $arg0);
		return self::$connection->get('users/search', $arg);
	}

	/**
	 * Find the users following a user
	 */
	function get_followers($arg0)
	{
		// return $this->get_followers_v0($arg0);
		return $this->get_followers_v1($arg0);
	}

	function get_followers_v0($arg0)
	{
		if (empty($arg0['user_id']) && empty($arg0('screen_name'))) {
			throw new \Exception('Argument must include user_id or screen_name');
		}

		$arg1 = array(
			'user_id' => '',
			'screen_name' => '',
			'cursor' => -1,
			'skip_status' => true,
			// 'include_user_entities' => true,
		);

		$arg = array_merge($arg1, $arg0);
		$res = self::$connection->get('followers/list', $arg);
		// $res = json_decode(json_encode($res), true);
	
		var_dump($res);
		echo "\n\n";

		if (!empty($res['next_cursor_str'])) {
			$arg['cursor'] = $res['next_cursor_str'];
			return $this->get_followers($arg);
		}

		return $res;
	}

	function get_followers_v1($arg0)
	{
		if (empty($arg0['cursor']) || intval($arg0['cursor']) < 0) {
			$this->res_list = array();
		}

		if (empty($arg0['user_id']) && empty($arg0['screen_name'])) {
			throw new \Exception('Argument must include user_id or screen_name [LST-140]');
		}

		$arg1 = array(
			'user_id' => '',
			'screen_name' => '',
			'cursor' => -1,
			'skip_status' => true,
			// 'include_user_entities' => true,
		);

		$arg = array_merge($arg1, $arg0);
		do {
			syslog(LOG_INFO, sprintf("Open Twitter API Request %s-%s", getmypid(), abs($arg['cursor'])));
			$res = self::$connection->get('followers/list', $arg);
			syslog(LOG_INFO, sprintf("Done Twitter API Request %s-%s", getmypid(), abs($arg['cursor'])));

			if (!empty($res['errors'])) {
				if (count($res['errors']) > 1) {
					echo json_encode($res, JSON_PRETTY_PRINT) ."\n";
					exit(1);
				}

				$error = $res['errors'][0];
				switch ($error['code']) {
					case 88:
						// @see https://developer.twitter.com/en/docs/twitter-api/v1/rate-limits
						syslog(LOG_WARNING, "Rate limit encountered");
						sleep(900); // 15min
						break;
	
					default:
						echo json_encode($res, JSON_PRETTY_PRINT) ."\n";
						throw new \Exception("Unknown Twitter error [LST-167]");
						exit(1);
				}
			} else {
				$arg['cursor'] = $res['next_cursor_str'];
				$this->res_list[ $arg['cursor'] ] = $res;
			}

		} while ($arg['cursor'] != 0);

		return $this->res_list;
	}

	/**
	 * Find the users that some user follows
	 */
	function get_friends($arg0)
	{
		// return $this->get_friends_v0($arg0);
		return $this->get_friends_v1($arg0);
	}

	function get_friends_v0($arg0)
	{
		if (empty($arg0['user_id']) && empty($arg0('screen_name'))) {
			throw new \Exception('Argument must include user_id or screen_name');
		}

		$arg1 = array(
			'user_id' => '',
			'screen_name' => '',
			'cursor' => -1,
			'skip_status' => true,
			// 'include_user_entities' => true,
		);

		$arg = array_merge($arg1, $arg0);
		$res = self::$connection->get('friends/list', $arg);
		// $res = json_decode(json_encode($res), true);
	
		if (!empty($res['next_cursor_str'])) {
			$arg['cursor'] = $res['next_cursor_str'];
			return $this->get_friends($arg);
		}

		return $res;

	}

	function get_friends_v1($arg0)
	{
		if (empty($arg0['cursor']) || intval($arg0['cursor']) < 0) {
			$this->res_list = array();
		}

		if (empty($arg0['user_id']) && empty($arg0['screen_name'])) {
			throw new \Exception('Argument must include user_id or screen_name [LST-140]');
		}

		$arg1 = array(
			'user_id' => '',
			'screen_name' => '',
			'cursor' => -1,
			'skip_status' => true,
			// 'include_user_entities' => true,
		);

		$arg = array_merge($arg1, $arg0);
		do {
			syslog(LOG_INFO, sprintf("Open Twitter API Request %s-%s", getmypid(), abs($arg['cursor'])));
			// $res = self::$connection->get('followers/list', $arg);
			$res = self::$connection->get('friends/list', $arg);
			syslog(LOG_INFO, sprintf("Done Twitter API Request %s-%s", getmypid(), abs($arg['cursor'])));

			if (!empty($res['errors'])) {
				if (count($res['errors']) > 1) {
					echo json_encode($res, JSON_PRETTY_PRINT) ."\n";
					exit(1);
				}

				$error = $res['errors'][0];
				switch ($error['code']) {
					case 88:
						// @see https://developer.twitter.com/en/docs/twitter-api/v1/rate-limits
						syslog(LOG_WARNING, "Rate limit encountered");
						sleep(900); // 15min
						break;
	
					default:
						echo json_encode($res, JSON_PRETTY_PRINT) ."\n";
						throw new \Exception("Unknown Twitter error [LST-167]");
						exit(1);
				}
			} else {
				$arg['cursor'] = $res['next_cursor_str'];
				$this->res_list[ $arg['cursor'] ] = $res;
			}

		} while ($arg['cursor'] != 0);

		return $this->res_list;

	}
}
