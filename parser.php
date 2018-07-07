<?php

require_once 'vendor/autoload.php';
require_once 'Autoloader.php';

use \SteamGroupAPI\Common\Authentication;

error_reporting(E_ERROR | E_WARNING | E_PARSE);

//$content = file_get_contents("Steam Community __ Group __ Universal Gaming Alliance.html");
//echo $content;

$curl = Authentication::login("", "");
if ($curl === false || !Authentication::is_logged($curl)) {
	die("Could not log into Steam.");
}

date_default_timezone_set('America/Chicago');

$feed = new Feed("unigamia", 2009);
$feed->processPages($curl);
