<?php
/**
 * Twitter
 */
require_once((dirname(dirname(__FILE__)) . '/src/boot.php'));
// require_once((dirname(dirname(__FILE__)) . '/src/lib/cli.php'));

use App\Service\Twitter;

echo "\nTesting \n";

$twtr = Twitter::factory();

$res = $twtr->get_followers([
	'user_id' => '16039297',
]);

file_put_contents('/opt/twitter/dash/var/get-followers-2.json', json_encode($res, JSON_PRETTY_PRINT));

exit(0);