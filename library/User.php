<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_User {
	private $_acl = null;
	private $_data = array();
	private $_models = array();
	private $_auth = null;
	private $_auth_adapter = null;


	function isAllowed($role = null, $resource = null, $privilege = null) {
		return $this->_acl->isAllowed($role, $resource, $privilege);
	}

	function __construct($data) {
		foreach ($data as $_k => $_v) $this->_models[$_k] = $_v;
	}

	function __get($k) {
		return $this->get($k);
	}

	function get($k = null) {
		return $k === null
			? $this->_data
			: (isset($this->_data->$k) ? $this->_data->$k : null);
	}

	private function initAuth() {
		$this->_auth = Zend_Auth::getInstance();
		$this->_auth->setStorage(new Zend_Auth_Storage_Session('Default', 'user_'.$this->_models['user']->info('name')));
		$this->_auth_adapter = new Zend_Auth_Adapter_DbTable(
			$this->_models['user']->getAdapter(),
			$this->_models['user']->info('name'),
			'login',
			'password',
			'SHA1(?) AND active = ""'
		);
		return $this;
	}

	function initAcl() {
		$this->_acl = new Zend_Acl();
		$roles = $this->_models['role']->fetchCol('id');
		if ($roles) {
			foreach ($roles as $el) {
				$r = new Zend_Acl_Role($el);
				$ps = $this->_models['role_refer']->fetchCol('parentid', array(
					'`role` = ?' => $el
				));
				$p = array();
				if ($ps) {
					foreach ($ps as $el_1) $p[] = new Zend_Acl_Role($el_1);
				}
				$this->_acl->addRole($r, $p);
			}
		}

		$res = $this->_models['resource']->fetchAll(null, 'parentid');

		if ($res) {
			foreach ($res as $el) {
				$r = new Zend_Acl_Resource($el->id);
				$rp = $el->parentid ? new Zend_Acl_Resource($el->parentid) : null;
				$this->_acl->addResource($r, $rp);
			}
		}

		$rs = $this->_models['rule']->fetchAll();
		if ($rs) {
			foreach ($rs as $el) {
				$rs_role = $this->_models['rule_role']->fetchCol('role', array(
					'`parentid` = ?' => $el->id
				));
				$rs_res = $this->_models['rule_resource']->fetchCol('resource', array(
					'`parentid` = ?' => $el->id
				));
				if ($el->rule) $this->_acl->allow($rs_role, $rs_res);
				else $this->_acl->deny($rs_role, $rs_res);
			}
		}
		return $this;
	}

	function logout() {
		if ($this->_auth === null) $this->initAuth();
		if ($this->_auth->hasIdentity()) {
			$this->_auth->clearIdentity();
			$key = 'user_'.$this->_models['user']->info('name');
			$this->_data = array();
			unset($_COOKIE[$key]);
			setcookie(
				$key,
				'',
				0,
				'/'
			);
			return true;
		}
		return false;
	}

	function login($login, $password = null, $remember = false) {
		if ($this->_auth === null) $this->initAuth();
		$key = 'user_'.$this->_models['user']->info('name');
		if ($password === null) {
			$data = $this->_models['user']->fetchRow(array(
				'`login` = ?' => $login
			));
			if ($data) {
				$result = $this->_auth->getStorage()->write($login);
				$this->_data = $data;
				if ($remember) setcookie(
					$key,
					sha1($this->_data->login.$this->_data->password),
					time() + 86400 * 30,
					'/'
				);
				return true;
			}
		}
		else {
			$this->_auth_adapter	->setIdentity($login)
									->setCredential($password);
			$result = $this->_auth->authenticate($this->_auth_adapter);
			if ($result->isValid()) return $this->login($login, null, $remember);
		}
		return false;
	}

	function loginAuto() {
		if ($this->_auth === null) $this->initAuth();
		if ($this->_auth->hasIdentity()) $this->login($this->_auth->getIdentity());
		else {
			$key = 'user_'.$this->_models['user']->info('name');
			$secret = @$_COOKIE[$key] ? $_COOKIE[$key] : '';
			$data = $this->_models['user']->fetchRow(array(
				'SHA1(CONCAT(`login`, `password`)) = ?' => $secret
			));
			if ($data) $this->login($data->login);
			else setcookie(
				$key,
				'',
				0,
				'/'
			);
		}
	}

	function register($data) {
		$meta = $this->_models['user']->info('metadata');
		if ($data) foreach ($data as $k => $v) if (!array_key_exists($k, $meta)) unset($data[$k]);
		$data['password'] = sha1($data['password']);
		return $this->_models['user']->insert($data);
	}

	function update($data, $id = null) {
		$meta = $this->_models['user']->info('metadata');
		if ($data) foreach ($data as $k => $v) if (!array_key_exists($k, $meta)) unset($data[$k]);
		if (!$data) return false;
		$same = $id === null || $id == @$this->_data->id;
		if ($same) $id = $this->_data->id;
		if ($id) {
			if (isset($data['password'])) $data['password'] = sha1($data['password']);
			$ok = $this->_models['user']->update($data, array(
				'`id` = ?' => $id
			));
			if ($same) $this->login(@$data['login'] ? $data['login'] : $this->_data->login);
			if ($ok) $ok = $id;
			return $ok;
		}
		return false;
	}

	function loginza($token, $match = array()) {
		$res = @file_get_contents('http://loginza.ru/api/authinfo?token='.$token);
		$res_decoded = $this->loginzaParse($res);
		if ($res_decoded && isset($res_decoded['mail'])) {
			$ex = (int)$this->_models['user']->fetchOne('id', array(
				'`login` = ?' => $res_decoded['login']
			));
			$data = array(
				'token' => $token,
				'token_last' => date('Y-m-d H:i:s'),
				'loginza' => $res
			);
			if ($ex) {
				$ok = $this->update($data, $ex);
			}
			else {
				$data['login'] = $res_decoded['login'];
				$data['password'] = md5(time().rand(0, 9999999));
				if ($match) {
					$meta = $this->_models['user']->info('metadata');
					foreach ($match as $k => $v) {
						if (isset($meta[$v]) && isset($res_decoded[$k])) {
							$data[$v] = $res_decoded[$k];
						}
					}
				}
				$ok = $this->register($data);
			}
			if ($ok) {
				$this->login($res_decoded['login']);
			}
		}
		else $ok = false;
		return $ok;
	}

	function loginzaParse($data) {
		$ret = array();
		$res = @json_decode($data);
		if ($res) {
			$ret = array(
				'nick' => isset($res->nickname) ? $res->nickname : '',
				'name' => isset($res->name->first_name) ? $res->name->first_name : '',
				'family' => isset($res->name->last_name) ? $res->name->last_name : '',
				'login' =>	isset($res->identity) ? $res->identity : '',
				'full' => isset($res->name->full_name) ? $res->name->full_name : '',
				'mail' =>	isset($res->email) ? $res->email : ''
			);
			if (!$ret['full']) $ret['full'] = ($ret['name'] || $ret['family']
				? $ret['name'].($ret['name'] && $ret['family'] ? ' ' : '').$ret['family']
				: ($ret['nick'] ? $ret['nick'] : ($ret['mail'] ? $ret['mail'] : preg_replace('/(http\:\/\/openid\.yandex\.ru\/|\/)/i', '', $ret['login'])))
			);
		}
		return $ret;
	}
}
