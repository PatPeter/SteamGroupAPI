<?php
namespace SteamGroupAPI\History;

class HistoryItem {
	public $group_id;
	public $history_id;
	public $type_id;
	public $title;
	public $date;
	public $year_offset;
	public $month;
	public $day;
	public $time;
	public $source = '';
	public $source_steam_id = '';
	public $target = '';
	public $target_steam_id = '';
	
	public function __construct() {
		//$this->group_id = $group_id;
		//$this->history_id = $history_id;
		//$this->type_id = $type_id;
	}
	
	public function __toString() {
		$s = "";
		
		foreach ($this as $value) {
			if ($value == null) {
				$value = 'NULL';
			}
			
			if (strlen($s) == 0) {
				$s .= '( "' . $value . '"';
			} else {
				$s .= ' , "' . $value . '"';
			}
		}
		$s .= ' )';
		
		return $s;
	}
}
