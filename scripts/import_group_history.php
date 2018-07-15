<?php

require_once '../vendor/autoload.php';
require_once '../Autoloader.php';

use \SteamGroupAPI\History\Feed;
use \SteamGroupAPI\Common\Authentication;

error_reporting(E_ERROR | E_WARNING | E_PARSE);

if ($argc != 5) {
	error_log('argc was ' . $argc . '. Expected 4.');
	die('Usage: php import_group_history.php <steam name> <steam password> <group URI> <group last history year>');
}

//$content = file_get_contents("Steam Community __ Group __ Universal Gaming Alliance.html");
//echo $content;

$curl = Authentication::login($argv[3], $argv[4]);
if ($curl === false || !Authentication::is_logged($curl)) {
	die("Could not log into Steam.");
}

date_default_timezone_set('America/Chicago');

$feed = new Feed($argv[1], $argv[2]);
$feed->processPages($curl);
