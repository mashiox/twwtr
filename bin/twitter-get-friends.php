<?php
/**
 * Twitter
 */

require_once((dirname(dirname(__FILE__)) . '/src/boot.php'));

use App\Service\Twitter;

$arg = [
	// 'license:',
	// 'guid:',
	'code:',
	'file:',
];
$opt = _cli_args($arg);

// if (empty($opt['license'])) {
	// if (empty($opt['guid'] || empty($opt['code']))) {
	// throw new \Exception("Must pass license id");
	// }
	// $needle = $opt['license'] ?: $opt['guid'] ?: $opt['code'];
// }
if (empty($opt['code'])) {
	throw new \Exception("Must pass license id");
}
if (empty($opt['file'])) {
	$opt['file'] = '/opt/twitter/dash/var/get-friends.json';
}

var_dump($opt);

$twtr = Twitter::factory();

$res = $twtr->get_friends([
	// 'user_id' => '16039297',
	'screen_name' => $opt['code'],
]);

// var_dump($res);
file_put_contents($opt['file'], json_encode($res, JSON_PRETTY_PRINT));

exit(0);