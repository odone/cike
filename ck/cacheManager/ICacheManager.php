<?php

! defined ( 'IN_CIKE' ) ? null : '!';

interface ICacheManager {
	function get($cacheName);
	function set($cacheName, $cacheData, $expires = null);
	function del($cacheName);
	function purge();
}

?>