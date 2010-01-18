<?php

/**
 * @zk_title   		Панель: участники - ресурсы
 * @zk_routable		0
 */
class Zkernel_Controller_Cresource extends Zkernel_Controller_Action {
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