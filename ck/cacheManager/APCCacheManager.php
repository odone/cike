<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class APCCacheManager extends CKObject implements ICacheManager {
	
	private $expires;
	
	public function __construct() {
		if (! function_exists ( 'apc_add' )) {
			Cike::throwException ( 'CacheEngineNotSupport', 'apc', true );
		}
		
		$this->expires = Cike::configure ()->get ( 'apccache.expires' );
		if ($this->expires == null) {
			$this->expires = 3600;
		}
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::get()
	 */
	public function get($cacheName) {
		$cacheName = $this->getCacheName ( $cacheName );
		return apc_fetch ( $cacheName );
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
		
		return apc_add ( $cacheName, $cacheData, $lifetime );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::del()
	 */
	public function del($cacheName) {
		// todo Auto-generated method stub
		apc_delete ( $this->getCacheName ( $cacheName ) );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::purge()
	 */
	public function purge() {
	
	}
	
	private function getCacheName($cacheName) {
		return APP_ID . '.' . $cacheName . '.php';
	}
}

?>