<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.view.*' );

class RedirectView extends CKObject implements IView {
	
	public function __construct($controller, $action, $viewdata) {
		$this->viewdata = $viewdata;
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::display()
	 */
	public function display() {
        if (isset($this->viewdata['redirectUrl']) ){
        	header("Location: {$this->viewdata['redirectUrl']}\n");
        }
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::assign()
	 */
	public function assign($var) {
		$this->viewdata = array_merge ( $this->viewdata, $var );
	}

}

?>