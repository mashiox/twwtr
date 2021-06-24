<?php
/**
 * Login to twitter api
 */

require_once((dirname(dirname(__FILE__)) . '/src/boot.php'));

use Lyrixx\Twitter\Twitter;
use OpenTHC\Service\Redis;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

echo "\nTesting \n";

$redis = OpenTHC\Service\Redis::factory();

$ulid = _ulid();
$path = sprintf("%s.out", $ulid);
$logger = new Logger('Logger');
$logger->pushHandler(new StreamHandler(sprintf('%s/var/%s', APP_ROOT, $path)), Logger::DEBUG);
// You can find them at: https://dev.twitter.com/apps > your app
// API Key
// @see lastpass
$consumerKey = $redis->get('twitter/consumer-key');
$consumerSecret = $redis->get('twitter/consumer-secret');
$accessToken = $redis->get('twitter/access-token');
$accessTokenSecret = $redis->get('twitter/access-token-secret');

// $twitter = new MyTwitter(
$twitter = new Twitter(
	$consumerKey,
	$consumerSecret,
	$accessToken,
	$accessTokenSecret,
	$endPoint = null,
	$oAuth = null,
	$client = null, 
	$logger
);

// Fetch yours last tweets
// guzzle logging http requests https://michaelstivala.com/logging-guzzle-requests/
$res = $twitter->query('GET', 'statuses/user_timeline');
$tweets = json_decode($response->getBody(), true);


// // old shit
// // Classes for MyTwitter
// use GuzzleHttp\Client;
// use GuzzleHttp\HandlerStack;
// use GuzzleHttp\Middleware;
// use GuzzleHttp\MessageFormatter;
// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;
// class MyTwitter extends Lyrixx\Twitter\Twitter {

// 	function __construct(...$args)
// 	{
// 		$ulid = _ulid();
// 		$path = sprintf("%s.out", $ulid);
// 		$stack = HandlerStack::create();
// 		$logger = new Logger('Logger');
// 		$logger->pushHandler(new StreamHandler(sprintf('%s/var/%s', APP_ROOT, $path)), Logger::DEBUG);
// 		$stack->push(
// 			Middleware::log(
// 				$logger,
// 				new MessageFormatter('{req_body} - {res_body}')
// 			)
// 		);

// 		// $args['client'] = new Client([
// 		$args[7] = new Client([
// 			'handler' => $stack,
// 		]);

// 		parent::__construct(...$args);
// 	}
// }
