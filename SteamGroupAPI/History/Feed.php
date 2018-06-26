<?php
namespace SteamGroupAPI\History;

use SteamGroupAPI\Common\Core;
use SteamGroupAPI\Common\SteamBase;

class Feed {
	const OFFSET = 2; // Offset for the time zone cURL fetches
	const BR = "<br />\n";
	const PREFIX = "[SteamGroupAPI] ";
	
	private $authcode  = ""; // For "enter your code here"
	
	#private $users     = array(
	#							"" => ""
	#					 );
	
	/**
	 * 
	 * Program Modified Connection Variables
	 * 
	 */
	/* @var $connection \MySQLi */
	private $connection;
	private $group = '';
	private $table = '';
	//private $sb = '';
	
	private $curlID = null;
	private $options = null;
	
	/**
	 * 
	 * Date Variables
	 * 
	 */
	private $year   = 2012;
	private $month  = '';
	private $months = array(
		'January' => '01',
		'February' => '02',
		'March' => '03',
		'April' => '04',
		'May' => '05',
		'June' => '06',
		'July' => '07',
		'August' => '08',
		'September' => '09',
		'October' => '10',
		'November' => '11',
		'December' => '12'
	);
	
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
	public function __construct($group, $table, $year) {
		$this->year = $year;
		// TODO: Generate group_id with XML cURL get
	}
	
	public function __destruct() { }
	
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
	public function Update() {
	
	}
	
	public function ParsePage(\DOMDocument $doc, $yearOffset, $lastMonth) { // http://www.php.net/manual/en/language.references.pass.php
		$history_items = array();

		$xpath = new \DOMXPath($doc);
		/* @var $divs \DOMNode */
		$divs = $xpath->query("//*/div[@class='historyItem' or @class='historyItemb']");
		
		//$yearOffset = 0;
		//$lastMonth = 0;
		for ($i = $divs->length - 1; -1 < $i; $i--) {
			/* @var $div \DOMElement */
			$div = $divs->item($i);
			$history_item = new HistoryItem();
			$history_item->group_id = "103582791430024497";
			$history_item->history_id = $i + 1;
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
							$dateParts = date_parse(str_replace(' @ ', ' ', $childNode->textContent));
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
							$history_item->date = ($this->year + $yearOffset) . '-' . $history_item->month . '-' . $history_item->day . ' ' . $history_item->time;
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
							$processedNodes[] = $childNode;
							break;
					}
				}
			}
			foreach ($processedNodes as $childNode) {
				$div->removeChild($childNode);
			}

			//error_log("HISTORY DETAILS: " . trim($doc->saveHtml($div)));

			$history_items[] = $history_item;
		}
		
		return $history_items;
	}
	
	/**
	 * set_name_and_url
	 * 
	 * 
	 * @param history_item Passed reference from program's history_item
	 * @param desc         Description section of row
	 * @param name         Source or Target for saving into history_item
	 * @param offset          
	 */
	private function SetNameAndURL($curl, &$history_item, $desc, $name, $offset) {
		$name_url = $name . "_url";
		if (in_array($history_item->title,$this->events) && $offset == 0) {
			$quote = strpos($desc,"\""); // Find the first quotation mark around the event
			$endquote = strpos($desc,"\"",$quote + 1); // Find the second quotation mark
			$item = substr($desc,$quote + 1,$endquote - ($quote + 1)); // Get the event title
			$newoffset = strpos($desc,"<a",$endquote + 1);
			$history_item->$name_url = "NULL"; // Set the URL to NULL
			$history_item->$name = addslashes($item); // Set the name to the event's name
		} else {
			$quote = strpos($desc,"href=\"",$offset);
			$endquote = strpos($desc,"\"",$quote + 6);
			$item = substr($desc,$quote + 6,$endquote - ($quote + 6));
			if (strpos($desc,"/id/") !== false) {
				$converter = new Core();
				$item = $converter->convertCommunityID($curl, $item);
				unset($converter);
			} else {
				$item = explode("/",$item);
				$item = $item[count($item) - 1];
			}
			$history_item->$name_url = addslashes($item);
			
			$tagend = strpos($desc,"</a>",$offset);
			$tag = substr($desc,$offset,($tagend + 4) - $offset);
			$newoffset = strpos($desc,"<a",$tagend + 4);
			//echo $tag . "<br />\n";
			$history_item->$name = addslashes(strip_tags($tag));
		}
		//echo $item . "<br />\n";
		return $newoffset;
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
	private function GetItemCount() {
		curl_setopt($this->curlID, CURLOPT_URL, "https://steamcommunity.com/groups/" . $this->group  . "/history");
		$content = curl_exec($this->curlID);
		$location = strpos($content,'History Items');
		
		$end = $location - 1;
		while (substr($content,$end,1) != '>')
			$end--;
		$div = substr($content,$end + 1,$location - $end);
		unset($content);
		
		$history_location = 0;
		$items = explode(" ",$div);
		foreach ($items as $item)
			if (is_numeric($item))
				if ($history_location < $item)
					$history_location = $item;
		
		return $history_location;
	}
	
	/**
	 * Converts Steam's date format (without year) to an ISO 8601 format
	 * 
	 * @param $date The date in Steam's format, trimmed
	 * @return The date in ISO 8601 format
	 */
	private function convertSteamDate($steamdate) {
		//echo $steamdate . "<br />\n";
		// Start with year
		$date = $this->year . "-";
		
		// Turn the Steam date into an array
		$expdate = explode(" ",$steamdate);
		
		# Convert the month to a number and
		# import into the class to check for
		# year retrogression
		$month = $this->months[$expdate[0]];
		if ($this->month == "")
			$this->month = $month;
		else if ($this->month != $month)
			$this->month--;
		if ($this->month <= 0) {
			$this->year--;
			$this->month = "";
		}
		
		//echo $this->year . "<br />";
		//echo $this->month . "<br />";
		
		# Remove letters from the day and store it
		# Ensure that it has a leading zero
		$day = preg_replace("[^0-9]", "", $expdate[1]);
		if ((int) $day < 10)
			$day = "0" . $day;
		
		# Add month and day to date
		$date .= $month . "-" . $day . " ";
		
		# Set the time, get the meridian, remove the
		# meridian, add 12 hours if necessary, add a
		# zero if necessary, and concatenate
		$time = $expdate[3];
		//$timelen = strlen($time);
		$meridian = preg_replace("[^a-z]","",$time);
		//$meridian = substr($time,$timelen - 2,$timelen);
		//echo $meridian . "<br />";
		$time = preg_replace("[a-z]","",$time);
		//echo $time . "<br />";
		//$time = substr($time,0,$timelen - 2);
		$time = explode(":",$time);
		//echo $time[0] . "<br />\n";
		if ($meridian == "pm")
			$time[0] += 12;
		elseif ($meridian == "am" && $time[0] == 12)
			$time[0] -= 12;
		//echo $time[0] . "<br />\n";
		$time[0] += self::OFFSET; // Add the hour offset that cURL has
		if ((int) $time[0] >= 24)
			$time[0] -= 24; // If the hour offset goes over 24
		elseif ((int) $time[0] < 10)
			$time[0] = "0" . $time[0]; // Leading zeros
		$time = $time[0] . ":" . $time[1]; // Implode array
		$date .= $time . ":00"; // Add seconds
		//print_r($expdate);
		//echo "<br />\n";
		
		//echo $date . "<br />\n";
		return $date;
	}
}