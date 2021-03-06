<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_User extends Zend_Application_Resource_ResourceAbstract {
	const DEFAULT_REGISTRY_KEY = 'Zkernel_User';
	protected $_user = null;

	public function init () {
		if (null === $this->_user && !preg_match('/\/fu$/i', $_SERVER['REQUEST_URI'])) {
			$options = $this->getOptions();

			if (!isset($options['role'])) $options['role'] = 'Default_Model_Role';
			if (!isset($options['role_refer'])) $options['role_refer'] = 'Default_Model_Rolerefer';
			if (!isset($options['resource'])) $options['resource'] = 'Default_Model_Resource';
			if (!isset($options['rule'])) $options['rule'] = 'Default_Model_Rule';
			if (!isset($options['rule_role'])) $options['rule_role'] = 'Default_Model_Rulerole';
			if (!isset($options['rule_resource'])) $options['rule_resource'] = 'Default_Model_Ruleresource';
			if (!isset($options['user'])) $options['user'] = 'Default_Model_User';

			$class_role = $options['role'];
			$class_role_refer = $options['role_refer'];
			$class_resource = $options['resource'];
			$class_rule = $options['rule'];
			$class_rule_role = $options['rule_role'];
			$class_rule_resource = $options['rule_resource'];
			$class_user = $options['user'];

			$this->_user = new Zkernel_User(array(
				'role' => class_exists($class_role) ? new $class_role() : null,
				'role_refer' => class_exists($class_role_refer) ? new $class_role_refer() : null,
				'resource' => class_exists($class_resource) ? new $class_resource() : null,
				'rule' => class_exists($class_rule) ? new $class_rule() : null,
				'rule_role' => class_exists($class_rule_role) ? new $class_rule_role() : null,
				'rule_resource' => class_exists($class_rule_resource) ? new $class_rule_resource() : null,
				'user' => new $class_user()
			));
			$this->_user->initAcl();
			$this->_user->loginAuto();

			$key = (isset($options['registry']) && !is_numeric($options['registry'])) ? $options['registry'] : self::DEFAULT_REGISTRY_KEY;
			Zend_Registry::set($key, $this->_user);
		}
		return $this->_user;
	}
}