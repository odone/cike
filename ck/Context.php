<?php

! defined ( 'IN_CIKE' ) ? null : '!';

abstract class Context extends CKObject {
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $key
	 * @param string $dataType
	 * @param mixed $defaultValue
	 */
	static public function request($key, $dataType = 's', $defaultValue = null) {
		if (isset ( $_REQUEST [$key] )) {
			return self::convertValue ( $_REQUEST [$key], $dataType );
		}
		return $defaultValue;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param string $dataType
	 * @param mixed $defaultValue
	 */
	static public function get($key, $dataType = 's', $defaultValue = null) {
		if (isset ( $_GET [$key] )) {
			return self::convertValue ( $_GET [$key], $dataType );
		}
		return $defaultValue;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param string $dataType
	 * @param mixed $defaultValue
	 */
	static public function post($key, $dataType = 's', $defaultValue = null) {
		if (isset ( $_POST [$key] )) {
			return self::convertValue ( $_POST [$key], $dataType );
		}
		return $defaultValue;
	}
	
	/**
	 * 
	 * @param string $key
	 * @param string $value
	 * @param int $expire
	 * @param string $path
	 * @param string $domain
	 * @return mixed
	 */
	static public function cookie($key, $value = '',$expire = '', $path = '', $domain = ''){
		static $prefix = Cike::configure()->get('cookie.prefix');
		$keyName = $prefix . $keyName;
		
		if ($value == '') {
			return isset($_COOKIE[$keyName]) ? $_COOKIE[$keyName] : null;
		}
		return setcookie($keyName, $value, $expire, $path, $domain);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param mixed $value
	 * @param string $type
	 */
	static private function convertValue($value, $type) {
		if ($value == null)
			return null;
		
		$type = strtolower ( $type );
		switch ($type) {
			case 'i' :
				$value = ( int ) $value;
				break;
			case 'f' :
				$value = ( float ) $value;
				break;
			case 'd' :
				$value = ( double ) $value;
				break;
			case 'b' :
				$value = ( bool ) $value;
				break;
			case 'a':
				$value = ( array ) $value;
				break;
			default :
				$value = ( string ) $value;
				break;
		}
		
		return $value;
	
	}
}

?>