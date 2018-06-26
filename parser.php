<?php

require_once 'vendor/autoload.php';
require_once 'Autoloader.php';

use \Curl\Curl;
use \SteamGroupAPI\History\Feed;
use \SteamGroupAPI\History\HistoryItem;

error_reporting(E_ERROR | E_WARNING | E_PARSE);

$content = file_get_contents("Steam Community __ Group __ Universal Gaming Alliance.html");
//echo $content;

$doc = new \DOMDocument();
$doc->loadHtmlFile("Steam Community __ Group __ Universal Gaming Alliance.html");

$curl = new Curl();

$feed = new Feed("unigamia", "uga", 2014);
//$history_item = new HistoryItem();
//$page_number = 23;

$last_page = $feed->GetLastPage($doc);

error_log("Starting from last page..." + $last_page);

$history_items = $feed->ParsePage($doc, 0, 0);

print_r($history_items);