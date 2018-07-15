<?php
namespace SteamGroupAPI\History;

class HistoryItem {
	public $group_id;
	public $history_id;// derived from auto increment
	public $type_id;
	public $title; // derived from event (may remove later)
	public $display_date; // derived from date elements below
	public $year_offset;
	public $month;
	public $day;
	public $time;
	public $source_name = null; // derived from Steam ID
	public $source_steam_id = null;
	public $target_name = null; // derived from Steam ID
	public $target_steam_id = null;
	
	public function __construct() {
		//$this->group_id = $group_id;
		//$this->history_id = $history_id;
		//$this->type_id = $type_id;
	}
	
	public static function compare(HistoryItem $a, HistoryItem $b) {
		if ($a->group_id != $b->group_id) {
			error_log('group_id: ' . $a->group_id . '!=' . $b->group_id);
			return false;
		}
		if ((int) $a->type_id != (int) $b->type_id) {
			error_log('type_id: ' . $a->type_id . ' != ' . $b->type_id);
			return false;
		}
		/*if ((int) $a->year_offset != (int) $b->year_offset) {
			error_log('year_offset: ' . $a->year_offset . ' != ' . $b->year_offset);
			return false;
		}*/
		if ((int) $a->month != (int) $b->month) {
			error_log('month: ' . $a->month . ' != ' . $b->month);
			return false;
		}
		if ((int) $a->day != (int) $b->day) {
			error_log('day: ' . $a->day . ' != ' . $b->day);
			return false;
		}
		if ($a->time != $b->time) {
			error_log('time: ' . $a->time . ' != ' . $b->time);
			return false;
		}
		if ($a->source_steam_id != $b->source_steam_id) {
			error_log('source_steam_id: ' . $a->source_steam_id . ' != ' . $b->source_steam_id);
			return false;
		}
		if ($a->target_steam_id != $b->target_steam_id) {
			error_log('target_steam_id: ' . $a->target_steam_id . ' != ' . $b->target_steam_id);
			return false;
		}
		error_log('Match found!');
		// Cannot print out 4-byte Unicode characters in Command Prompt w/o crash
		//print_r($a);
		//print_r($b);
		return true;
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
