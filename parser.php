<?php

require_once 'vendor/autoload.php';
require_once 'Autoloader.php';

use \Curl\Curl;
use \SteamGroupAPI\History\Feed;
use \SteamGroupAPI\History\HistoryItem;

error_reporting(E_ERROR | E_PARSE);

$content = file_get_contents("Steam Community __ Group __ Universal Gaming Alliance.html");
//echo $content;

$doc = new DOMDocument();
$doc->loadHtmlFile("Steam Community __ Group __ Universal Gaming Alliance.html");

$curl = new Curl();


$xpath = new DOMXPath($doc);

$feed = new Feed("unigamia", "uga");
//$history_item = new HistoryItem();
//$page_number = 23;
//$feed->ParsePage($curl, $content, $page_number, $history_item);

$history_items = array();

/* @var $divs \DOMNode */
$divs = $xpath->query("//*/div[@class='historyItem' or @class='historyItemb']");

$baseYear = 2014;
$yearOffset = 0;
$lastMonth = 0;
for ($i = $divs->length - 1; -1 < $i; $i--) {
	/* @var $div \DOMElement */
    $div = $divs->item($i);
    $history_item = new HistoryItem();
	$history_item->group_id = 103582791430024497;
	$history_item->history_id = $i;
	$processedNodes = array();
    /* @var $childNode \DOMElement */
    foreach ($div->childNodes as $childNode) {
        error_log("CHILD NODE: " . $doc->saveHtml($childNode));
        if ($childNode->attributes != null) {
			error_log("SWITCH ON CLASS: " . $childNode->attributes->getNamedItem("class")->textContent);
			
            switch ($childNode->attributes->getNamedItem("class")->textContent) {
                case "historyIcon":
                    $img = $childNode->childNodes->item(0);
                    $history_item->type_id = str_replace(array('https://steamcommunity-a.akamaihd.net/public/images/skin_1/HistoryAction', 'b.gif', '.gif',), array('', '', '',), $img->attributes->getNamedItem("src")->textContent);
					$processedNodes[] = $childNode;
					//$div->removeChild($childNode);
                    break;
                
                case "historyShort":
                    $history_item->title = $childNode->textContent;
					//$div->removeChild($childNode);
					$processedNodes[] = $childNode;
                    break;
                
                case "historyDate":
					$noAt = str_replace(' @ ', ' ', $childNode->textContent);
					error_log($noAt);
					$dateParts = date_parse($noAt);
					print_r($parsedDate);
					if ($dateParts['month'] > $lastMonth) {
						$lastMonth = $dateParts['month'];
					} else if ($dateParts['month'] < $lastMonth) {
						$yearOffset++;
						$lastMonth = $dateParts['month'];
					}
					$history_item->year_offset = $yearOffset;
					$history_item->month = $dateParts['month'];
					$history_item->day = $dateParts['day'];
                    $history_item->time = $dateParts['hour'] . ':' . $dateParts['minute'] . ':' . $dateParts['second'];
					$history_item->date = ($baseYear + $yearOffset) . '-' . $history_item->month . '-' . $history_item->day . ' ' . $history_item->time;
					//$div->removeChild($childNode);
					$processedNodes[] = $childNode;
                    break;
                
				case "historyDash";
					$processedNodes[] = $childNode;
					break;
				
				case "whiteLink":
					if ($history_item->source == '') {
						$history_item->source = $childNode->textContent;
						$history_item->source_url = $childNode->attributes->getNamedItem("data-miniprofile")->textContent;
					} else {
						$history_item->target = $history_item->source;
						$history_item->target_url = $history_item->source_url;
						$history_item->source = $childNode->textContent;
						$history_item->source_url = $childNode->attributes->getNamedItem("data-miniprofile")->textContent;
					}
					break;
            }
        }
    }
	foreach ($processedNodes as $childNode) {
		$div->removeChild($childNode);
	}
	
	error_log("HISTORY DETAILS: " . trim($doc->saveHtml($div)));
	
    $history_items[] = $history_item;
}

print_r($history_items);