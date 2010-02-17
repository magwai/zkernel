<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @zk_title   		Панель: участники - роли
 * @zk_routable		0
 */
class Zkernel_Controller_Crole extends Zkernel_Controller_Action {
	function ctlinit() {
		$request = $this->getRequest();

		$tt = $this->model->fetchPairs('id', 'title', array(
			'`id` != ?' => (int)$request->getParam('id')
		));
		if ($tt) $ft = array(
			'title' => 'Родители',
			'type' => 'multiCheckbox',
			'param' => array(
				'multioptions' => $tt
			),
			'm2m' => array(
				'model' => new Default_Model_Crolerefer(),
				'self' => 'role',
				'foreign' => 'parentid'
			)
		);
		else $ft = array(
			'active' => false
		);
		$this->_helper->control()->config->set(array(
			'field' => array(
				'title' => array(
					'title' => 'Название',
					'required' => true,
					'sortable' => true,
					'unique' => true
				),
				'role' => $ft
			)
		));
	}

	public function ctlshowAction()
    {
    	$this->_helper->control()->config->set(array(
    		'field' => array(
    			'role' => array(
    				'active' => false
    			)
    		)
		));

    	$this->_helper->control()->routeDefault();
    }
}