<?php

class Zkernel_Controller_Plugin_User extends Zend_Controller_Plugin_Abstract {
	public function routeStartup(Zend_Controller_Request_Abstract $request) {
		if (strpos($_SERVER['REQUEST_URI'], '/fu') !== false) return;

		$user = new Zkernel_User(array(
			'role' => new Default_Model_Role(),
			'role_refer' => new Default_Model_Rolerefer(),
			'resource' => new Default_Model_Resource(),
			'rule' => new Default_Model_Rule(),
			'rule_role' => new Default_Model_Rulerole(),
			'rule_resource' => new Default_Model_Ruleresource(),
			'user' => new Default_Model_User()
		));
		$user->initAcl();
		$user->loginAuto();
		Zend_Registry::set('Zkernel_User', $user);
    }
}

