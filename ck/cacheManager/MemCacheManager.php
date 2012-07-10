<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class MemCacheManager extends CKObject implements ICacheManager {
	
	private $expires;
	private $memCache;
	private $config;
	
	public function __construct() {
		if (! isset ( $this->config ['port'] )) {
			$this->config ['port'] = 12121;
		}
		
		if (SAE_MODE) {
			$this->memCache = memcache_init ();
		} else {
			if (! class_exists ( 'MemCache' )) {
				Cike::throwException ( 'CacheEngineNotSupport', 'MemCache', true );
			}
			
			$this->config = Cike::configure ()->get ( 'memcache' );
			$this->expires = Cike::configure ()->get ( 'memcache.expires' );
			
			if ($this->expires == null) {
				$this->expires = 3600;
			}
			
			$this->memCache = new MemCache ();
			try {
				if (! isset ( $this->config ['host'] )) {
					$this->config ['host'] = '127.0.0.1';
				}
				if (! isset ( $this->config ['port'] )) {
					$this->config ['port'] = '11211';
				}
				
				$this->memCache
					->connect ( $this->config ['host'], $this->config ['port'] );
			} catch ( Exception $ex ) {
			
			}
		}
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::get()
	 */
	public function get($cacheName) {
		$cacheName = $this->getCacheName ( $cacheName );
		return $this->memCache
			->get ( $cacheName );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::set()
	 */
	public function set($cacheName, $cacheData, $expires = null) {
		$cacheName = $this->getCacheName ( $cacheName );
		if (! is_array ( $cacheData ))
			$cacheData = array ($cacheData );
		
		$lifetime = time ();
		if ($expires != null) {
			$lifetime += $expires;
		} else {
			$lifetime += $this->expires;
		}
		
		$this->memCache
			->add ( $cacheName, $cacheData, null, $lifetime );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::del()
	 */
	public function del($cacheName) {
		// todo Auto-generated method stub
		$this->memCache
			->delete ( $this->getCacheName ( $cacheName ) );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::purge()
	 */
	public function purge() {
		$this->memCache
			->flush ();
	}
	
	private function getCacheName($cacheName) {
		return APP_ID . '.' . $cacheName;
	}
}

?>