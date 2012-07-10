<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.view.*' );

class CKView extends CKObject implements IView {
	
	private $viewer;
	private $template;
	
	public function __construct($viewer, $controller, $action, $viewdata) {
		$this->viewer = Cike::singleton('@.ck.view.'.$viewer.'View', array( $controller, $action, $viewdata) );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::display()
	 */
	public function display() {
		$this->viewer->display ();
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::assign()
	 */
	public function assign($var) {
		$this->viewer->assign ( $var );
	}

}

?>