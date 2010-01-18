<?php

/**
 * @zk_title   		Интерфейс
 * @zk_routable 	0
 */
class Zkernel_Controller_Txt extends Zkernel_Controller_Action {
	function ctlinit() {
		$this->_helper->control()->config->set(array(
			'field' => array(
				'title' => array(
					'title' => 'Заголовок',
					'required' => true,
					'sortable' => true,
					'unique' => true
				),
				'key' => array(
					'title' => 'Ключ',
					'required' => true,
					'sortable' => true,
					'unique' => true,
					'validators' => array(array(
						'validator' => 'Regex',
						'options' => array('/^[a-z0-9\_\-]*$/i')
					))
				),
				'value' => array(
					'title' => 'Значение',
					'type' => 'textarea',
				)
			)
		));
	}
}