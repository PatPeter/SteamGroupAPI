<?php
class history_item {
	public $id;
	public $img;
	public $title;
	public $date;
	public $desc;
	public $source = "";
	public $source_url = "";
	public $target = "";
	public $target_url = "";
	
	public function unsetall() {
		unset($this->id);
		unset($this->img);
		unset($this->title);
		unset($this->date);
		unset($this->desc);
		unset($this->source);
		unset($this->source_url);
		/*$this->id         = '';
		$this->img        = '';
		$this->title      = '';
		$this->date       = '';
		$this->desc       = '';*/
		//$this->source     = '';
		//$this->source_url = '';
		$this->target     = '';
		$this->target_url = '';
	}
	
	public function __toString() {
		$return = "( '$this->id', '$this->img', '$this->title', '$this->date', '$this->desc', '$this->source', '$this->source_url', ";
		if ($this->target != "") {
			$return .= "'$this->target', '$this->target_url' )";
		} else {
			$return .= 'NULL, NULL )';
		}
		
		/*return "ID: " . $this->id . 
			"<br />\nImg:        " . $this->img . 
			"<br />\nTitle:      " . $this->title . 
			"<br />\nDate:       " . $this->date . 
			"<br />\nDesc:       " . $this->desc . 
			"<br />\nSource:     " . $this->source . 
			"<br />\nSource URL: " . $this->source_url . 
			"<br />\nTarget:     " . $this->target . 
			"<br />\nTarget URL: " . $this->target_url . 
			"<br />\n";*/
		return $return;
	}
}

class steam_tools {
	private $id;
	private $opt_array;
	
	public function __construct() {
		$this->id = curl_init();
		$this->opt_array = array(
			CURLOPT_RETURNTRANSFER => 1,      // return web page
			CURLOPT_HEADER         => false, // Do not return headers
			CURLOPT_FOLLOWLOCATION => true,  // follow redirects
			CURLOPT_USERAGENT      => 'libsteam', // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
			CURLOPT_TIMEOUT        => 120,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
		);
		curl_setopt_array($this->id,$this->opt_array);
	}
	
	//public function __destruct() {
	//	curl_close($this->id);
	//}

	public function convert_community_id($link) {
		curl_setopt($this->id, CURLOPT_URL, $link);
		$content = curl_exec($this->id);
		curl_close($this->id);
		
		$id_position = strpos($content,"steam://friends/add/");
		$newlink = "http://steamcommunity.com/profiles/" . substr($content,$id_position + 20,17);
		unset($content);
		return $newlink;
	}
}

class feed {
	const host      = 'localhost';
	const username  = '';
	const password  = '';
	const database  = 'rss';
	
	const steamuser = '';
	const steampass = '';
	const offset    = 2;
	
	private $connection;
	private $group = '';
	private $year  = 2010;
	private $month = '';
	private $months = array('January' => '01',
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
	private $double = array(
							'New Officer',
							'Officer Demoted',
							'Member Dropped',
							'Invite Sent'
						);
	/*private $titles = array('', # 00
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
								'',                     # 19
								'',                     # 20
								'',                     # 21
								'Type Changed',         # 22 // public  // Source only
								'Type Changed'          # 23 // private // Source only
							);*/
	/*private $titles = array('New Member' => 1, // Source Only
								'Member Left' => 2, // Source only
								'New Officer' => 3, // Target left, Source right
								'Officer Demoted' => 4, // Target left, Source right
								'Member Dropped' => 5, // Target left, Source right
								// '' => 6,
								'Invite Sent' => 7, // Target left, Source right
								'New Event' => 8, // No URL Target left, Source right
								'Event Updated' => 9, // No URL Target left, Source right
								'Event Deleted' => 10, // No URL Target left, Source right
								'Permissions Change' => 11, // Source only
								'New Announcement' => 12, // Source only
								'Announcement Updated' => 13, // Source only
								'Announcement Deleted' => 14, // Source only
								// '' => 15,
								// Source only
								'Profile Change' => 16, // web links
								// 'Profile Change' => 17, // group details
								// '' => 18, 
								// '' => 19, 
								// '' => 20, 
								// '' => 21, 
								// Source only
								'Type Changed' => 22, // public
								// 'Type Changed' => 23 // private
								);*/
	private $table  = '';
	private $curlid = null;
	private $options = null;
	
	/**
	 * 
	 * 
	 * @param $group The group's short URL.
	 * @param $table The table to store and call data from.
	 */
	public function __construct($group,$table) {
		$this->connection = mysql_connect(self::host,self::username,self::password)
			or die("Could not connect to database server!");
		$this->group = $group;
		$this->table = $table;
		$this->options = array(
			CURLOPT_URL            => "https://steamcommunity.com/",
			//CURLOPT_COOKIEFILE     => "/tmp/cookie.txt",
			CURLOPT_COOKIEJAR      => "/tmp/cookies.txt",
			# Setting CURLOPT_RETURNTRANSFER variable to 1 will force cURL
			# not to print out the results of its query.
			# Instead, it will return the results as a string return value
			# from curl_exec() instead of the usual true/false.
			CURLOPT_RETURNTRANSFER => 1,      // return web page
			CURLOPT_HEADER         => false, // Do not return headers
			CURLOPT_FOLLOWLOCATION => true,  // follow redirects
			CURLOPT_POST           => 1,
			CURLOPT_POSTFIELDS     => "action=doLogin&goto=&qs=&msg=&steamAccountName=" . self::steamuser . "&steamPassword=" . self::steampass,
			//CURLOPT_ENCODING       => "",       // handle compressed
			# SSL Variables
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			//CURLOPT_CAINFO         => "/tmp/steamcommunity.crt",
			CURLOPT_USERAGENT      => 'libsteam', // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
			CURLOPT_TIMEOUT        => 120,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
		);
		$this->curlid = curl_init();
		curl_setopt_array($this->curlid,$this->options);
		// Execute Opt Array to create cookies
		curl_exec($this->curlid);
		//$login   = curl_exec($this->curlid);
		//$logerr  = curl_errno($id);
		//$logmsg  = curl_error($id);
		//$loghead = curl_getinfo($id);
		
		//$loghead['errno']   = $logerr;
		//$loghead['errmsg']  = $logmsg;
		//$loghead['content'] = $login;
	}
	
	public function __destruct() {
		//curl_close($this->curlid);
		//mysql_close($this->connection);
		unset($this->curlid);
		unset($this->options);
		unset($this->table);
		unset($this->group);
	}
	
	public function main($limit) {
		mysql_select_db(self::database,$this->connection)
			or die("Cannot select database!");
		
		echo "<?xml version=\"1.0\" ?>\n";
		echo "<rss version=\"2.0\">\n";
		echo "<channel>\n";
		
		$result = mysql_query("SELECT * FROM $this->table", $this->connection)
			or die(mysql_error());
		$num_rows = mysql_num_rows($result);
		
		while ($limit > 0) {
			$result = mysql_query("SELECT title,description,source_url FROM `$this->table` WHERE id = '$num_rows'")
				or die(mysql_error());
			$row = mysql_fetch_row($result);
			echo "<item>\n";
			echo "<title>$row[0]</title>\n";
			echo "<description>" . htmlspecialchars($row[1]) . "</description>\n";
			echo "<link>$row[2]</link>\n";
			echo "</item>\n";
			
			$num_rows--;
			$limit--;
		}
		
		echo "</channel>";
		echo "</rss>";
	}
	
	public function db_maketable() {
		if (mysql_select_db(self::database,$this->connection)) {
			// !mysql_query("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'rss';")
			// mysql_select_db(self::database) or die("Could not select database!");
			
			mysql_query(
				"CREATE TABLE IF NOT EXISTS `" . self::database . "`.`$this->table` (
					`id`          BIGINT         NOT NULL ,
					`img`         VARCHAR( 96 )  NOT NULL ,
					`title`       VARCHAR( 32 )  NOT NULL ,
					`date`        DATETIME       NOT NULL ,
					`description` VARCHAR( 192 ) NOT NULL ,
					`source`      VARCHAR( 32 )  NOT NULL ,
					`source_url`  VARCHAR( 64 )  NOT NULL ,
					`target`      VARCHAR( 32 )  NULL ,
					`target_url`  VARCHAR( 64 )  NULL ,
					PRIMARY KEY ( `id` )
				) ENGINE = MYISAM COMMENT = 'RSS History for the " . strtoupper($this->group) . "';",$this->connection)
			or print("You cannot make the table!<br />\n");
		} else {
			mysql_query(
				"CREATE DATABASE IF NOT EXISTS `rss`
					DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;",$this->connection)
			or print("You cannot make the database!<br />\n");
		}
	}
	
	public function db_input() {		
		$pages = $this->get_last_page();
		if (!$pages) {
			curl_close($this->curlid);
			mysql_close($this->connection);
			$this->__destruct();
			return;
		}
		
		$id = $this->get_item_count();
		if (!$id) {
			curl_close($this->curlid);
			mysql_close($this->connection);
			$this->__destruct();
			return;
		}
		
		mysql_select_db(self::database,$this->connection)
			or die("Cannot select database!");
		mysql_query("TRUNCATE TABLE `$this->table`",$this->connection);
		
		$page = 1;
		//$pages = 1;
		$history_item = new history_item();
		while ($page <= $pages) {
			//echo "Page $page<br />";
			curl_setopt($this->curlid, CURLOPT_URL, "https://steamcommunity.com/groups/" . $this->group . "/history?p=" . $page);
			$content = curl_exec($this->curlid);
			//$errno   = curl_errno($id);
			//$errmsg  = curl_error($id);
			//$header  = curl_getinfo($id);
			
			//$header['errno']   = $errno;
			//$header['errmsg']  = $errmsg;
			//$header['content'] = $content;
			
			$items = explode('<',$content);
			unset($content);
			$stack = 0;
			$output = "";
			foreach ($items as $item) {
				// Check for div tag
				if (substr($item,0,3) == "div") {
					// Increment stack if it finds another div tag
					if ($stack > 0) {
						$stack++;
					} else {
						$segment = strpos($item,'historyItem');
						// If historyItem is located in the string
						// therefore segment will not be false
						if (/*$segment >= 0 && */$segment !== false) {
							$stack++;
							//echo $item . "<br />";
							$output .= "<" . $item;
						}
					}
				// Check for div ending tag, decrement the stack and
				// add the tag to the output
				} elseif (substr($item,0,4) == "/div") {
					if ($stack > 1) {
						$stack--;
					} elseif ($stack == 1) {
						$stack--;
						$output .= "<" . $item;
					}
				// If the stack is positive keep adding to the output
				} elseif ($stack > 0) {
					$output .= "<" . $item;
				}
			}
			//echo $output;
			do {
				$end = strpos($output,'</div>');
				if ($end === false)
					break;
					
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
				$history_item->img = addslashes($img);
				
				// Get the title and convert the date
				$section = strip_tags($section,'<a>');
				$section = explode("\r\n",$section);
				$history_item->title = addslashes(trim($section[0]));
				$history_item->date = $this->convert_steam_date(addslashes(trim($section[2])));
				$desc = trim($section[4]);
				
				// Get the source, source url, target, and target url
				
				if (in_array($history_item->title,$this->double)) {
					$first = $this->set_name_and_url($history_item,$desc,"target",0,0);
					$second = $this->set_name_and_url($history_item,$first[2],"source",$first[0],$first[1]);
					//echo $second[2] . "<br />\n";
					$history_item->desc = addslashes($second[2]);
				} else {
					$first = $this->set_name_and_url($history_item,$desc,"source",0,0);
					//echo $first[2] . "<br />\n";
					$history_item->desc = addslashes($first[2]);
				}
				
				mysql_query(
					"INSERT INTO `" . self::database . "`.`$this->table` (
						`id`,
						`img`,
						`title`,
						`date`,
						`description`,
						`source`,
						`source_url`,
						`target`,
						`target_url`
					) VALUES $history_item;",$this->connection)
				or die("Could not insert row: $history_item <br />\n");
				
				echo $history_item;
				echo "\n<br />\n";
			} while ($end !== false);
			//echo "Page $page successfully inserted.<br />\n";
			$page++;
		}
		
		//mysql_close($this->connection);
		//curl_close($this->curlid);
	}
	
	private function get_last_page() {
		curl_setopt($this->curlid, CURLOPT_URL, "https://steamcommunity.com/groups/" . $this->group  . "/history");
		$content = curl_exec($this->curlid);
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
			$pagelinks = str_replace("...","",$pagelinks);
			$pagelinks = str_replace("&nbsp;&nbsp;"," ",$pagelinks);
			$pagelinks = str_replace("&gt;&gt;"," ",$pagelinks);
			$pagelinks = str_replace("&nbsp;"," ",$pagelinks);
			
			// Remove characters before links
			// To do: Find first <a> in segment and remove loop
			$l = strlen($pagelinks);
			for ($i = 0; $i < $l; $i++) {
				$char = substr($pagelinks,$i, 1);
				if ($char == "<") {
					$pagelinks = substr($pagelinks,$i,$l - $i);
					break;
				}
			}
			
			// Remove links and trim
			$pagelinks = strip_tags($pagelinks);
			$pagelinks = trim($pagelinks);
			
			// Change into array
			$pagenumbers = explode(" ",$pagelinks);
			
			// Find largest page
			// To Do: Use last item in array instead of loop?
			foreach ($pagenumbers as $pagenumber) 
				if ($pagenumber > $pages)
					$pages = $pagenumber;
		}
		return $pages;
	}
	
	/**
	 * 
	 * 
	 * @return
	 */
	private function get_item_count() {
		curl_setopt($this->curlid, CURLOPT_URL, "https://steamcommunity.com/groups/" . $this->group  . "/history");
		$content = curl_exec($this->curlid);
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
	private function convert_steam_date($steamdate) {
		// Start with year
		$date = $this->year . "/";
		
		// Turn the Steam date into an array
		$expdate = explode(" ",$steamdate);
		
		// Convert the month to a number and
		// import into the class to check for
		// year retrogression
		$month = $this->months[$expdate[0]];
		if ($this->month == "")
			$this->month = $month;
		elseif ($this->month != $month)
			$this->month--;
		if ($this->month <= 0) {
			$this->year--;
			$this->month = "";
		}
		
		//echo $this->year . "<br />";
		//echo $this->month . "<br />";
		
		// Remove letters from the day and store it
		$day = ereg_replace("[^0-9]", "", $expdate[1]);
		if ($day < 10)
			$day = "0" . $day;
		
		// Add month and day to date
		$date .= $month . "/" . $day . " ";
		
		// Set the time, get the meridian, remove the
		// meridian, add 12 hours if necessary, add a
		// zero if necessary, and concatenate
		$time = $expdate[3];
		$timelen = strlen($time);
		$meridian = substr($time,$timelen - 2,$timelen);
		//echo $meridian . "<br />";
		$time = substr($time,0,$timelen - 2);
		$time = explode(":",$time);
		//echo $time[0] . "<br />\n";
		if ($meridian == "pm")
			$time[0] += 12;
		//echo $time[0] . "<br />\n";
		$time[0] += self::offset;
		if ((int) $time[0] < 10)
			$time[0] = "0" . $time[0];
		$time = $time[0] . ":" . $time[1];
		$date .= $time . ":00";
		//print_r($expdate);
		//echo "<br />\n";
		
		return $date;
	}
	
	private function set_name_and_url($history_item,$desc,$name,$offset1,$offset2) {
		$name_url = $name . "_url";
		
		// Find first link
		$a = strpos($desc,"<a",$offset1);
		
		// Find first hyperlink reference
		$href = strpos($desc,"href=\"",$a);
		
		// Find the end of the reference
		$end = strpos($desc,"\"",$href + 6);
		
		// Get the link
		$link = substr($desc,$href + 6,$end - ($href + 6));
		
		// Convert the link from a /id/ to a profiles
		if (strpos($link,"/id/")) {
			$steam_tools = new steam_tools();
			$newlink = $steam_tools->convert_community_id($link);
			unset($steam_tools);
			$history_item->{$name_url} = addslashes($newlink);
		} else {
			$history_item->{$name_url} = addslashes($link);
		}
		//echo $link . "<br />\n";
		
		// Set the supplied history item's *_url value to the link
		
		
		// Find the end of the hyperlink
		$sa = strpos($desc,"</a>",$offset2);
		
		// Get the <a> tag only and remove the tags so that only the name remains
		$history_item->{$name} = addslashes(strip_tags(substr($desc,$a,($sa + 4) - $a)));
		
		$placeholder = "__" . strtoupper($name) . "__";
		$placeholder_url = "__" . strtoupper($name) . "URL__";
		$newdesc = str_replace(stripslashes($history_item->{$name}),$placeholder,$desc);
		$newdesc = str_replace($link,$placeholder_url,$newdesc);
		
		//echo substr($desc,$a,($sa + 4) - $a);
		//echo $link . "<br />\n";
		
		return array($a + 2,$sa + 4,$newdesc);
	}
	
	
}

$feed = new feed("unigamia","uga");
$feed->db_maketable();
$feed->db_input();
$feed->main(15);

?>