<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class YAMLConfigureManager extends CKObject implements IConfigureManager {
	
	public function load($file) {
		$filename = Cike::getFilePath(substr ( $file, strrpos ( $file, '.' ) + 1 ));
		return Util::loadYaml ( $filename );
	}
}

?>