<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.db.*' );
Cike::import ( '@.ck.db.driver.*' );

abstract class CKModel extends CKObject {
	
	protected $dsnid;
	
	protected $primaryKey;
	protected $tableName;
	protected $fullTableName;
	
	protected $createdField = 'created';
	protected $updatedField = 'updated';
	
	protected $mapping = array ();
	
	/**
	 * @var DBDriver
	 */
	protected $dbo;
	/**
	 * 
	 * @var IDBHelper
	 */
	protected $helper;
	
	public function __construct() {
		$dsns = Cike::configure ()->get ( 'dsn.' . RUN_MODE );
		
		if ($this->dsnid == '') {
			foreach ( $dsns as $_dsn ) {
				$dsn = $_dsn;
				break;
			}
		} else {
			$dsn = $dsns [$this->dsnid];
		}
		
		if ($this->tableName == '' && $this->fullTableName == '') {
			$this->tableName = str_replace ( 'Model', '', $this->getClassName () );
			$this->tableName = strtolower ( $this->tableName );
			$this->fullTableName = (isset ( $dsn ['prefix'] ) ? $dsn ['prefix'] : '') . $this->tableName;
		}
		if ($this->primaryKey == '') {
			$this->primaryKey = 'id';
		}
		if (! isset ( $dsn ['driver'] )) {
			$dsn ['driver'] = 'MySql';
		}
		
		$driver = ucfirst ( $dsn ['driver'] );
		if (SAE_MODE) {
			$driver = 'SAE' . $driver;
		}
		
		$this->dbo = Cike::singleton ( '@.ck.db.driver.' . $driver . 'DB' );
		$this->dbo->connect ( $dsn );
		
		$this->helper = Cike::singleton ( '@.ck.db.helper.' . $driver . 'DBHelper', array ($this->getFullTableName (), $this->dbo ) );
	}
	
	/**
	 * @return string
	 */
	public function getPrimaryKey() {
		return $this->primaryKey;
	}
	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}
	
	/**
	 * @return string
	 */
	public function getFullTableName() {
		return $this->fullTableName;
	}
	
	/**
	 * @return DBDriver
	 */
	public function getDBO() {
		return $this->dbo;
	}
	
	/**
	 * return bool
	 */
	public function validate() {
		return true;
	}
	
	public function beforeCreate($row) {
	}
	
	public function afterCreate($row, $insertId) {
	}
	
	public function beforeUpdate($row, $condition) {
	}
	
	public function afterUpdate($row, $condition) {
	}
	
	public function beforeDelete($condition) {
	}
	
	public function afterDelete($condition) {
	}
	
	public function findCount($condition) {
		$tmp = $this->mapping;
		$this->closeAllMapping ();
		
		$row = $this->find ( 'COUNT(1) AS COUNT', $condition );
		
		$this->mapping = $tmp;
		
		return $row ['COUNT'];
	}
	
	public function find($fields, $condition = '1=1', $orderby = null) {
		$row = $this->findAll ( $fields, $condition, $orderby, 1 );
		if (count ( $row ) > 0) {
			return $row [0];
		}
		return array ();
	}
	
	public function findAll($fields = '*', $condition = '1=1', $orderby = null, $limit = null) {
		$fields = $fields . ',' . $this->primaryKey . ' AS PKV';
		$sql = $this->helper->getSelectSql ( $fields, $condition, $orderby, $limit );
		$row = $this->dbo->getAll ( $sql );
		
		foreach ( $this->mapping as $mapname => $mapping ) {
			if (isset ( $mapping ['enable'] ) && $mapping ['enable'] && isset ( $mapping ['model'] ) && ($mapping ['type'] == '1:n' || $mapping ['type'] = '1:1')) {
				
				static $tbname = null;
				if ($tbname == null) {
					$tbname = strtolower ( $this->tableName );
				}
				
				if ($row) {
					foreach ( $row as & $r ) {
						$pk = isset ( $mapping ['primaryKey'] ) ? $mapping ['primaryKey'] : $tbname . '_id';
						$condition = sprintf ( '%s = %s', $pk, $r ['PKV'] );
						
						$field = isset ( $mapping ['fields'] ) ? $mapping ['fields'] : '*';
						$orderby = isset ( $mapping ['orderby'] ) ? $mapping ['orderby'] : null;
						$condition .= isset ( $mapping ['condition'] ) ? ' AND ' . $mapping ['condition'] : '';
						$limit = isset ( $mapping ['limit'] ) ? $mapping ['limit'] : null;
						
						if ($mapping ['type'] == '1:1') {
							$r [$mapname] = Cike::singleton ( $mapping ['model'] )->find ( $field, $condition, $orderby );
						} else {
							$r [$mapname] = Cike::singleton ( $mapping ['model'] )->findAll ( $field, $condition, $orderby, $limit );
						}
					}
				}
			}
		}
		
		return $row;
	}
	
	public function execute($sql) {
		$this->dbo->query ( $sql );
		return $this->dbo->getAffected ();
	}
	
	public function create($row) {
		if (count ( $row ) == 0)
			return - 1;
		
		$row [$this->createdField] = Util::now ();
		
		if ($this->validate ()) {
			$sql = $this->helper->getInsertSql ( $row );
			
			if ($sql != '') {
				
				$this->beforeCreate ( $row );
				$this->dbo->query ( $sql );
				$insertId = $this->dbo->getInsertId ();
				$this->afterCreate ( $row, $insertId );
				
				return $insertId;
			}
		}
		return - 1;
	}
	
	public function update($row, $condition = '1=0') {
		if (count ( $row ) == 0)
			return - 1;
		
		$row [$this->updatedField] = Util::now ();
		
		if ($this->validate ()) {
			
			$sql = $this->helper->getUpdateSql ( $row, $condition );
			
			if ($sql != '') {
				
				$this->beforeUpdate ( $row, $condition );
				$this->dbo->query ( $sql );
				$affected = $this->dbo->getAffected ();
				$this->afterUpdate ( $row, $condition );
				
				return $affected;
			}
		}
		return - 1;
	}
	
	public function save($row) {
		if (count ( $row ) == 0)
			return - 1;
		
		if (isset ( $row [$this->primaryKey] )) {
			return $this->update ( $row, sprintf ( '%s = %s', $this->primaryKey, $row [$this->primaryKey] ) );
		} elseif (isset ( $row ['PKV'] )) {
			return $this->update ( $row, sprintf ( '%s = %s', $this->primaryKey, $row ['PKV'] ) );
		} else {
			return $this->create ( $row );
		}
	}
	
	public function delete($condition = '1=0') {
		$sql = $this->helper->getDeleteSql ( $condition );
		$this->beforeDelete ( $condition );
		$this->dbo->query ( $sql );
		$this->afterDelete ( $condition );
		
		return $this->dbo->getAffected ();
	}
	
	public function getMetas() {
		return $this->helper->getMetas ();
	}
	
	public function openMapping($mapping) {
		if (isset ( $this->mapping [$mapping] )) {
			$this->mapping [$mapping] ['enable'] = true;
		}
	}
	
	public function closeMapping($mapping) {
		if (isset ( $this->mapping [$mapping] )) {
			$this->mapping [$mapping] ['enable'] = false;
		}
	}
	
	public function openAllMapping() {
		foreach ( $this->mapping as $mapping ) {
			$this->mapping [$mapping] ['enable'] = true;
		}
	}
	
	public function closeAllMapping() {
		foreach ( $this->mapping as $mapping ) {
			$this->mapping [$mapping] ['enable'] = false;
		}
	}
	
	public function __call($method, $args) {
		$matchs = array ();
		//find
		if (preg_match ( '/findBy(\w+)/', $method, & $matchs )) {
			if (count ( $matchs ) > 0) {
				$field = strtolower ( $matchs [1] );
				$args [1] = $field . '=' . $args [1];
				return call_user_func_array ( array ($this, 'findAll' ), $args );
			}
		}
		//update
		if (preg_match ( '/updateBy(\w+)/', $method, & $matchs )) {
			if (count ( $matchs ) > 0) {
				$field = strtolower ( $matchs [1] );
				$args [1] = $field . '=' . $args [1];
				return call_user_func_array ( array ($this, 'update' ), $args );
			}
		}
		//delete
		if (preg_match ( '/deleteBy(\w+)/', $method, & $matchs )) {
			if (count ( $matchs ) > 0) {
				$field = strtolower ( $matchs [1] );
				$args [0] = $field . '=' . $args [0];
				return call_user_func_array ( array ($this, 'delete' ), $args );
			}
		}
	}
}

?>