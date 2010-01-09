<?php

class Magwai_Controller_Cresource extends Magwai_Controller_Action {
	function ctlinit() {
		$this->_helper->control()->config->set(array(
			'tree' => true,
			'field' => array(
				'title' => array(
					'title' => 'Название',
					'required' => true,
					'sortable' => true,
					'unique' => true
				),
				'key' => array(
					'title' => 'Ключ',
					'sortable' => true,
					'unique' => true
				),
				'parentid' => array(
					'active' => false
				)
			)
		));
	}

	public function ctlshowAction()
    {
    	$this->_helper->control()->config->set(array(
    		'field' => array(
    			'resource' => array(
    				'active' => false
    			)
    		)
		));

    	$this->_helper->control()->routeDefault();
    }
}