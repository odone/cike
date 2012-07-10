<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.db.*' );
Cike::import ( '@.ck.db.driver.*' );

class MySqlDBHelper implements IDBHelper {
	
	protected $tableName;
	protected $columns;
	
	/**
	 * 
	 * @var MySqlDB
	 */
	protected $dbo;
	
	public function __construct($tableName, $dbo) {
		$this->tableName = '`' . $tableName . '`';
		$this->dbo = $dbo;
		$this->columns = $this->getMetas ();
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/db/IDBHelper::getSelectSql()
	 */
	public function getSelectSql($fields = '*', $condition = '1=1', $orderby = null, $limit = null) {
		return sprintf ( 'SELECT %s FROM %s WHERE %s %s %s', $fields, $this->tableName, $condition, ($orderby == null ? '' : 'ORDER BY ' . $orderby), ($limit == null ? '' : 'LIMIT ' . $limit) );
	
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/db/IDBHelper::getInsertSql()
	 */
	public function getInsertSql($row) {
		$row = $this->filterRow ( $row );
		if (count ( $row ) == 0)
			return '';
		
		$fields = implode ( ',', array_keys ( $row ) );
		$values = '';
		foreach ( $row as $k => $v ) {
			$v = $this->dbo
				->escapeField ( $v );
			$v = "'$v'";
			$values .= ($values == '' ? $v : ',' . $v);
		}
		
		return sprintf ( 'INSERT INTO %s(%s) VALUE(%s)', $this->tableName, $fields, $values );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/db/IDBHelper::getUpdateSql()
	 */
	public function getUpdateSql($row, $condition = '1=0') {
		$row = $this->filterRow ( $row );
		if (count ( $row ) == 0)
			return '';
		
		$sets = '';
		foreach ( $row as $k => $v ) {
			$v = $this->dbo
				->escapeField ( $v );
			$v = "'$v'";
			$sets .= ($sets == '' ? $k . '=' . $v : ',' . $k . '=' . $v);
		}
		
		return sprintf ( 'UPDATE %s SET %s WHERE %s', $this->tableName, $sets, $condition );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/db/IDBHelper::getDeleteSql()
	 */
	public function getDeleteSql($condition = '1=0') {
		return sprintf ( 'DELETE FROM %s WHERE %s', $this->tableName, $condition );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/db/IDBHelper::getMetas()
	 */
	public function getMetas() {
		$cacheName = 'columns.' . $this->tableName;
		$columns = Cike::cache ()->get ( $cacheName );
		if (! $columns) {
			$sql = 'SHOW COLUMNS FROM ' . $this->tableName;
			$columns = $this->dbo
				->getAll ( $sql );
			
			if ($columns) {
				foreach ( $columns as $k => $v ) {
					$columns [$v ['Field']] = array ('match' => '', 'type' => $v ['Type'], 'allowNull' => $v ['Null'] == 'YES' ? true : false, 'default' => $v ['Default'], 'isPKV' => $v ['Key'] == 'PRI' );
					
					$type = $v ['Type'];
					$type = str_replace ( '(', ' ', $type );
					$type = str_replace ( ')', '', $type );
					$columns [$v ['Field']] ['match'] = explode ( ' ', $type );
					//				preg_match('/([a-z]+)([0-9,]+)(\w*)/', $type, $columns[ $v['Field'] ]['match']);
					

					if ($v ['Extra'] == 'auto_increment') {
						$columns [$v ['Field']] ['allowNull'] = true;
					}
					if ($v ['Default'] != '') {
						$columns [$v ['Field']] ['allowNull'] = true;
					}
					
					unset ( $columns [$k] );
				}
			}
			Cike::cache ()->set ( $cacheName, $columns, (RUN_MODE == 'debug' ? 10 : null) );
		}
		return $columns;
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/db/IDBHelper::validate()
	 */
	public function filterRow($row) {
		
		foreach ( $row as $k => $v ) {
			if (! isset ( $this->columns [$k] )) {
				unset ( $row [$k] );
				continue;
			}
		}
		
		if ($this->columns) {
			foreach ( $this->columns as $k => $col ) {
				if ($col ['allowNull'] == false) {
					if (isset ( $row [$k] ) && $row [$k] != '') {
					
					} else {
						Cike::throwException ( 'FieldCouldNotBeEmptyException', $k );
					}
				}
			}
		}
		
		return $row;
	}

}

?>