<?php

class Magwai_Controller_Page extends Magwai_Controller_Action {
	function indexAction() {
		$id = $this->getRequest()->getParam('id');
		$this->view->card = $this->model->fetchRow(array(
			'`stitle` = ?' => $id
		));
		if (!$this->view->card) $this->view->card = $this->model->fetchRow('`stitle` = "error"');
	}

	function ctlinit() {
		$this->_helper->control()->config->set(array(
			'static_field' => true,
			'field' => array(
				'title' => array(
					'title' => 'Заголовок',
					'required' => true,
					'sortable' => true
				),
				'stitle' => array(
					'title' => 'Псевдоним',
					'required' => true,
					'formatter' => 'function',
					'formatoptions' => 'return Number(data.cedit) ? "/page/<strong>" + data.stitle + "</strong>/" : "";',
					'sortable' => true,
					'validators' => array(array(
						'validator' => 'Regex',
						'options' => array('/^[a-z0-9\_\-]*$/i')
					))
				),
				'message' => array(
					'title' => 'Текст',
					'type' => 'mce',
					'required' => true
				),
				'cedit' => array(
					'title' => 'Можно редактировать',
					'type' => 'select',
					'param' => array(
						'multioptions' => array('1' => 'Да', '0' => 'Нет')
					)
				)
			)
		));
	}

	function ctlshowAction() {
		$this->_helper->control()->config->set(array(
			'button_top' => array('add', 'edit', 'delete'),
			'field' => array(
				'message' => array(
					'active' => false
				),
				'cedit' => array(
					'hidden' => true
				)
			)
		));

		$this->_helper->control()->routeDefault();
	}

	function ctladdAction() {
		$this->_helper->control()->config->set(array(
			'field' => array(
				'stitle' => array(
					'active' => false
				),
				'cedit' => array(
					'active' => $this->_helper->user()->acl->isAllowed(
						$this->_helper->user()->role,
						$this->_helper->util()->getById(array(
							'model' => new Site_Model_Cresource(),
							'field' => 'id',
							'key' => 'key',
							'id' => 'admin'
						))
					)
				)
			)
		));

		$this->_helper->control()->routeDefault();
	}

	function ctleditAction() {
		$this->_helper->control()->config->set(array(
			'static_field' => false,
			'field' => array(
				'stitle' => array(
					'unique' => true
				),
				'cedit' => array(
					'active' => $this->_helper->user()->acl->isAllowed(
						$this->_helper->user()->role,
						$this->_helper->util()->getById(array(
							'model' => new Site_Model_Cresource(),
							'field' => 'id',
							'key' => 'key',
							'id' => 'admin'
						))
					)
				)
			)
		));

		$this->_helper->control()->routeDefault();
	}
}