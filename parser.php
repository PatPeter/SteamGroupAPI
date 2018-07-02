<?php

require_once 'vendor/autoload.php';
require_once 'Autoloader.php';

use \Curl\Curl;
use \SteamGroupAPI\Common\Database;
use \SteamGroupAPI\Common\Authenticator;
use \SteamGroupAPI\History\Feed;
use \SteamGroupAPI\History\HistoryItem;

error_reporting(E_ERROR | E_PARSE);

//$content = file_get_contents("Steam Community __ Group __ Universal Gaming Alliance.html");
//echo $content;

$curl = SteamGroupAPI\Common\Authentication::login("", "");
if ($curl === false || !SteamGroupAPI\Common\Authentication::is_logged($curl)) {
	die("Could not log into Steam.");
}

date_default_timezone_set('America/Chicago');

$db = Database::getInstance();
$last_row = $db->getLastRow("103582791430024497");
var_dump($last_row);
if ($last_row === false) {
	//$mysqli = new mysqli("", "", "", "");
	//$result = $mysqli->query('SELECT * FROM group_history WHERE group_id = 103582791430024497 ORDER BY history_id DESC LIMIT 1');
	print_r($result->fetch_assoc());
}

$history_url = 'https://steamcommunity.com/groups/unigamia/history';
$content = $curl->get($history_url);

$doc = new \DOMDocument();
//$doc->loadHtmlFile("Steam Community   Group   Universal Gaming Alliance   Page 13.html");
$doc->loadHTML($content);

//error_log($content);

$feed = new Feed("unigamia", "uga", 2009);
//$history_item = new HistoryItem();
//$page_number = 23;

$last_page = $feed->GetLastPage($doc);
if (!is_numeric($last_page)) {
	die('Last page could not be detected! Exiting.');
}

error_log("Starting from last page..." . $last_page);

$total_items = $feed->GetItemCount($doc);

error_log("With total items..." . $total_items);

// Was breaking parsing below
//$history_items = $feed->ParsePage($doc);

$content_cache = array();
$history_cache = array();

$located = false;
$history_id = $last_row->history_id;
for ($p = $last_page; $p > 0; $p--) {
	$content = $curl->get($history_url . '?p=' . $p);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	error_log('GET URL: ' . $history_url . '?p=' . $p . ' from ' . $last_page);
	$content_cache[$p] = $content;
	//error_log($content);
	
	$doc = new \DOMDocument();
	//$doc->loadHtmlFile("Steam Community   Group   Universal Gaming Alliance   Page 13.html");
	$doc->loadHTML($content);
	$history_items = $feed->ParsePage($doc);
	$history_cache[$p] = $history_items;
	
	for ($i = 0; $i < count($history_items); $i++) {
		$history_item = $history_items[$i];
		if (HistoryItem::compare($history_item, $last_row)) {
			$located = $i;
			break;
		}
	}

	if (is_numeric($located)) {
		error_log("LOCATED");
		error_log($located);
		error_log(count($history_items));
		$history_items = array_slice($history_items, $located + 1);
		$located = true;
	}
	//print_r($history_items);
	
	if ($located === true) {
		for ($i = 0; $i < count($history_items); $i++) {
			$history_item = $history_items[$i];
			$history_item->history_id = ++$history_id;
			$feed->setHistoryID($history_id);
		}
		for ($i = 0; $i < count($history_items); $i++) {
			$history_item = $history_items[$i];
			//error_log("Inseting history item...");
			//print_r($history_item);
			$db->insertHistoryItem($history_item);
		}
	}
}

$feed->Update($doc, $history_items);
