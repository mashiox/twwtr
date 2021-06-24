<?php
/**
 * Twitter
 */

require_once((dirname(dirname(__FILE__)) . '/src/boot.php'));

use App\Service\Twitter;

echo "\nTesting \n";

$twtr = Twitter::factory();

$res = $twtr->search([
	'q' => 'Hello World',
]);

var_dump($res);

$res = $twtr->post([
	'status' => 'quakc quack quakc im an api twitter post',
]);

var_dump($res);



exit(0);