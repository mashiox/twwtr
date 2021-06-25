<?php
/**
 * Insert people someone follows into License table, and add relationships to followers table
 */
require_once((dirname(dirname(__FILE__)) . '/src/boot.php'));

use Edoceo\Radix\DB\SQL;
use OpenTHC\Config;

$arg = [
	// 'license:',
	'code:',
	'file:',
];
$opt = _cli_args($arg);

if (empty($opt['code'])) {
	throw new \Exception("Must pass license id");
}

if (empty($opt['file'])) {
	$opt['file'] = sprintf('/opt/twitter/dash/var/%s-get-friends.json', strtolower($opt['code']));
}

$cfg = Config::get('database');
$dsn = sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);

SQL::init($dsn, $cfg['username'], $cfg['password']);

// $User_Src = SQL::fetch_row('SELECT * FROM license WHERE id = :pk', [
// 	':pk' => $opt['license'],
// ]);
$User_Src = SQL::fetch_row('SELECT * FROM license WHERE code = :code', [
	':code' => $opt['code'],
]);

if (empty($User_Src['id'])) {
	throw new \Exception('Error processing %s, Twitter License not found', $opt['code']);
}

$json = file_get_contents($opt['file']);
$res = json_decode($json, true);

// var_dump(_ulid());
// exit(0);

foreach ($res as $cursor => $data) {

	$user_list = $data['users'];
	foreach ($user_list as $user) {

		$sql = 'SELECT id FROM license';
		$sql.= ' WHERE guid = :guid';
		$sql.= ' OR code = :code';
		$x = SQL::fetch_one($sql, [
			':guid' => $user['id_str'],
			':code' => $user['screen_name'],
		]);

		// Insert new Twitter user
		if (empty($x)) {
			$pk = _ulid();
			$sql = 'INSERT INTO license (id, guid, code, name, meta, hash, type)';
			$sql.= ' VALUES(:pk, :guid, :code, :name, :meta, :hash, :type)';
			$arg = [
				':pk' => $pk,
				':guid' => $user['id_str'],
				':code' => $user['screen_name'],
				':name' => $user['name'],
				':meta' => json_encode($user),
				':hash' => md5(json_encode($user)),
				':type' => 'twitter',
			];
			SQL::query($sql, $arg);
			syslog(LOG_INFO, sprintf("[PASS] Insert %s as %s", $user['screen_name'], $pk));

		} else {
			syslog(LOG_WARNING, sprintf("[SKIP] Found %s as %s", $user['screen_name'], $x));
			$pk = $x;
		}

		// Make follower link if it DNE
		$sql = 'SELECT id FROM twitter_follower WHERE license_id_origin = :l0 AND license_id_follow = :l1';
		$res = SQL::fetch_row($sql, [
			':l0' => $User_Src['id'],
			':l1' => $pk,
		]);
		if (empty($res['id'])) {
			$sql = 'INSERT INTO twitter_follower (license_id_origin, license_id_follow)';
			$sql.= 'VALUES(:l0, :l1)';
			SQL::query($sql, [
				':l0' => $User_Src['id'],
				':l1' => $pk,
			]);
			syslog(LOG_INFO, sprintf("[PASS] %s follows %s", $pk, $User_Src['id']));
		}

	}

}
