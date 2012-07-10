<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.db.*' );

class MySqlDB extends DBDriver implements IDBDriver {
	
	public function connect($dsn) {
		if (isset ( $dsn ['host'], $dsn ['user'], $dsn ['pass'] )) {
			$this->conn = mysql_connect ( $dsn ['host'] . (isset ( $dsn ['port'] ) ? ':' . $dsn ['port'] : ''), $dsn ['user'], $dsn ['pass'] );
			if ($this->conn) {
				static $setNames = null;
				if ($setNames == null) {
					if (isset ( $dsn ['charset'] ) && $dsn ['charset'] != '') {
						$this->query ( 'SET NAMES ' . $dsn ['charset'] );
					}
					$setNames = true;
				}
				if (isset ( $dsn ['name'] )) {
					return mysql_select_db ( $dsn ['name'], $this->conn ) != null;
				}
				return true;
			}
			Cike::throwException('DBConnectException', $dsn['host'], true);
		}
		Cike::throwException('DBConfigException', var_export($dsn, true) );
		return false;
	}
	
	public function close() {
		if ($this->conn) {
			mysql_close ( $this->conn );
		}
	}
	
	function startTransaction() {
		$this->query ( 'START TRANSACTION' );
	}
	
	function commit() {
		$this->query ( 'COMMIT' );
	}
	
	function rollback() {
		$this->query ( 'ROLLBACK' );
	}
	
	public function free() {
		if ($this->query) {
			mysql_free_result ( $this->query );
		}
	}
	
	public function escapeField($fields) {
		if ($this->conn) {
			return mysql_real_escape_string ( $fields, $this->conn );
		} else {
			return mysql_escape_string ( $fields );
		}
	}
	
	public function query($sql) {
		if ($this->conn) {
			$this->query = mysql_query ( $sql, $this->conn );
			if ($this->query) {
				$this->affected = mysql_affected_rows ( $this->conn );
				$this->insertId = mysql_insert_id ( $this->conn );
				Cike::log ()->add ( $sql, 'SQL' );
				return true;
			}
		}
		Cike::log ()->add ( $sql, '*SQL' );
		Cike::throwException ( 'SqlQueryException', $sql . ' error:' . mysql_error (), true );
		return false;
	}
	
	public function getAll($sql) {
		$this->query ( $sql );
		
		$rows = array ();
		if ($this->affected > 0) {
			while ( ($row = mysql_fetch_assoc ( $this->query )) != null ) {
				$rows [] = $row;
			}
		}
		$this->free ();
		
		return $rows;
	}
}

?>