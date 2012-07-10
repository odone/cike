<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CKObject {
	
	function getClassName() {
		$ref = new ReflectionClass ( $this );
		return $ref->getName ();
	}
	
}

?>