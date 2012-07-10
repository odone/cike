<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CKRBAC extends CKObject {
	
	private $sessionKey;
	private $roleKey;
	
	public function __construct() {
		$this->sessionKey = Cike::configure ()->get ( 'session.key' );
		$this->roleKey = Cike::configure ()->get ( 'rbac.key' );
	}
	
	public function getUser() {
		return isset ( $_SESSION [$this->sessionKey] ) ? $_SESSION [$this->sessionKey] : null;
	}
	
	public function getRole() {
		return isset ( $_SESSION [$this->roleKey] ) ? $_SESSION [$this->roleKey] : null;
	}
	
	public function setUser($user) {
		$_SESSION [$this->sessionKey] = $user;
	}
	
	public function setRole($role) {
		$_SESSION [$this->roleKey] = $role;
	}
	
	public function clearUser() {
		$this->setUser ( array () );
	}
	
	public function clearRole() {
		$this->setRole ( array () );
	}

}