<?php
namespace SteamGroupAPI\History;

class HistoryItem {
	public $id;
	public $img;
	public $title;
	public $date;
	public $source = '';
	public $source_url = '';
	public $target = '';
	public $target_url = '';
	
	public function unsetall() {
		unset($this->id);
		unset($this->img);
		unset($this->title);
		unset($this->date);
		$this->source     = '';
		$this->source_url = '';
		$this->target     = '';
		$this->target_url = '';
	}
	
	public function __toString() {
		$return = "( '$this->id', '$this->img', '$this->title', '$this->date', '$this->source', '$this->source_url', ";
		if ($this->target != '')
			if ($this->target_url != '')
				if ($this->target_url != "NULL")
					$return .= "'$this->target', '$this->target_url' )";
				else
					$return .= "'$this->target', NULL )";
			else
				$return .= "'$this->target', NULL )";
		else
			$return .= 'NULL, NULL )';
		return $return;
	}
}