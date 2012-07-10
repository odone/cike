<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.db.*' );

abstract class DBDriver extends CKObject implements IDBDriver {
	
	protected $conn;
	protected $query;
	protected $insertId;
	protected $affected;
	
	public function getAffected() {
		return $this->affected;
	}
	
	public function getInsertId() {
		return $this->insertId;
	}
}

?>