<?php
namespace SteamGroupAPI\History;

//use \Curl\Curl;
use \SteamGroupAPI\Common\Database;
use \SteamGroupAPI\History\Feed;
use \SteamGroupAPI\History\HistoryItem;

class Feed {
	/**
	 * 
	 * Program Modified Connection Variables
	 * 
	 */
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
	public function __construct($group_id, $first_year) {
		$this->group_id = $group_id;
		$this->first_year = $first_year;
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
	
	/**
	 * input_current
	 * Function to input current history items.
	 * 
	 * 
	 */
	public function processPages(\Curl\Curl $curl) {
		$db = Database::getInstance();
		$last_row = $db->getLastHistoryItem($this->group_id);
		var_dump($last_row);
		if ($last_row == false) {
			//$mysqli = new mysqli("", "", "", "");
			//$result = $mysqli->query('SELECT * FROM group_history WHERE group_id = 103582791430024497 ORDER BY history_id DESC LIMIT 1');
			//print_r($result->fetch_assoc());
			error_log('No last row, populating a new group.');
			$last_row = null;
		}

		$history_url = 'https://steamcommunity.com/groups/unigamia/history';
		$curl->setHeader('Content-type', 'text/html; charset=UTF-8');
		//$curl->setOpt(CURLOPT_ENCODING , 'UTF-8');
		$content = $curl->get($history_url);
		//$content = utf8_decode($content);
		//error_log($content);

		$doc = new \DOMDocument('1.0', 'utf-8');
		//$doc->loadHtmlFile("Steam Community   Group   Universal Gaming Alliance   Page 13.html");
		$content  = mb_convert_encoding($content , 'HTML-ENTITIES', 'UTF-8');
		@$doc->loadHTML($content);

		//error_log($content);

		/*
		INSERT INTO uga_libsteam.group_history 
		SELECT 103582791430024497, id, `type`, title, date, 
		DATE_FORMAT(date, '%Y') - 2009, DATE_FORMAT(date, '%m'), DATE_FORMAT(date, '%d'), DATE_FORMAT(date,'%H:%i:%s'),
		source, sourceID, target, targetID
		FROM uga_libsteam.uga;

		SET SQL_SAFE_UPDATES = FALSE;
		UPDATE uga_libsteam.group_history SET
		  source_name = CONVERT(CAST(CONVERT(source_name USING 'latin1') AS BINARY) USING 'utf8mb4'),
		  target_name = CONVERT(CAST(CONVERT(target_name USING 'latin1') AS BINARY) USING 'utf8mb4')
		  WHERE history_id != 440;
		 */
		//$history_item = new HistoryItem();
		//$page_number = 23;

		$last_page = $this->GetLastPage($doc);
		if (!is_numeric($last_page)) {
			die('Last page could not be detected! Exiting.');
		}

		error_log("Starting from last page..." . $last_page);

		$total_items = $this->GetItemCount($doc);

		error_log("With total items..." . $total_items);

		// Was breaking parsing below
		//$history_items = $feed->ParsePage($doc);

		//$content_cache = array();
		//$history_cache = array();

		$history_items = array();
		//$history_id = $last_row->history_id;
		for ($p = $last_page; $p > 0; $p--) {
			//$curl->setHeader('Content-type', 'text/html; charset=UTF-8');
			//$curl->setOpt(CURLOPT_ENCODING , 'UTF-8');
			$content = $curl->get($history_url . '?p=' . $p);
			$content  = mb_convert_encoding($content , 'HTML-ENTITIES', 'UTF-8');
			//$content = utf8_decode($content);
			//$content = iconv('ISO-8859-1', 'UTF-8', $content);
			file_put_contents("page" . $p.'.html', $content);
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
			//$content_cache[$p] = $content;
			error_log($content);

			$doc = new \DOMDocument('1.0', 'utf-8');
			//$doc->loadHtmlFile("Steam Community   Group   Universal Gaming Alliance   Page 13.html");
			@$doc->loadHTML($content);
			//file_put_contents('page' . $p . '.html', $doc->saveHTML());
			//break;

			if (count($history_items) == 0) {
				error_log('HISTORY ITEMS = 0, SEARCH FOR HISTORY ITEM');
				$history_items = $feed->ParsePage($doc, $last_row);
			} else {
				error_log('HISTORY ITEMS = ' . count($history_items) . ', INSERT INTO DATABASE');
				$history_items = $feed->ParsePage($doc);
			}
			//$history_cache[$p] = $history_items;


			/*for ($i = 0; $i < count($history_items); $i++) {
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
			}*/
			//print_r($history_items);

			if (count($history_items) > 0) {
				error_log('INSERTING ' . count($history_items) . ' INTO DATABASE');
				/*for ($i = 0; $i < count($history_items); $i++) {
					$history_item = $history_items[$i];
					$history_item->history_id = ++$history_id;
					$feed->setHistoryID($history_id);
				}*/
				for ($i = 0; $i < count($history_items); $i++) {
					$history_item = $history_items[$i];
					//error_log("Inseting history item...");
					//print_r($history_item);
					$db->insertHistoryItem($history_item);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * 
	 * 
	 * 
	 * 
	 */
	public function Update(\DOMDocument $doc, $history_items) {
		
	}
	
	public function ParsePage(\DOMDocument $doc, &$last_row = null) { // http://www.php.net/manual/en/language.references.pass.php
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
			$history_item->group_id = $this->group_id;
			if ($last_row == null) {
				$history_item->history_id = ++$this->history_id;
			}
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
								error_log('NEW MONTH DETECTED');
								error_log($month . '>' . $this->last_month);
							} else if ($month < $this->last_month) {
								error_log('NEW YEAR DETECTED');
								error_log($month . '<' . $this->last_month);
								error_log($this->year_offset + 1);
								
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
								$history_item->target_name = null;
								$history_item->target_steam_id = null;
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
			//error_log($div->textContent);
			$startQuote = mb_strpos($div->textContent, '"', null, 'UTF-8');
			//error_log('SEARCH FOR EVENT STRING. QUOTE LOCATION? ' . $startQuote);
			if ($startQuote !== false) {
				$endQuote = mb_strpos($div->textContent, '"', $startQuote + 1, 'UTF-8');
				//error_log('SEARCH FOR END OF EVENT STRING. QUOTE LOCATION? ' . $endQuote);
				$eventName = mb_substr($div->textContent, $startQuote + 1, $endQuote - ($startQuote + 1), 'UTF-8');
				//error_log('EVENT NAME DETERMINED: ' . $eventName);
				$history_item->target_name = $eventName;
			}
			
			if ($last_row != null && HistoryItem::compare($history_item, $last_row)) {
				//error_log('MATCH FOUND' . $last_row->history_id);
				$this->history_id = $last_row->history_id; //++
				//error_log('NEXT ID ' . $this->history_id);
				//$history_item->history_id = $this->history_id;
				$last_row = null;
				// Don't add a duplicate row once matched
				continue;
			}
			
			//error_log("HISTORY DETAILS: " . trim($doc->saveHtml($div)));
			
			if ($last_row == null) {
				error_log('LAST ROW IS NULL, ADD HISTORY ITEM ' . $history_item->history_id);
				$history_items[] = $history_item;
			}
		}
		
		return $history_items;
	}
}
