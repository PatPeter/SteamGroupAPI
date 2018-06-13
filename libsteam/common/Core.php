<?php
namespace libsteam\common;

class Core {
	private $id;
	private $optArray;
	
	public function __construct() {
		$this->id = curl_init();
		$this->optArray = array(
			CURLOPT_RETURNTRANSFER => 1,      // return web page
			CURLOPT_HEADER         => false, // Do not return headers
			CURLOPT_FOLLOWLOCATION => true,  // follow redirects
			CURLOPT_USERAGENT      => 'libsteam', // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
			CURLOPT_TIMEOUT        => 120,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
		);
		curl_setopt_array($this->id,$this->optArray);
	}
	
	public function __destruct() {
		//curl_close($this->id);
	}

	/**
	 * Methods:
	 * ConvertSteamID
	 * ConvertCommunityID
	 * ConvertProfilesURL
	 * ConvertIDURL
	 *
	 * Submethods:
	 * 
	 */

	public function getCommunityID($link) {
		curl_setopt($this->id, CURLOPT_URL, $link);
		$content = curl_exec($this->id);
		curl_close($this->id);
		
		$id_position = strpos($content,"steam://friends/add/");
		$newlink = substr($content,$id_position + 20,17);
		unset($content);
		return $newlink;
	}
}