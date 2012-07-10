<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.cacheManager.*' );

class CKCacheManager extends CKObject implements ICacheManager {
	
	private $config = array ();
	private $manager;
	
	public function __construct() {
	}
	
	public function init() {
		if ($this->manager == null) {
			$this->file ();
		}
	}
	
	public function get($cacheName) {
		$this->init();
		
		return $this->manager
			->get ( $cacheName );
	}
	
	public function set($cacheName, $cacheData, $expires = null) {
		$this->init();
		
		$this->manager
			->set ( $cacheName, $cacheData, $expires );
	}
	
	public function del($cacheName) {
		$this->init();
		
		$this->manager
			->del ( $cacheName );
	}
	
	public function purge() {
		$this->manager
			->purge ();
	}
	
	public function file() {
		if (SAE_MODE){
			return $this->saefile();
		}
		$this->manager = Cike::singleton ( 'FileCacheManager' );
		return $this;
	}
	
	public function saefile() {
		$this->manager = Cike::singleton ( 'SAEFileCacheManager' );
		return $this;
	}
	
	public function apc() {
		$this->manager = Cike::singleton ( 'APCCacheManager' );
		return $this;
	}
	
	public function xcache() {
		$this->manager = Cike::singleton ( 'XCacheManager' );
		return $this;
	}
	
	public function memcache() {
		$this->manager = Cike::singleton ( 'MemCacheManager' );
		return $this;
	}
	
	public function eaccelerator() {
		$this->manager = Cike::singleton ( 'EacceleratorCacheManager' );
		return $this;
	}

}