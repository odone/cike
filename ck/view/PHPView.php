<?php

! defined ( 'IN_CIKE' ) ? null : '!';

Cike::import ( '@.ck.view.*' );

class PHPView extends CKObject implements IView {
	
	private $viewDir;
	private $template;
	private $viewdata;
	
	public function __construct($controller, $action, $viewdata) {
		$this->template = $controller . '_' . $action;
		if ($action == Cike::configure ()->get ( 'action.default' )) {
			$this->template = $controller;
		}
		$this->template .= '.php';
		
		$this->viewDir = Cike::configure ()->get ( 'phpview.dir' );
		if (! file_exists ( $this->viewDir )) {
			Cike::throwException ( 'DirNotExistsException', $this->viewDir, true );
		}
		$this->viewdata = $viewdata;
	}
	
	public function url($controller = '', $action = '', $params = array()) {
		return Util::url ( $controller, $action, $params );
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::display()
	 */
	public function display() {
		extract ( $this->viewdata );
		$template = $this->viewDir . DS . $this->template;
		if (! file_exists ( $template )) {
			Cike::throwException ( 'TemplateNotExistsException', $template, true );
		}
		include $template;
	}
	
	/* (non-PHPdoc)
	 * @see libs/Cike/ck/view/IView::assign()
	 */
	public function assign($var) {
		$this->viewdata = array_merge ( $this->viewdata, $var );
	}

}

?>