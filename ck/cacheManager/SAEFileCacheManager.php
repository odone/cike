<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class SAEFileCacheManager extends CKObject implements ICacheManager {
	
	private $storage;
	private $expiresKey = '.EXPIRES';
	private $expires;
	private $domain;
	
	public function __construct() {
		$this->expires = Cike::configure ()->get ( 'sae.storage.expires' );
		$this->domain = Cike::configure ()->get ( 'sae.storage.domain' );
		
		if ($this->expires == null) {
			$this->expires = 3600;
		}
		
		$this->storage = Cike::singleton ( 'SaeStorage' );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::get()
	 */
	public function get($cacheName) {
		$cacheFilename = $this->getCacheName ( $cacheName );
		$code = $this->storage
			->read ( $this->domain, $cacheFilename );
		if ($code) {
			$cacheData = unserialize ( $code );
			if (isset ( $cacheData [$this->expiresKey] ) && $cacheData [$this->expiresKey] > time ()) {
				unset ( $cacheData [$this->expiresKey] );
				return $cacheData;
			}
		}
		
		$this->del ( $cacheName );
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::set()
	 */
	public function set($cacheName, $cacheData, $expires = null) {
		$cacheFilename = $this->getCacheName ( $cacheName );
		if (! is_array ( $cacheData ))
			$cacheData = array ($cacheData );
		
		$cacheData [$this->expiresKey] = time ();
		if ($expires != null) {
			$cacheData [$this->expiresKey] += $expires;
		} else {
			$cacheData [$this->expiresKey] += $this->expires;
		}
		
		$this->storage
			->write ( $this->domain, $cacheFilename, serialize ( $cacheData ) );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::del()
	 */
	public function del($cacheName) {
		$this->storage
			->delete ( $this->domain, $cacheName );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::purge()
	 */
	public function purge() {
	}
	
	private function getCacheName($cacheName) {
		return APP_ID . '.' . $cacheName . '.cache';
	}
}

?>