<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class XCacheManager extends CKObject implements ICacheManager {
	
	private $expires;
	
	public function __construct() {
		if (! function_exists ( 'xcache_set' )) {
			Cike::throwException ( 'CacheEngineNotSupport', 'XCache', true );
		}
		
		$this->expires = Cike::configure ()->get ( 'xcache.expires' );
		
		if ($this->expires == null) {
			$this->expires = 3600;
		}
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::get()
	 */
	public function get($cacheName) {
		$cacheName = $this->getCacheName ( $cacheName );
		return xcache_get ( $cacheName );
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
		
		xcache_set ( $cacheName, $cacheData, $lifetime );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::del()
	 */
	public function del($cacheName) {
		// todo Auto-generated method stub
		xcache_unset ( $this->getCacheName ( $cacheName ) );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::purge()
	 */
	public function purge() {
	}
	
	private function getCacheName($cacheName) {
		return APP_ID . '.' . $cacheName;
	}
}

?>