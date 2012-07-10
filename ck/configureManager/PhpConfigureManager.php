<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class PhpConfigureManager extends CKObject implements IConfigureManager {
	
	public function load($file) {
		$filename = substr ( $file, strrpos ( $file, '.' ) + 1 );
		$loadArray = Cike::load ( $filename );
		
		$config = array ();
		foreach ( $loadArray as $k => $item ) {
			if (strpos ( $k, '.' )) {
				$code = '$config[\'' . str_replace ( '.', '\'][\'', $k ) . '\']=$item;';
				eval ( $code );
			} else {
				$config [$k] = $item;
			}
		}
		
		return $config;
	}
}

?>