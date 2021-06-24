<?php
/**
 * Insert followers into License table, and add relationships to followers table
 */
require_once((dirname(dirname(__FILE__)) . '/src/boot.php'));

use Edoceo\Radix\DB\SQL;
use OpenTHC\Config;

$cfg = Config::get('database');
$dsn = sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);

SQL::init($dsn, $cfg['username'], $cfg['password']);

$User = SQL::fetch_row('SELECT * FROM license WHERE id = :pk', [
	':pk' => '01F8WJ0TCF7CCG7NR3BBPF3R20',
]);

$json = file_get_contents('/opt/twitter/dash/var/get-followers-2.json');
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

			// $sql = 'INSERT INTO twitter_follower';
			// $sql.= ' (id, license_id_origin, license_id_follow)';
			// $sql.= ' VALUES(:pk, :l0, :l1)';
			// SQL::query($sql, [
			// 	':pk' => _ulid(),
			// 	':l0' => $x,
			// 	':l1' => $User['id'],
			// ]);
			// syslog(LOG_INFO, sprintf("[PASS] %s follows %s", $pk, $User['id']));

		} else {
			syslog(LOG_WARNING, sprintf("[SKIP] Found %s as %s", $user['screen_name'], $x));
		}

		// Make follower link if it DNE
		$sql = 'SELECT id FROM twitter_follower WHERE license_id_origin = :l0 AND license_id_follow = :l1';
		$res = SQL::fetch_row($sql, [
			':l0' => $pk,
			':l1' => $User['id'],
		]);
		if (empty($res['id'])) {
			$sql = 'INSERT INTO twitter_follower (id, license_id_origin, license_id_follow)';
			$sql.= 'VALUES(:pk, :l0, :l1)';
			SQL::query($sql, [
				':pk' => _ulid(),
				':l0' => $pk,
				':l1' => $User['id'],
			]);
			syslog(LOG_INFO, sprintf("[PASS] %s follows %s", $pk, $User['id']));
		}

	}

}
