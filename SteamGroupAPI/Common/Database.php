<?php
namespace SteamGroupAPI\Common;

class Database {
	private $hostname = '';
	private $port	  = '';
	private $username = '';
	private $password = '';
	private $database = '';
	
	private $instance = null;
	
	public function __construct() {
		$dsn = "mysql:host=$this->host;port=$this->port;dbname=$this->database;charset=utf8mb4";
		try {
			$this->instance = new \PDO($dsn, $this->username, $this->password, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
		} catch (\PDOException $ex) {
			print_r($ex);
		}
	}
	
	public function get_last_row() {
		try {
			$stmt = $this->instance->query('SELECT * FROM group_history WHERE group_id = 103582791430024497 ORDER BY history_id DESC LIMIT 1');
			$stmt->bindValue(1, 103582791430024497);
			$last_row = $stmt->fetch();
			return $last_row;
		} catch (\PDOException $e) {
			print_r($e->getMessage());
			print_r($e->getTraceAsString());
			return null;
		}
	}
}
//Date Created: 2012-06-19 11:57 AM
