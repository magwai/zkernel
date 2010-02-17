<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @zk_title   		Панель: участники - пользователи
 * @zk_routable		0
 */
class Zkernel_Controller_Cuser extends Zkernel_Controller_Action {
	function ctlinit() {
		$model = new Default_Model_Crole();
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
			'field_title' => 'login',
			'post_field_unset' => array('password'),
			'post_field_extend' => $ex,
			'field' => array(
				'active' => array(
					'active' => false
				),
				'login' => array(
					'title' => 'Логин',
					'required' => true,
					'sortable' => true,
					'unique' => true,
					'order' => 1
				),
				'password' => array(
					'title' => 'Пароль',
					'type' => 'password',
					'required' => true,
					'order' => 2
				),
				'role' => array(
					'title' => 'Роль',
					'type' => 'select',
					'param' => array(
						'multioptions' => $role
					),
					'required' => true,
					'order' => 3
				)
			),
			'action_config' => array(
				'ctlshow' => array(
					'field' => array(
						'password' => array(
							'active' => false
						),
						'role' => array(
							'active' => false
						)
					)
				),
				'ctledit' => array(
					'field' => array(
						'password' => array(
							'required' => false
						)
					)
				)
			)
		));
	}
}