<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CKDispatcher extends CKObject {
	
	private $router;
	
	private $controllerName;
	private $actionName;
	
	private $ACLS;
	private $USER;
	private $ROLE;
	
	/**
	 * @param Router $router
	 */
	public function __construct($router) {
		$this->router = $router;
		
		$this->controllerName = $this->router
			->getController ();
		$this->actionName = $this->router
			->getAction ();
		
		$this->USER = (array)Cike::RBAC ()->getUser ();
		$this->ROLE = (array)Cike::RBAC ()->getRole ();
	}
	
	public function dispatch() {
		if (! $this->check ()) {
			Cike::throwException ( 'AccessDeniedException', $this->getController () . '->' . $this->getAction (), true );
		}
		
		$controller = $this->loadController ();
		$methodName = Cike::configure ()->get ( 'action.prefix' ) . $this->actionName . Cike::configure ()->get ( 'action.suffix' );
		
		if (method_exists ( $controller, $methodName )) {
			
			// 前置ACTION，每次都会执行
			$beforeActionName = Cike::configure ()->get ( 'action.before' );
			if (method_exists ( $controller, $beforeActionName )) {
				$controller->$beforeActionName ();
			}
			
			Util::isXSS();
			
			$viewCached = Cike::configure ()->get ( 'view.cached' );
			$viewCacheData = null;
			if ($viewCached) {
				$viewCacheName = 'view.' . $this->controllerName . '.' . $this->actionName;
				$viewCacheData = Cike::cache ()->get ( $viewCacheName );
			}
			if (! $viewCacheData) {
				if ($viewCached) {
					@ob_start ();
				}
				
				$result = $controller->$methodName ();
				if ($result != null) {
					$result->viewdata['_TOKEN'] = md5( APP_ID.URL.Util::getIP().$_SERVER['HTTP_USER_AGENT']);
					$token = Context::post('_TOKEN','s',null);
					if ($token && $result->viewdata['_TOKEN'] != $token){
						die('XSS攻击@');
					}
					$viewer = Cike::singleton ( 'CKView', array ($result->viewer, $result->getController (), $result->getAction (), $result->viewdata ) );
					$viewer->display ();
				}
				
				if ($viewCached) {
					$viewCacheData = array ('html' => ob_get_contents () );
					if (ob_get_length() > 0){
						@ob_end_clean();
					}
					Cike::cache ()->set ( $viewCacheName, $viewCacheData, Cike::configure ()->get ( 'view.expires' ) );
				}
			}
			
			if ($viewCached) {
				echo $viewCacheData ['html'];
			}
			
			// 后置ACTION，每次都会执行
			$afterActionName = Cike::configure ()->get ( 'action.after' );
			if (method_exists ( $controller, $afterActionName )) {
				$controller->$afterActionName ();
			}
			
			exit ();
		} else {
			Cike::throwException ( 'ActionNotExistsException', $this->actionName );
		}
	}
	
	private function loadController() {
		$controllerName = Cike::configure ()->get ( 'controller.prefix' ) . $this->controllerName . Cike::configure ()->get ( 'controller.suffix' );
		$package = '$.' . Cike::configure ()->get ( 'controller.dir' ) . '.' . ucfirst ( $controllerName );
		return Cike::singleton ( $package, array ($this ) );
	}
	
	public function forward($controller, $action) {
		$this->controllerName = $controller;
		$this->actionName = $action;
		
		$this->dispatch ();
	}
	
	public function check() {
		$aclFile = Cike::configure ()->get ( 'rbac.acl' );
		if ($aclFile == null)
			return true;
		
		Cike::configure ()->load ( $aclFile, 'acl' );
		$this->ACLS = Cike::configure ()->get ( 'acl' );
		
		if ($this->ACLS == null) {
			return true;
		}
		
		if (isset ( $this->ACLS [$this->controllerName] )) {
			if (isset ( $this->ACLS [$this->controllerName] [$this->actionName] )) {
				return $this->checkRole ( $this->ACLS [$this->controllerName] [$this->actionName] );
			} else {
				if (isset ( $this->ACLS [$this->controllerName] ['deny'] )) {
					return ! $this->checkRole ( $this->ACLS [$this->controllerName] ['deny'] );
				}
				if (isset ( $this->ACLS [$this->controllerName] ['allow'] )) {
					return $this->checkRole ( $this->ACLS [$this->controllerName] ['allow'] );
				}
			}
		}
		return false;
	}
	
	public function checkRole($role) {
		switch ($role) {
			case RBAC_HAS_ROLE :
				return ($this->ROLE ? true : false);
				break;
			case RBAC_NO_ROLE :
				return ($this->ROLE ? false : true);
				break;
			case RBAC_ALL :
				return true;
				break;
			default :
				return in_array ( $role, $this->ROLE );
		}
	}
	
	public function getController() {
		return $this->controllerName;
	}
	
	public function getAction() {
		return $this->actionName;
	}
}

?>