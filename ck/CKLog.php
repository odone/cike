<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CKLog extends CKObject {
	
	protected $logDir;
	protected $maxSize;
	protected $filename;
	protected $level;
	
	public function __construct() {
		if (Cike::configure ()->get ( 'log.enable' )) {
			$this->logDir = Cike::configure ()->get ( 'log.dir' );
			$this->logDir = $this->logDir != null ? $this->logDir : 'logs';
			
			if (file_exists ( $this->logDir )) {
				$this->level = Cike::configure ()->get ( 'log.level' );
				if (! $this->level) {
					$this->level = 'ERROR';
				}
				$this->level = explode ( ',', $this->level );
				
				$this->maxSize = Cike::configure ()->get ( 'log.maxsize' );
				$this->maxSize = $this->maxSize != null ? $this->maxSize : 102400;
				
				$this->filename = $this->logDir . DS . APP_ID . '.access.csv.php';
				
				if (file_exists ( $this->filename ) && filesize ( $this->filename ) > $this->maxSize) {
					$newFilename = str_replace ( '.csv.php', '.(' . date ( 'Y-m-d-H.i.s' ) . ').csv.php', $this->filename );
					rename ( $this->filename, $newFilename );
				}
				
				$this->add ( sprintf ( '%s,%s,%s', $_SERVER ['REQUEST_METHOD'], URL, $_SERVER ['SERVER_PROTOCOL'] ), 'URI' );
				$this->add ( isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : '', 'REFERER' );
				
				$this->add ( $_SERVER ['HTTP_USER_AGENT'], 'AGENT' );
				$this->add ( $_SERVER ['REMOTE_ADDR'], 'IP' );
				$this->add ( var_export ( $_POST, true ), 'POST' );
				$this->add ( var_export ( $_COOKIE, true ), 'COOKIE' );
			}
		}
	}
	
	/**
	 * 添加日志事件
	 * @param string $log 日志事件内容
	 * @param string $type 日志类型
	 */
	public function add($log, $type = 'NOTICE') {
		if (Cike::configure ()->get ( 'log.enable' ) && file_exists ( $this->logDir ) && is_array ( $this->level ) && in_array ( $type, $this->level )) {
			$logs = sprintf ( "\r\n%s,%s,\"%s\"", date ( 'Y-m-d H:i:s' ), $type, $log );
			$fp = @fopen ( $this->filename, 'a+' );
			if ($fp) {
				if (flock ( $fp, LOCK_EX )) {
					clearstatcache ();
					fwrite ( $fp, $logs );
					flock ( $fp, LOCK_UN );
					fclose ( $fp );
				}
			}
		}
	}
}

?>