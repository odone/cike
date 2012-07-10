<?php

! defined ( 'IN_CIKE' ) ? null : '!';

class CKUserModel extends CKModel {
	
	protected $salt;
	protected $usernameField = 'username';
	protected $passwordField = 'password';
	
	public function __construct(){
		parent::__construct();
		$this->salt = APP_ID;
	}
	
	public function createUser($username, $password){
		return parent::create(array(
			$this->usernameField => $username,
			$this->passwordField => $this->compilePassword($password),
		));
	}
	
	public function compilePassword($password){
		return md5(md5($password).$this->salt);
	}
	
	public function validateUser($username, $password){
		return $this->find('*', sprintf(
			'%s = %s AND %s = %s',
			$this->usernameField,
			"'" . $username . "'",
			$this->passwordField,
			"'" . $this->compilePassword($password) . "'"
		));
	}
	
	public function changePassword($userid, $password){
		return $this->update(array(
				$this->passwordField => $this->compilePassword($password),
			), 
			sprintf(
				'%s = %s',
				$this->getPrimaryKey,
				$userid
			)
		);
	}
}

?>