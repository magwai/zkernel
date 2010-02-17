<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_User extends Zend_View_Helper_Abstract  {
	private $_user = null;

	public function init() {
		$this->_user = Zend_Registry::get('Zkernel_User');
	}

	public function isLogged() {
		return $this->_user->id ? true : false;
	}

	public function login($login, $password = null, $remember = false) {
		return $this->_user->login($login, $password, $remember);
	}

	public function logout() {
		return $this->_user->logout();
	}

	function isAllowed($role = null, $resource = null, $privilege = null) {
		return $this->_user->isAllowed($role, $resource, $privilege);
	}

	function update($data, $id = null) {
		return $this->_user->update($data, $id);
	}

	function register($data) {
		return $this->_user->register($data);
	}

	public function user($p = null) {
		if ($this->_user === null) $this->init();
		if ($p === true) return $this->_user->get();
		else if ($p !== null) return $this->_user->$p;
    	return $this;
    }
}