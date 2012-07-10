<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.view.*' );

class JSONView extends CKObject implements IView {
	
	private $viewdata;
	
	public function __construct($controller, $action, $viewdata) {
		$this->viewdata = $viewdata;
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::display()
	 */
	public function display() {
		header("content-type: application/json; \n\n");
		echo json_encode($this->viewdata);
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::assign()
	 */
	public function assign($var) {
		$this->viewdata = array_merge ( $this->viewdata, $var );
	}

}

?>