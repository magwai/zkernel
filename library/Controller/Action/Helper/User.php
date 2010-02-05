<?php

class Zkernel_Controller_Action_Helper_User extends Zend_Controller_Action_Helper_Abstract  {
	public $acl;
	public $data = array();
	public $models = null;
	public $auth;
	public $auth_adapter;

	function setModels($data) {
		if ($this->models === null) $this->models = new stdClass();
		if ($data) {
			foreach ($data as $k => $v) $this->models->$k = $v;
		}
		return $this;
	}

	function __get($k) {
		return isset($this->data->$k) ? $this->data->$k : null;
	}

	function initAuth() {
		$this->auth = Zend_Auth::getInstance();
		$this->auth->setStorage(new Zend_Auth_Storage_Session('Default', 'user_'.$this->models->user->info('name')));
		$this->auth_adapter = new Zend_Auth_Adapter_DbTable(
			$this->models->user->getAdapter(),
			$this->models->user->info('name'),
			'login',
			'password',
			'SHA1(?) AND active = ""'
		);
		return $this;
	}

	function initAcl() {
		$this->acl = new Zend_Acl();

		/*$roles = $this->models->role->fetchCol($this->models->role->getAdapter()->select()
			->from(array('r' => $this->models->role->info('name')), array('id'))
			->joinLeft(array('f' => $this->models->role_refer->info('name')), 'r.id = f.role', '')
			->group('r.id')
			->order('COUNT(f.id) = 0 desc')
		);
*/


		$roles = $this->models->role->fetchCol('id');
		if ($roles) {
			foreach ($roles as $el) {
				$r = new Zend_Acl_Role($el);
				$ps = $this->models->role_refer->fetchCol('parentid', array(
					'`role` = ?' => $el
				));
				$p = array();
				if ($ps) {
					foreach ($ps as $el_1) $p[] = new Zend_Acl_Role($el_1);
				}
				$this->acl->addRole($r, $p);
			}
		}
		$res = $this->models->resource->fetchAll();
		if ($res) {
			foreach ($res as $el) {
				$r = new Zend_Acl_Resource($el->id);
				$rp = $el->parentid ? new Zend_Acl_Resource($el->parentid) : null;
				$this->acl->addResource($r, $rp);
			}
		}

		$rs = $this->models->rule->fetchAll();
		if ($rs) {
			foreach ($rs as $el) {
				$rs_role = $this->models->rule_role->fetchCol('role', array(
					'`parentid` = ?' => $el->id
				));
				$rs_res = $this->models->rule_resource->fetchCol('resource', array(
					'`parentid` = ?' => $el->id
				));
				if ($el->rule) $this->acl->allow($rs_role, $rs_res);
				else $this->acl->deny($rs_role, $rs_res);
			}
		}

		return $this;
	}

	function logout() {
		if ($this->auth->hasIdentity()) {
			$this->auth->clearIdentity();
			$key = 'user_'.$this->models->user->info('name');
			$this->data = array();
			unset($_COOKIE[$key]);
			unset($s->$key);
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
		$key = 'user_'.$this->models->user->info('name');
		if ($password === null) {
			$data = $this->models->user->fetchRow(array(
				'`login` = ?' => $login
			));
			if ($data) {
				$result = $this->auth->getStorage()->write($login);
				$this->data = $data;
				if ($remember) setcookie(
					$key,
					sha1($this->data->login.$this->data->password),
					time() + 86400 * 30,
					'/'
				);
				return true;
			}
		}
		else {
			$this->auth_adapter	->setIdentity($login)
								->setCredential($password);
			$result = $this->auth->authenticate($this->auth_adapter);
			if ($result->isValid()) return $this->login($login, null, $remember);
		}
		return false;
	}

	function loginAuto() {
		if ($this->auth->hasIdentity()) $this->login($this->auth->getIdentity());
		else {
			$key = 'user_'.$this->models->user->info('name');
			$secret = @$_COOKIE[$key] ? $_COOKIE[$key] : '';
			$data = $this->models->user->fetchRow(array(
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
		$meta = $this->models->user->info('metadata');
		if ($data) foreach ($data as $k => $v) if (!array_key_exists($k, $meta)) unset($data[$k]);
		$data['password'] = sha1($data['password']);
		return $this->models->user->insert($data);
	}

	function update($data, $id = null) {
		$meta = $this->models->user->info('metadata');
		if ($data) foreach ($data as $k => $v) if (!array_key_exists($k, $meta)) unset($data[$k]);
		if (!$data) return false;
		$same = $id === null || $id == $this->data->id;
		if ($same) $id = $this->data->id;
		if ($id) {
			if (isset($data['password'])) $data['password'] = sha1($data['password']);
			$ok = $this->models->user->update($data, array(
				'`id` = ?' => $id
			));
			if ($same) $this->login($id);
			return $ok;
		}
		return false;
	}

	public function direct()
    {
        return $this;
    }
}