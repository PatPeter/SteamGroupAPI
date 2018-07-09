<?php
namespace SteamGroupAPI\Common;

use \Curl\Curl;

class Core {
    public function __construct() {
        
    }

    public function __destruct() {
		
    }
	
	public static function getGroupInfo(\Curl\Curl $curl, $uri) {
		// https://stackoverflow.com/questions/8830599/php-convert-xml-to-json
		$url = "https://steamcommunity.com/groups/$uri/memberslistxml?xml=1";
		$content = $curl->get($url);
		//print_r($content);
		//$xml = simplexml_load_string($xml_string);
		$json = json_encode($content);
		return json_decode($json,true);
		/*$group_info = array();
		$doc = new \DOMDocument();
		$doc->loadXML($content);
		$x = $xmlDoc->documentElement;
		foreach ($x->childNodes as $childNode) {
			switch ($childNode->nodeName) {
				case 'groupID64':
					$group_info['groupID64'] = $childNode->nodeValue;
					break;
				
				case 'groupDetails':
					$group_info['groupDetails'] = array();
					foreach ($childNode->childNodes as $grandchildNode) {
						switch ($grandchildNode->nodeName) {
							case 'groupName':
								
								break;
						}
					}
					break;
				
				case 'memberCount':
					$group_info['memberCount'] = $childNode->nodeValue;
					break;
				
				case 'totalPages':
					$group_info['totalPages'] = $childNode->nodeValue;
					break;
				
				case 'currentPage':
					$group_info['currentPage'] = $childNode->nodeValue;
					break;
				
				case 'startingMember':
					$group_info['startingMember'] = $childNode->nodeValue;
					break;
				
				case 'members':
					foreach ($childNode->childNodes as $grandchildNodes) {
						
					}
					break;
			}
		}*/
	}
}
