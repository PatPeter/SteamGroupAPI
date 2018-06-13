<?php
namespace libsteam\group\history;

use libsteam\common\Core;
use libsteam\common\SteamBase;
use libsteam\common\RSAHandler;

class Feed {
	const OFFSET = 2; // Offset for the time zone cURL fetches
	const BR = "<br />\n";
	const PREFIX = "[libsteam] ";
	const USER_AGENT = 'libsteam';
	
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
		'Event Deleted'
	);
	
	private $titles = array(
		'', # 00
		'New Member',           # 01 // Source Only
		'Member Left',          # 02 // Source only
		'New Officer',          # 03 // Target left, Source right
		'Officer Demoted',      # 04 // Target left, Source right
		'Member Dropped',       # 05 // Target left, Source right
		'',                     # 06
		'Invite Sent',          # 07 // Target left, Source right
		'New Event',            # 08 // No URL Target left, Source right
		'Event Updated',        # 09 // No URL Target left, Source right
		'Event Deleted',        # 10 // No URL Target left, Source right
		'Permissions Change',   # 11 // Source only
		'New Announcement',     # 12 // Source only
		'Announcement Updated', # 13 // Source only
		'Announcement Deleted', # 14 // Source only
		'',                     # 15
		'Profile Change',       # 16 // web links     // Source only
		'Profile Change',       # 17 // group details // Source only
		'',                     # 18
		'Group Locked',         # 19 // No Source, no Target
		'',                     # 20
		'',                     # 21
		'Type Changed',         # 22 // public  // Source only
		'Type Changed'          # 23 // private // Source only
	);
	
	private $description = array(
		'',
		' joined group',
		' left group',
		' was promoted to officer by ',
		' was demoted to member by ',
		' was kicked from the group by ',
		'',
		' was sent an invitation by ',
		' event was created by ',
		' event was updated by ',
		' event was deleted by ',
		'group permissions were changed by ',
		'announcement was created by ',
		'announcement was updated by ',
		'announcement was deleted by ',
		'',
		' changed group web links',
		' changed group details',
		'',
		'Modifications to the group have been disabled by Support',
		'',
		'',
		'Group was changed into a public group by ',
		'Group was changed into an invite-only group by '
	);
	
	/**
	 * __construct
	 * Constructor for the class that connects to MySQL,
	 * and initializes cURL.
	 * 
	 * @param $group The group's short URL.
	 * @param $table The table to store and call data from.
	 */
	public function __construct($group,$table) {
		$this->useragent = self::PREFIX . "Spider for Steam group history RSS feed";
		
		if ($group != null && $table != null) {
			/**
			 * Set MySQL Variables
			 */
			$this->connection = new \mysqli(SteamBase::HOST, SteamBase::USERNAME, SteamBase::PASSWORD)
				or exit(self::PREFIX . "Cound not establish a connection to the database. " . 
					"Please check your settings." . self::BR);
			$this->group = $group;
			$this->table = $table;
			$this->checkDatabase();
			
			
			#echo base64_decode($rsakey->publickey_mod);
			
			$rsaKey = $this->getRSAKey();
			if ($rsaKey == null) {
				exit(self::PREFIX . 'RSA not retrieved. Cannot continue.');
			}
			
			$rsa = new RSAHandler();
			$key = base64_encode(serialize(array($rsaKey->publickey_mod, 0, 4096)));
			$cpassword = $rsa->encrypt(SteamBase::STEAM_PASSWORD, $key);
			
			/**
			 * Set cURL Variables and Initialize Cookies
			 */
			$this->curlID = curl_init();
			$this->options = array(
				CURLOPT_URL            => 'https://steamcommunity.com/login/dologin/',
				//CURLOPT_COOKIEJAR      => 'C:\Windows\Temp',
				CURLOPT_RETURNTRANSFER => 1,      // return web page
				CURLOPT_HEADER         => false, // Do not return headers
				CURLOPT_FOLLOWLOCATION => true,  // follow redirects
				CURLOPT_POST           => 1,
				CURLOPT_POSTFIELDS     => "password=" . $cpassword . 
										  "&username=" . SteamBase::STEAM_USERNAME . 
										  "&captchagid=-1" . 
										  "&rsatimestamp=" . $rsaKey->timestamp,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_USERAGENT      => self::USER_AGENT,
				CURLOPT_AUTOREFERER    => true
			);
			curl_setopt_array($this->curlID,$this->options);
			// Execute Opt Array to create cookies
			echo curl_exec($this->curlID);
			$loghead = curl_getinfo($this->curlID);
			$loghead['errno']   = curl_errno($this->curlID);
			$loghead['errmsg']  = curl_error($this->curlID);
			#$loghead['content'] = $login;
			#print_r($loghead);
		}
	}
	
	public function __destruct() { }
	
	public function __toString() {
		echo self::PREFIX . "Feed Exists!" . self::BR;
	}
	
	private function getRSAKey() {
		$curlID = curl_init();
		$options = array(
			CURLOPT_URL            => 'https://steamcommunity.com/login/getrsakey/',
			//CURLOPT_COOKIEJAR      => '%TMP%',
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HEADER         => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_POST           => 1,
			CURLOPT_POSTFIELDS     => "username=" . SteamBase::STEAM_USERNAME,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_USERAGENT      => self::USER_AGENT,
			CURLOPT_AUTOREFERER    => false
		);
		curl_setopt_array($curlID,$options);
		$json = curl_exec($curlID);
		$loghead = curl_getinfo($curlID);
		print_r($json);
		print_r($loghead);
		if (curl_errno($curlID) != 0) {
			die(self::PREFIX . curl_error($curlID));
		}
		return json_decode($json);
	}
	
	public function doLogin() {
		
	}
	
	public function checkDatabase() {
		if ($this->connection->select_db(SteamBase::DATABASE)) {
			// To-do: Remove title.
			$this->connection->query(
				"CREATE TABLE IF NOT EXISTS `" . SteamBase::DATABASE . "`.`$this->table` (
					`id`          MEDIUMINT     NOT NULL ,
					`type`        TINYINT       NOT NULL ,
					`title`       VARCHAR( 32 ) NOT NULL ,
					`date`        DATETIME      NOT NULL ,
					`source`      VARCHAR( 32 ) NOT NULL ,
					`sourceID`    BIGINT        NOT NULL ,
					`target`      TINYTEXT      NULL ,
					`targetID`    BIGINT        NULL ,
					PRIMARY KEY ( `id` )
				) ENGINE = MYISAM
				CHARACTER SET utf8 COLLATE utf8_unicode_ci
				COMMENT = 'RSS History for the " . strtoupper($this->table) . ", Steam group /$this->group/.';")
			or exit(self::PREFIX . "You cannot make the table to store your group's history." . self::BR);
		} else {
			$this->connection->query(
				"CREATE DATABASE IF NOT EXISTS `" . SteamBase::DATABASE . "` " . 
					"DEFAULT CHARACTER SET utf8 COLLATE utf8_bin")
			or exit(self::PREFIX . "You cannot make the database!" . self::BR);
		}
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
	
	private function ParsePage($page,&$id,&$history_item) { // http://www.php.net/manual/en/language.references.pass.php
		curl_setopt($this->curlID, CURLOPT_URL, "https://steamcommunity.com/groups/" . $this->group . "/history?p=" . $page);
		$content = curl_exec($this->curlID);
		//echo $content;
		
		$items = explode('<',$content);
		unset($content);
		
		/**
		 * 
		 * 
		 * 
		 */
		$stack = 0;
		$output = "";
		foreach ($items as $item) {
			// Check for div tag
			if (substr($item,0,3) == "div") {
				// Increment stack if it finds a historyItem or
				// a div within a historyItem
				if ($stack > 0) {
					// Only increment on non-historyItem div tags
					// if they are not inside of historyItems
					$stack++;
				} else {
					$segment = strpos($item,'historyItem');
					// If historyItem is located in the string
					// increase stack and add the item
					if (/*$segment >= 0 && */$segment !== false) {
						$stack++;
						//echo $item . self::BR;
						$output .= "<" . $item;
					}
				}
			// Check for div ending tag, decrement the stack and
			// add the tag to the output
			} elseif (substr($item,0,4) == "/div") {
				if ($stack > 1) {
					// If a div within a historyItem, decrement and discard
					$stack--;
				} elseif ($stack == 1) {
					// If the div corresponds to the historyItem, decrement and close
					$stack--;
					$output .= "<" . $item;
				}
			// If the stack is positive keep adding to the output
			} elseif ($stack > 0) {
				$output .= "<" . $item;
			}
		}
		//echo $output;
		while ($end = strpos($output,'</div>')) {
			$section = substr($output,0,$end + 6);
			$output = substr($output,$end + 6,strlen($output));
			$section = strip_tags($section,'<img><span><a>');
			$section = trim($section);
			
			$history_item->unsetall();
			// Set the id and decrement
			$history_item->id = $id;
			$id--;
			
			// Get the image link
			$imgstart = strpos($section,"src=\"");
			$imgend = strpos($section,".gif\"");
			$img = substr($section,$imgstart + 5,($imgend + 4) - ($imgstart + 5));
			$img = explode("/",$img); # Explode the URL to obtain the image name
			$img = $img[count($img) - 1]; # Get the image name
			//echo $img;
			$history_item->img = ereg_replace("[^0-9]","",$img); # Remove all characters not numbers from image
			//echo $history_item->img;
			
			// Get the title and convert the date
			$section = strip_tags($section,'<a>');
			$section = explode("\r\n",$section);
			$history_item->title = addslashes(trim($section[0]));
			$history_item->date = addslashes($this->convertSteamDate(trim($section[2])));
			$desc = trim($section[4]);
			//echo $desc . self::BR;
			
			// Get the source, source url, target, and target url
			if (in_array($history_item->title,$this->double)) {
				$first = $this->SetNameAndURL($history_item,$desc,"target",0);
				$this->SetNameAndURL($history_item,$desc,"source",$first);
				//echo $second[2] . self::BR;
				//$history_item->desc = addslashes($second[2]);
			} else {
				$first = $this->SetNameAndURL($history_item,$desc,"source",strpos($desc,"<a"));
				//echo $first[2] . self::BR;
				//$history_item->desc = addslashes($first[2]);
			}
			
			/*mysql_query(
				"INSERT INTO `" . $this->database . "`.`$this->table` (
					`id`,
					`type`,
					`title`,
					`date`,
					`source`,
					`sourceID`,
					`target`,
					`targetID`
				) VALUES $history_item;",$this->connection)
				or print("<b>Could not insert row</b>: $history_item because:<br />&nbsp;&nbsp;&nbsp;&nbsp;" . mysql_error() . "<br />\n");
			 */
			
			echo $history_item;
			echo "\n" . self::BR;
		}
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
	private function SetNameAndURL(&$history_item, $desc, $name, $offset) {
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
				$item = $converter->convertCommunityID($item);
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
	private function GetLastPage() {
		curl_setopt($this->curlID, CURLOPT_URL, "https://steamcommunity.com/groups/" . $this->group  . "/history");
		$content = curl_exec($this->curlID);
		$pages = 0;
		$segment = strpos($content,'pageLinks');
		if ($segment === false) {
			return $pages;
		} else {
			// Get the segment containing the pages and trim
			$end = strpos($content,'</div>',$segment);
			$pagelinks = substr($content,$segment,$end - $segment);
			unset($content);
			$pagelinks = trim($pagelinks);
			
			// Remove characters and entities between links
			// and inside unimportant links
			//echo $pagelinks . "<br />\n";
			$pagelinks = str_replace("...","",$pagelinks);
			$pagelinks = str_replace("&nbsp;&nbsp;"," ",$pagelinks);
			$pagelinks = str_replace("&gt;&gt;"," ",$pagelinks);
			$pagelinks = str_replace("&nbsp;"," ",$pagelinks);
			//echo $pagelinks . "<br />\n";
			
			// Remove characters before links
			// To do: Find first <a> in segment and remove loop
			/*$l = strlen($pagelinks);
			for ($i = 0; $i < $l; $i++) {
				$char = substr($pagelinks,$i, 1);
				if ($char == "<") {
					echo $pagelinks . "<br />\n";
					$pagelinks = substr($pagelinks,$i,$l - $i);
					echo $pagelinks . "<br />\n";
					break;
				}
			}*/
			
			// Remove links and trim
			$pagelinks = strip_tags($pagelinks);
			$pagelinks = trim($pagelinks);
			//echo $pagelinks . "<br />\n";
			
			// Change into array
			$pagenumbers = explode(" ",$pagelinks);
			
			// Find largest page
			// To Do: Use last item in array instead of loop?
			//foreach ($pagenumbers as $pagenumber) 
			//	if ($pagenumber > $pages)
			//		$pages = $pagenumber;
			$pages = $pagenumbers[count($pagenumbers) - 1];
		}
		return $pages;
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
		$day = ereg_replace("[^0-9]", "", $expdate[1]);
		if ((int) $day < 10)
			$day = "0" . $day;
		
		# Add month and day to date
		$date .= $month . "-" . $day . " ";
		
		# Set the time, get the meridian, remove the
		# meridian, add 12 hours if necessary, add a
		# zero if necessary, and concatenate
		$time = $expdate[3];
		//$timelen = strlen($time);
		$meridian = ereg_replace("[^a-z]","",$time);
		//$meridian = substr($time,$timelen - 2,$timelen);
		//echo $meridian . "<br />";
		$time = ereg_replace("[a-z]","",$time);
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