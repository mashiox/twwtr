<?php
/**
 * Bootstrap umbral money 
*/

use Edoceo\Radix\DB\SQL;

define('APP_NAME', 'Twitter');
define('APP_SITE', getenv('APP_SITE'));
define('APP_ROOT', dirname(dirname(__FILE__)));	
define('APP_SALT', sha1(APP_NAME . APP_SITE . APP_ROOT));

// openlog('umbral', LOG_ODELAY|LOG_PID, LOG_LOCAL0);
openlog('umbral-cli', LOG_ODELAY|LOG_PERROR|LOG_PID, LOG_LOCAL0);

$x = posix_uname();
define('APP_BUILD', sprintf('%08x', crc32($x['nodename'])));

error_reporting(E_ALL & ~ E_NOTICE);

// require_once(APP_ROOT . '/lib/misc.php');

// Vendor Autoload
$cvl = sprintf('%s/src/vendor/autoload.php', APP_ROOT);
if (!is_file($cvl)) {
	die("Run Composer First\n");
}
require_once($cvl);

// Load OpenTHC Common
require_once(sprintf('%s/src/vendor/openthc/common/lib/php.php', APP_ROOT));

$cif = sprintf('%s/etc/app.ini', APP_ROOT);
if (!is_file($cif)) {
	die("Create App Config\n");
}
$cfg = parse_ini_file($cif, true);
$_ENV = array_change_key_case($cfg);

// Session Settings
session_save_path(sprintf("tcp://%s:6379", $cfg['redis']['hostname']));

// @todo setup db
// SQL::init(sprintf('pgsql:host=%s;dbname=%s', $_ENV['database']['hostname'], $_ENV['database']['database']), $_ENV['database']['username'], $_ENV['database']['password']);

// Command Line Configuration
// openlog('umbral-cli', LOG_ODELAY|LOG_PERROR|LOG_PID, LOG_LOCAL0);

// if (is_file(APP_ROOT . '/etc/cli.ini')) {
// $cfg = parse_ini_file(APP_ROOT . '/etc/cli.ini', true);
// $cfg = array_change_key_case($cfg);
// $_ENV = array_merge($_ENV, $cfg);
if (!empty($_ENV['runas']['user'])) {
	$u = posix_getpwnam($_ENV['runas']['user']);
	posix_seteuid($u['uid']);
	posix_setegid($u['gid']);
}
// }

\OpenTHC\Config::init((APP_ROOT));

/**
 * @param $args Array of Long Arguments
 */
function _cli_args($args)
{
	$opt = getopt(null, $args);

	// Handles Parameters Set with no Value
	foreach ($args as $k) {
		if (':' !== substr($k, -1)) {
			if (isset($opt[$k]) && (false === $opt[$k])) {
				$opt[$k] = true;
			}
		}
	}

	return $opt;
}
