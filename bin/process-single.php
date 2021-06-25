<?php
/**
 * Insert followers into License table, and add relationships to followers table
 */
require_once((dirname(dirname(__FILE__)) . '/src/boot.php'));

use Edoceo\Radix\DB\SQL;
use OpenTHC\Config;

$arg = [
	'code:',
];
$opt = _cli_args($arg);

$cfg = Config::get('database');
$dsn = sprintf('pgsql:host=%s;dbname=%s', $cfg['hostname'], $cfg['database']);

SQL::init($dsn, $cfg['username'], $cfg['password']);

$index = 0;
if (empty($opt['code'])) {
	throw new \Exception('Must pass license id');
}

$User = SQL::fetch_row('SELECT * FROM license WHERE code = :code', [
	':code' => $opt['code'],
]);
// do {

	var_dump($User['code']);

	$ulid = $User['id'];
	$file0 = sprintf('/opt/twitter/dash/var/%s-friends.json', $ulid);

	// Get all friends
	$cmd = array();
	$cmd[] = 'php /opt/twitter/dash/bin/twitter-get-friends.php';
	$cmd[] = sprintf('--code="%s"', $User['code']);
	$cmd[] = sprintf('--file="%s"', $file0);
	$cmd[] = '2>&1';
	$cmd[] = sprintf('>>/opt/twitter/dash/var/%s.out', $ulid);
	// $cmd[] = '&';
	var_dump(implode(" ", $cmd));
	// shell_exec(implode(" ", $cmd));

	// Process all friends
	$cmd = array();
	$cmd[] = 'php /opt/twitter/dash/bin/process-friends.php';
	$cmd[] = sprintf('--code="%s"', $User['code']);
	$cmd[] = sprintf('--file="%s"', $file0);
	$cmd[] = '2>&1';
	$cmd[] = sprintf('>>/opt/twitter/dash/var/%s-process.out', $ulid);
	// $cmd[] = '&';
	var_dump(implode(" ", $cmd));
	// shell_exec(implode(" ", $cmd));


	// $ulid = _ulid();
	$file1 = sprintf('/opt/twitter/dash/var/%s-followers.json', $ulid);

	// Get all followers
	$cmd = array();
	$cmd[] = 'php /opt/twitter/dash/bin/twitter-get-followers.php';
	$cmd[] = sprintf('--code="%s"', $User['code']);
	$cmd[] = sprintf('--file="%s"', $file1);
	$cmd[] = '2>&1';
	$cmd[] = sprintf('>>/opt/twitter/dash/var/%s.out', $ulid);
	// $cmd[] = '&';
	var_dump(implode(" ", $cmd));
	// shell_exec(implode(" ", $cmd));

	// Process all followers
	$cmd = array();
	$cmd[] = 'php /opt/twitter/dash/bin/process-followers.php';
	$cmd[] = sprintf('--code="%s"', $User['code']);
	$cmd[] = sprintf('--file="%s"', $file1);
	$cmd[] = '2>&1';
	$cmd[] = sprintf('>>/opt/twitter/dash/var/%s-process.out', $ulid);
	// $cmd[] = '&';
	var_dump(implode(" ", $cmd));
	// shell_exec(implode(" ", $cmd));

	// var_dump($index);
	$index = $index + 1;

	// $User = SQL::fetch_row('SELECT * FROM license ORDER BY ts_created ASC LIMIT 1 OFFSET :idx', [
	// 	':idx' => $index,
	// ]);
// } while (!empty($User));
