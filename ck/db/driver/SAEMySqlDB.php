<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.db.*' );

class SAEMySqlDB extends DBDriver implements IDBDriver {
	
	public function connect($dsn) {
		$this->conn = Cike::singleton ( 'SaeMysql' );
		if ($this->conn) {
			static $setNames = null;
			if ($setNames == null) {
				if (isset ( $dsn ['charset'] ) && $dsn ['charset'] != '') {
					$this->conn
						->setCharset ( $dsn ['charset'] );
				}
				$setNames = true;
			}
			return true;
		}
	}
	
	public function close() {
		if ($this->conn) {
			$this->conn
				->closeDb ();
		}
	}
	
	function startTransaction() {
	}
	
	function commit() {
	}
	
	function rollback() {
	}
	
	public function free() {
	}
	
	public function escapeField($fields) {
		if ($this->conn) {
			return $this->conn
				->escape ( $fields );
		}
	}
	
	public function query($sql) {
		if ($this->conn) {
			$this->conn
				->runSql ( $sql );
			
			if ($this->conn
				->errno () == 0) {
				$this->affected = $this->conn
					->affectedRows ();
				$this->insertId = $this->conn
					->lastId ();
				Cike::log ()->add ( $sql, 'SQL' );
				return true;
			}
		}
		Cike::log ()->add ( $sql, '*SQL' );
		Cike::throwException ( 'SqlQueryException', $sql . $this->conn
			->errmsg (), true );
		return false;
	}
	
	public function getAll($sql) {
		$row = $this->conn
			->getData ( $sql );
		if ($this->conn
			->errno () == 0) {
			return $row;
		}
		Cike::throwException ( 'SqlQueryException', $sql . ' : error' . $this->conn
			->errmsg (), true );
	}
}

?>