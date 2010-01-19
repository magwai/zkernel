<?php

/**
 * @zk_title   		Интерфейс
 * @zk_routable 	0
 */
class Zkernel_Controller_Txt extends Zkernel_Controller_Action {
	function ctlinit() {
		$this->_helper->control()->config->set(array(
			'field' => array(
				'key' => array(
					'active' => $this->_helper->user()->acl->isAllowed(
						$this->_helper->user()->role,
						$this->_helper->util()->getById(array(
							'model' => new Default_Model_Cresource(),
							'field' => 'id',
							'key' => 'key',
							'id' => 'admin'
						))
					),
					'title' => 'Ключ',
					'required' => true,
					'sortable' => true,
					'unique' => true,
					'validators' => array(array(
						'validator' => 'Regex',
						'options' => array('/^[a-z0-9\_\-]*$/i')
					))
				),
				'title' => array(
					'title' => 'Заголовок',
					'required' => true,
					'sortable' => true,
					'unique' => true
				),
				'value' => array(
					'title' => 'Значение',
					'type' => 'textarea',
				)
			)
		));
	}

	function ctlshowAction() {
		$this->_helper->control()->config->set(array(
			'button_top' => $this->_helper->user()->acl->isAllowed(
				$this->_helper->user()->role,
				$this->_helper->util()->getById(array(
					'model' => new Default_Model_Cresource(),
					'field' => 'id',
					'key' => 'key',
					'id' => 'admin'
				))
			) ? array() : array('edit')
		));

		$this->_helper->control()->routeDefault();
	}
}