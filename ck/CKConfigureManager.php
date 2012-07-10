<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.configureManager.*' );

class CKConfigureManager extends CKObject implements IConfigureManager {
	
	private $config = array ();
	private $manager = 'php';
	
	public function __construct() {
		$this->php ();
		$this->load ( '@.ck.config.dev' );
	}
	
	public function load($file, $key = '') {
		try {
			Cike::import ( $file );
		} catch ( PackageNotFoundException $ex ) {
			Cike::throwException ( 'ConfigFileNotExistsException', $file );
		}
		
		if (strpos ( $file, '(yaml)' ) > 0) {
			$this->yaml ();
		} elseif (strpos ( $file, '(ini)' ) > 0) {
			$this->ini ();
		} else {
			$this->php ();
		}
		
		if ($key == '') {
			$configs = $this->manager
				->load ( $file );
			foreach ( $configs as $k => $item ) {
				if (! is_array ( $item ))
					continue;
				foreach ( $item as $_k => $_v ) {
					$this->config [$k] [$_k] = $_v;
				}
			}
		} else {
			$this->config [$key] = $this->manager
				->load ( $file );
		}
	}
	
	public function getAll() {
		return $this->config;
	}
	
	public function get($key) {
		$s = '$this->config[\'' . str_replace ( '.', '\'][\'', $key ) . '\']';
		$s = 'return isset(' . $s . ') ? ' . $s . ' : null;';
		return eval ( $s );
	}
	
	public function set($key, $value) {
		$s = '$this->config[\'' . str_replace ( '.', '\'][\'', $key ) . '\'] = $value;';
		return eval ( $s );
	}
	
	public function php() {
		$this->manager = Cike::singleton ( 'PhpConfigureManager' );
		return $this;
	}
	
	public function yaml() {
		$this->manager = Cike::singleton ( 'YAMLConfigureManager' );
		return $this;
	}
	
	public function ini() {
		$this->manager = Cike::singleton ( 'IniConfigureManager' );
		return $this;
	}
}

?>