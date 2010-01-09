<?php

class Magwai_Controller_Cuser extends Magwai_Controller_Action {
	function ctlinit() {
		$model = new Site_Model_Crole();
		$role = array('' => '[ не задано ]');
		$d = $model->fetchPairs('id', 'title');
		if ($d) $role += $d;

		$request = $this->getRequest();

    	$ex = array();
    	$pw = $request->getPost('password');
    	if ($pw) $ex = array(
    		'password' => sha1($pw)
    	);

		$this->_helper->control()->config->set(array(
			'post_field_unset' => array('password'),
			'post_field_extend' => $ex,
			'field' => array(
				'login' => array(
					'title' => 'Логин',
					'required' => true,
					'sortable' => true,
					'unique' => true
				),
				'password' => array(
					'title' => 'Пароль',
					'type' => 'password',
					'required' => true
				),
				'role' => array(
					'title' => 'Роль',
					'type' => 'select',
					'param' => array(
						'multioptions' => $role
					),
					'required' => true
				)
			)
		));
	}

	public function ctlshowAction()
    {
    	$this->_helper->control()->config->set(array(
			'field' => array(
				'password' => array(
					'active' => false
				),
				'role' => array(
					'active' => false
				)
			)
		));

    	$this->_helper->control()->routeDefault();
    }

	public function ctleditAction()
    {

    	$this->_helper->control()->config->set(array(
    		'field' => array(
				'password' => array(
					'required' => false
				)
			)
		));

    	$this->_helper->control()->routeDefault();
    }
}