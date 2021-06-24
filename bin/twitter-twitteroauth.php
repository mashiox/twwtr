<?php
/**
 * Twitter
 */

require_once((dirname(dirname(__FILE__)) . '/src/boot.php'));

use Abraham\TwitterOAuth\TwitterOAuth;
use OpenTHC\Service\Redis;

echo "\nTesting \n";

$redis = OpenTHC\Service\Redis::factory();

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
$content = $connection->get("account/verify_credentials");

var_dump($content);

exit(0);