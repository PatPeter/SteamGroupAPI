<?php
namespace SteamGroupAPI\History;

use SteamGroupAPI\Common\Core;
use SteamGroupAPI\Common\SteamBase;

class Feed {
	const OFFSET = 2; // Offset for the time zone cURL fetches
	
	/**
	 * 
	 * Program Modified Connection Variables
	 * 
	 */
	private $group = '';
	private $group_id = null;
	private $history_id = 0;
	
	/**
	 * 
	 * Date Variables
	 * 
	 */
	private $year_offset = 0;
	private $first_year = null;
	private $last_month  = null;
	
	/**
	 * 
	 * Text Variables
	 * 
	 */
	private $single = array(
		'New Member',
		'Member Left',
		'Profile Change'
	);
	
	private $double = array(
		'New Officer',
		'Officer Demoted',
		'Member Dropped',
		'Invite Sent',
		'New Event',
		'Event Updated',
		'Event Deleted'
	);
	
	private $events = array(
		'New Event',
		'Event Updated',
		'Event Deleted',
	);
	
	public $titles = array(
		'',						# 00
		'New Member',			# 01 // Source Only
		'Member Left',			# 02 // Source only
		'New Officer',			# 03 // Target left, Source right
		'Officer Demoted',		# 04 // Target left, Source right
		'Member Dropped',		# 05 // Target left, Source right
		'',						# 06
		'Invite Sent',			# 07 // Target left, Source right
		'New Event',			# 08 // No URL Target left, Source right
		'Event Updated',		# 09 // No URL Target left, Source right
		'Event Deleted',		# 10 // No URL Target left, Source right
		'Permissions Change',	# 11 // Source only
		'New Announcement',		# 12 // Source only
		'Announcement Updated',	# 13 // Source only
		'Announcement Deleted',	# 14 // Source only
		'',						# 15
		'Profile Change',		# 16 // web links     // Source only
		'Profile Change',		# 17 // group details // Source only
		'',						# 18
		'Group Locked',			# 19 // No Source, no Target
		'',						# 20
		'',						# 21
		'Type Changed',			# 22 // public  // Source only
		'Type Changed',			# 23 // private // Source only
		'',						# 24
		'',						# 25
		'',						# 26
		'',						# 27
		'New Moderator',		# 28 // Target left, source right
		'',						# 29
		'',						# 30
		'New Join Request',		# 31 // Source only
	);
	
	public $description = array(
		'',																# 00
		' joined group',												# 01
		' left group',													# 02
		' was promoted to officer by ',									# 03
		' was demoted to member by ',									# 04
		' was kicked from the group by ',								# 05
		'',																# 06
		' was sent an invitation by ',									# 07
		' event was created by ',										# 08
		' event was updated by ',										# 09
		' event was deleted by ',										# 10
		'group permissions were changed by ',							# 11
		'announcement was created by ',									# 12
		'announcement was updated by ',									# 13
		'announcement was deleted by ',									# 14
		'',																# 15
		' changed group web links',										# 16
		' changed group details',										# 17
		'',																# 18
		'Modifications to the group have been disabled by Support',		# 19
		'',																# 20
		'',																# 21
		'Group was changed into a public group by ',					# 22
		'Group was changed into an invite-only group by ',				# 23
		'',																# 24
		'',																# 25
		'',																# 26
		'',																# 27
		' was promoted to moderator by ',								# 28
		'',																# 29
		'',																# 30
		' requested to join',											# 31
	);
	
	/**
	 * __construct
	 * Constructor for the class that connects to MySQL,
	 * and initializes cURL.
	 * 
	 * @param $group The group's short URL.
	 * @param $table The table to store and call data from.
	 */
	public function __construct($group, $table, $first_year) {
		$this->first_year = $first_year;
		// TODO: Generate group_id with XML cURL get
	}
	
	public function __destruct() { }
	
	public function getHistoryID() {
		return $this->history_id;
	}
	
	public function setHistoryID($history_id) {
		$this->history_id = $history_id;
		return $this;
	}
	
	public function __toString() {
		echo self::PREFIX . "Feed Exists!" . self::BR;
	}
	
	public function PrintRSS($limit) {
		mysql_select_db($database,$this->connection)
			or die("Cannot select database!");
		echo "<?xml version=\"1.0\" ?>\n";
		echo "<rss version=\"2.0\">\n";
		echo "<channel>\n";
		
		$result = mysql_query("SELECT * FROM $this->table", $this->connection)
			or die(mysql_error());
		$num_rows = mysql_num_rows($result);
		
		while ($limit > 0) {
			$result = mysql_query("SELECT * FROM `$this->table` WHERE id = '$num_rows'")
				or die(mysql_error());
			$row = mysql_fetch_row($result);
			echo "<item>\n";
			$title = $this->titles[$row[1]];
			echo "<title>History Item $row[0] - $title</title>\n";
			if (in_array($title,$this->events)) {
				# <a href=\"http://steamcommunity.com/profiles/$row[5]\">
				echo "<description>$row[3] - \"$row[6]\"" . $this->description[$row[1]] . "$row[4] (http://steamcommunity.com/profiles/$row[5]).</description>\n";
				echo "<link>http://steamcommunity.com/groups/$this->group/events</link>\n";
			} elseif (in_array($title,$this->double)) {
				echo "<description>$row[3] - $row[6] (http://steamcommunity.com/profiles/$row[7])" . $this->description[$row[1]] . "$row[4] (http://steamcommunity.com/profiles/$row[5]).</description>\n";
				echo "<link>http://steamcommunity.com/groups/$this->group/members</link>\n";
			} elseif (in_array($title,$this->single)) {
				echo "<description>$row[3] - $row[4] (http://steamcommunity.com/profiles/$row[5])" . $this->description[$row[1]] . "</description>\n";
				if (strpos($title,"Member") !== false) 
					echo "<link>http://steamcommunity.com/groups/$this->group/members</link>\n";
				else
					echo "<link>http://steamcommunity.com/groups/$this->group</link>\n";
			} else {
				echo "<description>$row[3] - " . $this->description[$row[1]] . "$row[4] (http://steamcommunity.com/profiles/$row[5])</description>\n";
				if (strpos($title,"Announcement") !== false) 
					echo "<link>http://steamcommunity.com/groups/$this->group/announcements</link>\n";
				else
					echo "<link>http://steamcommunity.com/groups/$this->group</link>\n";
			}
			echo "</item>\n";
			
			$num_rows--;
			$limit--;
		}
		
		echo "</channel>";
		echo "</rss>";
	}
	
	/**
	 * input_current
	 * Function to input current history items.
	 * 
	 * 
	 */
	public function InputCurrent() {
		$start_time = time();
		echo "Started timer." . self::BR;
		
		$pages = $this->GetLastPage();
		if ($pages == 0) {
			echo "Could not fetch the number of pages: $pages";
			curl_close($this->curlID);
			$this->connection->close();
			$this->__destruct();
			return;
		}
		
		$id = $this->GetItemCount();
		if ($id == 0) {
			echo "Could not fetch the number of history items: $id";
			curl_close($this->curlID);
			$this->connection->close();
			$this->__destruct();
			return;
		}
		
		//mysql_select_db($this->database,$this->connection)
		//	or die("Cannot select database!");
		//mysql_query("TRUNCATE TABLE `$this->table`",$this->connection);
		$result = mysql_query("SELECT * FROM $this->table", $this->connection)
			or die(mysql_error());
		$num_rows = mysql_num_rows($result);
		//echo $num_rows;
		
		if ($num_rows == 0) {
			$page = 1;
			$history_item = new HistoryItem();
			while ($page <= $pages) {
				echo "Parsing page $page." . self::BR;
				$this->ParsePage($page,$id,$history_item);
				$page++;
			}
		} else {
			echo "Table not empty, cannot input data." . self::BR;
		}
		$end_time = time();
		date_default_timezone_set("America/Chicago");
		# 0000-01-01 00:00:00 - 62167197600; 0001-01-01 00:00:00" - 62135575200
		echo "Parsing the Steam group history page and inserting it into the database took " . date("H:i:s",$end_time - $start_time - 62167197600) . ".";

		mysql_close($this->connection);
		curl_close($this->curlID);
	}
	
	/**
	 * 
	 * 
	 * 
	 * 
	 */
	public function Update(\DOMDocument $doc, $history_items) {
		
	}
	
	public function ParsePage(\DOMDocument $doc, $search_item = null) { // http://www.php.net/manual/en/language.references.pass.php
		$history_items = array();

		$xpath = new \DOMXPath($doc);
		/* @var $divs \DOMNode */
		$divs = $xpath->query("//*/div[@class='historyItem' or @class='historyItemb']");
		
		//$year_offset = 0;
		//$last_month = 0;
		for ($i = $divs->length - 1; -1 < $i; $i--) {
			/* @var $div \DOMElement */
			$div = $divs->item($i);
			$history_item = new HistoryItem();
			$history_item->group_id = "103582791430024497";
			//$history_item->history_id = ++$this->history_id;
			$processedNodes = array();
			/* @var $childNode \DOMElement */
			foreach ($div->childNodes as $childNode) {
				//error_log("CHILD NODE: " . $doc->saveHtml($childNode));
				if ($childNode->attributes != null) {
					//error_log("SWITCH ON CLASS: " . $childNode->attributes->getNamedItem("class")->textContent);

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
							/* @var $dateParts \DateTime */
							$dateParts = \DateTime::createFromFormat('F jS @ g:ia', $childNode->textContent);
							if ($dateParts == false) {
								throw new \Exception();
							}
							$month = $dateParts->format('m');
							$day = $dateParts->format('d');
							if ($month > $this->last_month) {
								$this->last_month = $month;
								error_log('**************************************************');
								error_log('NEW MONTH DETECTED');
								error_log($month . '>' . $this->last_month);
								error_log('**************************************************');
							} else if ($month < $this->last_month) {
								error_log('**************************************************');
								error_log('**************************************************');
								error_log('**************************************************');
								error_log('NEW YEAR DETECTED');
								error_log($month . '<' . $this->last_month);
								error_log($this->year_offset + 1);
								error_log('**************************************************');
								error_log('**************************************************');
								error_log('**************************************************');
								
								$this->year_offset++;
								$this->last_month = $month;
							}
							$history_item->year_offset = $this->year_offset;
							$history_item->month = (int) $month;
							$history_item->day = (int) $day;
							$history_item->time = $dateParts->format("H:i:s");
							
							$dateParts->setDate($this->first_year + $this->year_offset, $month, $day);
							$history_item->display_date = $dateParts->format("Y-m-d H:i:s");
							//$div->removeChild($childNode);
							$processedNodes[] = $childNode;
							break;

						case "historyDash";
							$processedNodes[] = $childNode;
							break;

						case "whiteLink":
							if ($history_item->source_name == '') {
								$history_item->source_name = $childNode->textContent;
								$steam_id = new \SteamID('[U:1:' . $childNode->attributes->getNamedItem("data-miniprofile")->textContent . ']');
								$history_item->source_steam_id =  $steam_id->ConvertToUInt64();
							} else {
								$history_item->target_name = $history_item->source_name;
								$history_item->target_steam_id = $history_item->source_steam_id;
								$history_item->source_name = $childNode->textContent;
								$steam_id = new \SteamID('[U:1:' . $childNode->attributes->getNamedItem("data-miniprofile")->textContent . ']');
								$history_item->source_steam_id = $steam_id->ConvertToUInt64();
							}
							$processedNodes[] = $childNode;
							break;
					}
				}
			}
			foreach ($processedNodes as $childNode) {
				$div->removeChild($childNode);
			}
			
			// Event code
			$startQuote = strpos($childNode->textContent, '"');
			if (strpos($childNode->textContent, '"') !== false) {
				$endQuote = strpos($childNode->textContent, '"', $startQuote + 1);
				$eventName = substr($childNode->textContent, $startQuote + 1, $endQuote - ($startQuote + 1));
				$history_item->target_name = $eventName;
			}
			
			//error_log("HISTORY DETAILS: " . trim($doc->saveHtml($div)));

			$history_items[] = $history_item;
		}
		
		return $history_items;
	}
	
	/**
	 * get_last_page
	 * 
	 * 
	 * @return $pages Number of pages for group history
	 */
	public function GetLastPage(\DOMDocument $doc) {
		$xpath = new \DOMXPath($doc);
		/* @var $elements \DOMNodeList */
		$elements = $xpath->query("//*/a[@class='pagelink']");
		/* @var $pagelink \DOMElement */
		$pagelink = $elements->item($elements->length - 1);
		return $pagelink->textContent;
	}
	
	/**
	 * get_item_count
	 * 
	 * 
	 * @return The number of history items reported by the page.
	 */
	public function GetItemCount(\DOMDocument $doc) {
		$xpath = new \DOMXPath($doc);
		/* @var $elements \DOMNodeList */
		$p = $xpath->query("//*/div[@class='group_paging']/p")->item(0);
		/* @var $pagelink \DOMElement */
		$parts = explode(' ', $p->textContent);
		return $parts[4];
	}
}
