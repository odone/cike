<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CKRouter extends CKObject {
	
	private $controllerName;
	private $actionName;
	
	public function __construct() {
		$this->controllerName = '';
		$this->actionName = '';
		
		$this->formatVars ( & $_REQUEST );
		$this->formatVars ( & $_GET );
		$this->formatVars ( & $_POST );
		
		switch (Cike::configure ()->get ( 'mvc.mode' )) {
			#MIXED
			case 'mixed' :
				if (isset ( $_GET [Cike::configure ()->get ( 'mvc.path_accessor' )] )) {
					$_SERVER ['PATH_INFO'] = $_GET [Cike::configure ()->get ( 'mvc.path_accessor' )];
				}
			#REWRITE
			case 'rewrite' :
			#PATHINFO
			case 'pathinfo' :
				$pathinfo = isset ( $_SERVER ['PATH_INFO'] ) ? $_SERVER ['PATH_INFO'] : '';
				$rules = Cike::configure ()->get ( 'mvc.rules' );
				foreach ( ( array ) $rules as $rule => $redirect ) {
					if (preg_match ( '/' . $rule . '/', $pathinfo )) {
						$pathinfo = preg_replace ( '/' . $rule . '/', $redirect, $pathinfo );
					}
				}
				
				if (substr ( $pathinfo, 0, 1 ) == '/') {
					$pathinfo = substr ( $pathinfo, 1 );
				}
				$arr = explode ( '/', $pathinfo );
				
				if (isset ( $arr [0] ) && ! empty ( $arr [0] )) {
					$this->controllerName = $arr [0];
					unset ( $arr [0] );
				}
				if (isset ( $arr [1] ) && $arr [1] != '') {
					$this->actionName = $arr [1];
					unset ( $arr [1] );
				}
				
				
				$C = count ( $arr );
				if ($C % 2 == 1) {
					array_pop ( $arr );
				}
				$C = count ( $arr );
				if ($C >= 2) {
					$arr = array_chunk ( $arr, 2 );
					foreach ( $arr as $ar ) {
						if (isset ( $ar [0] ) && isset ( $ar [1] )) {
							$_GET [$ar [0]] = $ar [1];
						}
					}
					
					$_REQUEST = array_merge ( $_REQUEST, $_GET );
				}
				
				break;
			#GET
			default :
				if (isset ( $_GET [Cike::configure ()->get ( 'controller.accessor' )] )) {
					$this->controllerName = $_GET [Cike::configure ()->get ( 'controller.accessor' )];
				}
				if (isset ( $_GET [Cike::configure ()->get ( 'action.accessor' )] ) && $_GET [Cike::configure ()->get ( 'action.accessor' )] != '') {
					$this->actionName = $_GET [Cike::configure ()->get ( 'action.accessor' )];
				}
		}
		
		if ($this->controllerName == '') {
			$this->controllerName = Cike::configure ()->get ( 'controller.default' );
		}
		if ($this->actionName == '') {
			$this->actionName = Cike::configure ()->get ( 'action.default' );
		}
		
		define ( 'CONTROLLER', $this->controllerName );
		define ( 'ACTION', $this->actionName );
	}
	
	public function getController() {
		return $this->controllerName;
	}
	
	public function getAction() {
		return $this->actionName;
	}
	
	/**
	 * 格式化变量
	 * @param string $vars 
	 */
	private function formatVars(& $vars) {
		foreach ( $vars as $k => & $v ) {
			if (is_array ( $v )) {
				$this->formatVars ( &$v );
			} else {
				$v = trim ( $v );
				if (QM_GPC) {
					$v = stripslashes ( $v );
				}
			}
		}
	}

}