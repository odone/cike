<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.view.*' );

class SendFileView extends CKObject implements IView {
	
	private $viewdata;
	
	public function __construct($controller, $action, $viewdata) {
		$this->viewdata = $viewdata;
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::display()
	 */
	public function display() {
		if (isset ( $this->viewdata ['filename'] )) {
			if (($pos = strpos ( $this->viewdata ['filename'], '/' )) === false) {
				$saveFilename = $this->viewdata ['filename'];
			} else {
				$saveFilename = substr ( $this->viewdata ['filename'], $pos + 1 );
			}
			Util::sendFile ( $this->viewdata ['filename'], $saveFilename );
		}
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::assign()
	 */
	public function assign($var) {
	}

}

?>