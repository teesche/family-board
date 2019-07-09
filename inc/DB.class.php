<?php

class DB {
	static private $dbPrivileges;
	static private $initialized = false;
	static private $connection = null;

	private function __construct() { }

	static public function init() {
		if(self::$initialized) return;
		self::$dbPrivileges = array(DB_HOST, DB_USER, DB_PASS, DB_BASE);
		self::$initialized = true;
	}

	static public function getConnection() {
		if(self::$connection === null) {
			try {
				self::$connection = new PDO('mysql:host=' . self::$dbPrivileges[0] . ';dbname=' . self::$dbPrivileges[3],
					self::$dbPrivileges[1],
					self::$dbPrivileges[2],
					array(PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8mb4'));
			} catch(Exception $e) {
				exit('This database error occured: ' . $e->getMessage());
			}
			if(!self::$connection) exit('An unknown database error occured.');
		}
		return self::$connection;
	}

	static public function fetchAll($query, $values = array()) {
		if(!isset($query) || (isset($values) && !is_array($values))) return;
		$sth = self::$connection->prepare($query);
		$sth->execute($values);
		$results = $sth->fetchAll();
		return $results;
	}

	static public function fetchRow($query, $values = array()) {
		if(!isset($query) || (isset($values) && !is_array($values))) return;
		$sth = self::$connection->prepare($query);
		$sth->execute($values);
		$results = $sth->fetch(PDO::FETCH_ASSOC);
		return $results;
	}

	static public function fetchSingle($query, $values = array()) {
		if(!isset($query) && !is_array($values)) return;
		$sth = self::$connection->prepare($query);
		$sth->execute($values);
		$result = $sth->fetch();
		return $result[0];
	}

	static public function insert($query, $values) {
		if(!isset($query) && !is_array($values)) return;
		$sth = self::$connection->prepare($query);
		$affected = $sth->execute($values);
		return self::$connection->lastInsertId();
	}

	static public function update($query, $values) {
		if(!isset($query) && !is_array($values)) return;
		$sth = self::$connection->prepare($query);
		$affected = $sth->execute($values);
		return $affected;
	}

	static public function delete($query, $values) {
		if(!isset($query) && !is_array($values)) return;
		$sth = self::$connection->prepare($query);
		$affected = $sth->execute($values);
		return $affected;
	}
}
