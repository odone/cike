<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CKApplication extends CKObject {
	
	private $dir;
	
	public function __construct($appDir) {
		if (file_exists ( $appDir )) {
			$appDir = realpath ( $appDir );
		} else {
			Cike::throwException ( 'DirNotExistsException', $appDir );
		}
		
		$this->dir = $appDir;
	}
	
	public function init() {
		static $inited = null;
		
		if ($inited == null) {
			$sessName = Cike::configure ()->get ( 'session.name' );
			if ($sessName != null) {
				session_name ( $sessName );
			}
			
			$inited = true;
		}
	}
	
	public function run() {
		static $running = null;
		$this->init ();
		
		if ($running == null) {
			session_start ();
			
			Cike::log ()->add ( 'begin request' );
			Cike::singleton ( 'CKDispatcher', array (Cike::singleton ( 'CKRouter' ) ) )->dispatch ();
			Cike::log ()->add ( 'end request' );
			
			$running = true;
		}
	}
	
	public function debug() {
		error_reporting ( E_ALL );
		$this->run ();
	}

}

?>