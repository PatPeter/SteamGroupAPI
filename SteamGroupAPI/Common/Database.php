<?php
namespace SteamGroupAPI\Common;

use SteamGroupAPI\History\HistoryItem;

class Database {
	private $hostname = '';
	private $port	  = '';
	private $username = '';
	private $password = '';
	private $database = '';
	
	/* @var $instance \PDO */
	private $instance = null;
	
	public function init() {
		$dsn = "mysql:host=$this->hostname;port=$this->port;dbname=$this->database;charset=utf8mb4";
		try {
			$this->instance = new \PDO($dsn, $this->username, $this->password, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
			//$this->instance->query('SELECT 1 + 1');
		} catch (\PDOException $ex) {
			print_r($ex->getTraceAsString());
			die($ex->getMessage());
		}
	}
	
	public function get_last_row($group_id) {
		try {
			/* @var $this->instance \PDO */
			/* @var $stmt \PDOStatement */
			$stmt = $this->instance->prepare('SELECT * FROM group_history WHERE group_id = ? ORDER BY history_id DESC LIMIT 1', array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
			var_dump($stmt);
			if ($stmt === false) {
				error_log($this->instance->errorCode());
				error_log($this->instance->errorInfo());
			}
			$stmt->bindParam(1, $group_id);
			var_dump($stmt);
			$stmt->setFetchMode(\PDO::FETCH_CLASS, '\SteamGroupAPI\History\HistoryItem');
			$stmt->execute([$group_id]);
			$last_row = $stmt->fetch();
			if ($last_row === false) {
				error_log($this->instance->errorCode());
				print_r($this->instance->errorInfo());
			}
			return $last_row;
		} catch (\PDOException $e) {
			print_r($e->getMessage());
			print_r($e->getTraceAsString());
			return null;
		}
	}
}
//Date Created: 2012-06-19 11:57 AM
