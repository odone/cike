<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.view.*' );

class XMLView extends CKObject implements IView {
	
	public function __construct($controller, $action, $viewdata) {
		$this->viewdata = $viewdata;
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::display()
	 */
	public function display() {
		$charset = Cike::configure ()->get ( 'mvc.response_charset' );
		header ( "content-type: text/xml; charset={$charset}\n\n" );
		
		echo "<?xml version=\"1.0\" encoding=\"{$charset}\" ?>\n";
		echo "<xml>\n", '<generator>Cike</generator>', $this->putXML ( $this->viewdata ), '</xml>';
	
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::assign()
	 */
	public function assign($var) {
		$this->viewdata = array_merge ( $this->viewdata, $var );
	}
	
	function putXML($data) {
		foreach ( $data as $k => $v ) {
			if (is_array ( $v )) {
				if ($k == '' || is_int ( $k ))
					$k = 'array';
				echo "<{$k}>\n", $this->putXML ( $v ), "</{$k}>\n";
			} else {
				$v = Util::t ( $v );
				echo "<$k>$v</$k>\n";
			}
		}
	}

}

?>