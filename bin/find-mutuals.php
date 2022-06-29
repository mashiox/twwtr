<?php
/**
 * Parse follower and friends json output
 * Find matches
 * Output to JSON
 */
require_once((dirname(dirname(__FILE__)) . '/src/boot.php'));

use App\Service\Twitter;

$arg = [
	'friends:',
	'followers:',
];
$opt = _cli_args($arg);

if (empty($opt['friends'])) {
	// $opt['friends'] = sprintf('/opt/twitter/dash/var/%s-get-friends.json', strtolower($opt['screen_name']));
	throw new Exception('Pass friends file');
}
if (empty($opt['followers'])) {
	throw new Exception('Pass followers file');
}
if (empty($opt['output'])) {
	throw new Exception('Pass output file');
}

$friends = file_get_contents($opt['friends']);
$friends = json_decode($friends, true);
$followers = file_get_contents($opt['followers']);
$followers = json_decode($followers, true);

$mutual_list = [];
foreach ($followers as $req => $res) {
foreach ($res['users'] as $user) {

	foreach ($friends as $req1 => $res1) {
		$mutual = search_users($user, $res1['users']);
		if (!empty($mutual)) {
			$mutual_list[] = $mutual;
		}
	}

}
}

file_put_contents($opt['output'], json_encode($mutual_list, JSON_PRETTY_PRINT));

function search_users($user, $user_list) {
	foreach ($user_list as $user1) {
		if ($user['id'] == $user1['id']) {
			return $user;
		}
	}
	return null;
}
