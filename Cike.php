<?php

define ( 'IN_CIKE', true );
define ( 'DS', DIRECTORY_SEPARATOR );
define ( 'PS', PATH_SEPARATOR );
define ( 'CIKE_VER', '1.0' );
define ( 'CIKE_DIR', dirname ( __FILE__ ) );
define ( 'URL', isset($_SERVER ['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_FILENAME'] );
! defined ( 'ISPOST' ) ? define ( 'ISPOST', isset ( $_SERVER ['REQUEST_METHOD'] ) && $_SERVER ['REQUEST_METHOD'] == 'POST' ? true : false ) : null;
! defined ( 'ISAJAX' ) ? define ( 'ISAJAX', isset ( $_SERVER ['HTTP_X_REQUESTED_WITH'] ) && $_SERVER ['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false ) : null;
/*
 * APP_ID将影响：
 * 缓存、cookie、session、用户密码加密方式
 */
! defined ( 'APP_ID' ) ? define ( 'APP_ID', substr ( md5 ( $_SERVER ['SCRIPT_FILENAME'] ), 0, 10 ) ) : null;
function_exists ( 'get_magic_quotes_gpc' ) ? define ( 'QM_GPC', get_magic_quotes_gpc () ) : null;
function_exists ( 'get_magic_quotes_runtime' ) ? define ( 'QM_RUNTIME', get_magic_quotes_runtime () ) : null;
! defined ( 'APP_DIR' ) ? define ( 'APP_DIR', dirname ( $_SERVER ['SCRIPT_FILENAME'] ) ) : null;
! defined ( 'RUN_MODE' ) ? define ( 'RUN_MODE', 'debug' ) : null;
! defined ( 'SAE_TMP_PATH' ) ? define ( 'SAE_MODE', false ) : define ( 'SAE_MODE', true );

define ( 'RBAC_HAS_ROLE', 'RBAC_HAS_ROLE' );
define ( 'RBAC_NO_ROLE', 'RBAC_NO_ROLE' );
define ( 'RBAC_ALL', 'RBAC_ALL' );

abstract class Cike {
	
	static $objects = array ();
	static $filePaths = array ();
	
	static public function import($package) {
		
		$_package = $package;
		switch (substr ( $_package, 0, 1 )) {
			case '@' :
				$_package = str_replace ( '@', CIKE_DIR, $_package );
				break;
			case '$' :
				$_package = str_replace ( '$', APP_DIR, $_package );
				break;
		}
		
		$_package = str_replace ( '.', DS, $_package );
		
		// 导入所有包
		if (substr ( $_package, - 1 ) == '*') {
			$files = glob ( $_package . '.php' );
		} else {
			$files = glob ( $_package . '.php' );
			
			// 不存在
			if (count ( $files ) == 0) {
				self::throwException ( 'PackageNotFoundException', $package );
			}
		}
		
		if ($files) {
			foreach ( $files as $file ) {
				self::$filePaths [basename ( $file, ".php" )] = $file;
			}
		}
	}
	
	static public function load($filename, $once = false) {
		return ($once ? require_once self::getFilePath ( $filename ) : require self::getFilePath ( $filename ));
	}
	
	static public function getFilePath($filename) {
		if ($filename == '')
			return '';
		if (isset ( self::$filePaths [$filename] )) {
			return self::$filePaths [$filename];
		} else {
			self::throwException ( 'FilePathNotFoundException', $filename, true );
		}
	}
	
	/**
	 * 
	 * @return CKApplication
	 */
	static public function application() {
		return self::singleton ( 'CKApplication', array (APP_DIR ) );
	}
	
	/**
	 * @return CKRBAC
	 */
	static public function RBAC() {
		return self::singleton ( 'CKRBAC' );
	}
	
	/**
	 * 
	 * @return CKConfigureManager
	 */
	static public function configure() {
		return self::singleton ( 'CKConfigureManager' );
	}
	
	/**
	 * 
	 * @return CKLog
	 */
	static public function log() {
		return self::singleton ( 'CKLog' );
	}
	
	/**
	 * 
	 * @return CKCacheManager
	 */
	static public function cache() {
		return self::singleton ( 'CKCacheManager' );
	}
	
	static public function helper($helper, $args = array()) {
		return self::singleton ( $helper . 'Helper', $args );
	}
	
	/**
	 * 
	 * @param string $class
	 * @param array $args
	 * @return CKObject
	 */
	static public function singleton($class, $args = array()) {
		if (strrpos ( $class, '.' ) === false) {
		} else {
			self::import ( $class );
			$class = substr ( $class, strrpos ( $class, '.' ) + 1 );
		}
		
		$class = ucfirst ( $class );
		$hashKeyForClass = $class . '@' . md5 ( serialize ( $args ) );
		
		if (! isset ( self::$objects [$hashKeyForClass] ) || ! is_object ( self::$objects [$hashKeyForClass] )) {
			if (! class_exists ( $class ) && ! interface_exists ( $class )) {
				self::load ( $class, true );
				self::throwException ( 'ClassNotFoundException', $class );
			}
			
			$tmp = new ReflectionClass ( $class );
			if (count ( $args ) == 0) {
				self::$objects [$hashKeyForClass] = $tmp->newInstance ();
			} else {
				self::$objects [$hashKeyForClass] = $tmp->newInstanceArgs ( $args );
			}
		}
		
		return self::$objects [$hashKeyForClass];
	}
	
	/**
	 * 
	 * @param string $exception
	 * @param string $message
	 * @param bool $exit
	 */
	static public function throwException($exception, $message, $exit = false) {
		throw new $exception ( $message );
		if ($exit) {
			die ();
		}
	}
}

function __autoload($class) {
	Cike::load ( $class );
}

function ck_exception_handler($ex) {
	include 'ck/page/exception.php';
}

set_exception_handler ( 'ck_exception_handler' );

Cike::import ( '@.ck.*' );
Cike::import ( '@.ck.exception.*' );
Cike::import ( '@.ck.helper.*' );

if (! file_exists ( APP_DIR )) {
	Cike::singleton ( 'CKGenerator' )->generateApp ();
}

?>