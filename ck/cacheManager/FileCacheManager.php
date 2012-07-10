<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class FileCacheManager extends CKObject implements ICacheManager {
	
	private $dir;
	private $expiresKey = '.EXPIRES';
	private $expires;
	
	public function __construct() {
		if (! SAE_MODE) {
			$this->dir = Cike::configure ()->get ( 'filecache.dir' );
			
			if (! file_exists ( $this->dir )) {
				Cike::throwException ( 'DirNotExistsException', $this->dir, true );
			}
		}
		
		$this->expires = Cike::configure ()->get ( 'filecache.expires' );
		if ($this->expires == null) {
			$this->expires = 3600;
		}
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::get()
	 */
	public function get($cacheName) {
		if (SAE_MODE) {
			return Cike::singleton ( 'SAEFileCacheManager' )->get ( $cacheName );
		}
		
		$cacheFilename = $this->getCacheName ( $cacheName );
		if (file_exists ( $cacheFilename )) {
			$cacheData = FS::safe_file_get_contents ( $cacheFilename );
			if ($cacheData) {
				$cacheData = substr ( $cacheData, 13 );
				$cacheData = unserialize ( $cacheData );
				if (isset ( $cacheData [$this->expiresKey] ) && $cacheData [$this->expiresKey] > time ()) {
					unset ( $cacheData [$this->expiresKey] );
					return $cacheData;
				}
			}
		}
		
		$this->del ( $cacheName );
		return null;
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::set()
	 */
	public function set($cacheName, $cacheData, $expires = null) {
		if (SAE_MODE) {
			return Cike::singleton ( 'SAEFileCacheManager' )->set ( $cacheName, $cacheData, $expires );
		}
		
		$cacheFilename = $this->getCacheName ( $cacheName );
		if (! is_array ( $cacheData ))
			$cacheData = array ($cacheData );
		
		$cacheData [$this->expiresKey] = time ();
		if ($expires != null) {
			$cacheData [$this->expiresKey] += $expires;
		} else {
			$cacheData [$this->expiresKey] += $this->expires;
		}
		
		$code = '<?php exit;?>' . serialize ( $cacheData );
		FS::safe_file_put_contents ( $cacheFilename, $code );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::del()
	 */
	public function del($cacheName) {
		if (SAE_MODE) {
			return Cike::singleton ( 'SAEFileCacheManager' )->del ( $cacheName );
		}
		$cacheFilename = $this->getCacheName ( $cacheName );
		if (file_exists ( $cacheFilename )) {
			return @unlink ( $cacheFilename );
		}
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/cacheManager/ICacheManager::purge()
	 */
	public function purge() {
		// todo Auto-generated method stub
		$files = glob ( $this->dir . DS . APP_ID . '*.php' );
		foreach ( $files as $f ) {
			@unlink ( $f );
		}
	}
	
	private function getCacheName($cacheName) {
		return $this->dir . DS . APP_ID . '.' . $cacheName . '.php';
	}
}

?>