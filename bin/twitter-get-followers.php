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
	$opt['file'] = sprintf('/opt/twitter/dash/var/%s-get-followers.json', strtolower($opt['screen_name']));
}

$twtr = Twitter::factory();

$res = $twtr->get_followers([
	'screen_name' => $opt['code'],
]);

file_put_contents($opt['file'], json_encode($res, JSON_PRETTY_PRINT));

exit(0);