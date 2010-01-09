<?php

class Magwai_Controller_Crole extends Magwai_Controller_Action {
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
				'model' => new Site_Model_Crolerefer(),
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