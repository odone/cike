<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CKController extends CKObject {
	
	/**
	 * @var CKDispatcher
	 */
	protected $dispatcher;
	protected $controller;
	protected $action;
	
	public $viewer;
	public $viewdata = array ();
	
	/**
	 * 
	 * @param CKDispatcher $dispatcher
	 */
	public function __construct($dispatcher) {
		
		$this->dispatcher = $dispatcher;
		$this->controller = $this->dispatcher
			->getController ();
		$this->action = $this->dispatcher
			->getAction ();
		
		$this->viewer = 'PHP';
		
		$this->template = $this->controller . '_' . $this->action;
		$this->viewdata = array ('NOW' => date ( 'Y-m-d H:i:s' ), 'CONTROLLER' => $this->controller, 'ACTION' => $this->action, 'DATE' => date ( 'Y-m-d' ), 'TIME' => date ( 'H:i:s' ), 'DESING_MODE' => false, 'URL' => URL );
	}
	
	public function forward($action, $controller = null) {
		$this->dispatcher
			->forward ( ($controller == null ? $this->controller : $controller), $action );
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function __before() {
	
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function __action() {
	
	}
	
	public function url($action = null, $params = array()) {
		return Util::url ( $this->getController (), $action, $params );
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function getController() {
		return $this->controller;
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function getAction() {
		return $this->action;
	}
}

?>