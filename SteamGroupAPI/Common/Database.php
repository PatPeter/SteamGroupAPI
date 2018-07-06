<?php
namespace SteamGroupAPI\Common;

use SteamGroupAPI\History\HistoryItem;

class Database {
	private $hostname = 'localhost';
	private $port	  = '3306';
	private $username = '';
	private $password = '';
	private $database = 'uga_libsteam';
	
	/* @var $pdo \PDO */
	public $pdo = null;
	
	/* @var $instance \PDO */
	private static $instance = null;
	
	public static function getInstance() {
		return Database::$instance != null ? Database::$instance : Database::$instance = new Database();
	}
	
	private function __construct() {
		$dsn = "mysql:host=$this->hostname;port=$this->port;dbname=$this->database;charset=utf8mb4";
		try {
			$this->pdo = new \PDO($dsn, $this->username, $this->password, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'));
			$this->pdo->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;');
			//$this->instance->query('SELECT 1 + 1');
		} catch (\PDOException $e) {
			print_r($e->getMessage());
			print_r($e->getTraceAsString());
			die($e->getMessage());
		}
	}
	
	public function getLastRow($group_id) {
		try {
			/* @var $this->instance \PDO */
			/* @var $stmt \PDOStatement */
			$stmt = $this->pdo->prepare('SELECT * FROM group_history WHERE group_id = ? ORDER BY history_id DESC LIMIT 1', array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
			var_dump($stmt);
			if ($stmt === false) {
				error_log($this->pdo->errorCode());
				error_log($this->pdo->errorInfo());
			}
			$stmt->bindParam(1, $group_id);
			var_dump($stmt);
			$stmt->setFetchMode(\PDO::FETCH_CLASS, '\SteamGroupAPI\History\HistoryItem');
			$stmt->execute([$group_id]);
			$last_row = $stmt->fetch();
			if ($last_row === false) {
				error_log($this->pdo->errorCode());
				print_r($this->pdo->errorInfo());
			}
			return $last_row;
		} catch (\PDOException $e) {
			print_r($e->getMessage());
			print_r($e->getTraceAsString());
			die();
		}
	}
	
	public function insertHistoryItem(HistoryItem $history_item) {
		try {
			/* @var $this->instance \PDO */
			/* @var $stmt \PDOStatement */
			$stmt = $this->pdo->prepare('INSERT INTO group_history VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
			//var_dump($stmt);
			if ($stmt === false) {
				error_log($this->pdo->errorCode());
				error_log($this->pdo->errorInfo());
			}
			$stmt->bindParam(1, $history_item->group_id);
			$stmt->bindParam(2, $history_item->history_id);
			$stmt->bindParam(3, $history_item->type_id);
			$stmt->bindParam(4, $history_item->title);
			$stmt->bindParam(5, $history_item->display_date);
			$stmt->bindParam(6, $history_item->year_offset);
			$stmt->bindParam(7, $history_item->month);
			$stmt->bindParam(8, $history_item->day);
			$stmt->bindParam(9, $history_item->time);
			$stmt->bindParam(10, html_entity_decode($history_item->source_name));
			$stmt->bindParam(11, $history_item->source_steam_id);
			$stmt->bindParam(12, strlen($history_item->target_name) > 0 ? html_entity_decode($history_item->target_name) : null);
			$stmt->bindParam(13, $history_item->target_steam_id);
			return $stmt->execute();
		} catch (\PDOException $e) {
			print_r($e->getMessage());
			print_r($e->getTraceAsString());
			die();
		}
	}
}
//Date Created: 2012-06-19 11:57 AM
