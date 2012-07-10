<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class IniConfigureManager extends CKObject implements IConfigureManager {
	
	public function load($file) {
		$filename = Cike::getFilePath ( substr ( $file, strrpos ( $file, '.' ) + 1 ) );
		return parse_ini_file ( $filename, true );
	}
}

?>