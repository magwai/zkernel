<?php

/**
 * @zk_title   		Страницы
 * @zk_routable		0
 * @zk_routes		page/(.*)|id
 */
class Zkernel_Controller_Page extends Zkernel_Controller_Action {
	function indexAction() {
		$id = $this->getRequest()->getParam('id');
		$this->view->card = $this->model->fetchRow(array(
			'`stitle` = ?' => $id
		));
		if (!$this->view->card) $this->view->card = $this->model->fetchRow('`stitle` = "error"');
		$sp = preg_split('/\<hr(\ )\/\>/si', $this->view->card->message);
		if (count($sp) > 1) {
			array_shift($sp);
			$this->view->card->message = preg_replace('/^\<\/p\>/i', '', trim(implode('<hr />', $sp)));
		}
	}

	function ctlinit() {
		$this->_helper->control()->config->set(array(
			'static_field' => true,
			'field' => array(
				'title' => array(
					'title' => 'Заголовок',
					'required' => true,
					'sortable' => true,
					'order' => 1
				),
				'stitle' => array(
					'title' => 'Псевдоним',
					'required' => true,
					'formatter' => 'function',
					'formatoptions' => 'return Number(data.cedit) || '.$this->_helper->user()->acl->isAllowed(
						$this->_helper->user()->role,
						$this->_helper->util()->getById(array(
							'model' => new Default_Model_Cresource(),
							'field' => 'id',
							'key' => 'key',
							'id' => 'admin'
						))
					).' ? "/page/<strong>" + data.stitle + "</strong>/" : "";',
					'sortable' => true,
					'validators' => array(array(
						'validator' => 'Regex',
						'options' => array('/^[a-z0-9\_\-]*$/i')
					)),
					'order' => 2
				),
				'message' => array(
					'title' => 'Текст',
					'type' => 'mce',
					'required' => true,
					'order' => 3
				),
				'cedit' => array(
					'title' => 'Можно редактировать',
					'type' => 'select',
					'param' => array(
						'multioptions' => array('1' => 'Да', '0' => 'Нет')
					),
					'order' => 4
				)
			)
		));
	}

	function ctlshowAction() {
		$this->_helper->control()->config->set(array(
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
					'active' => $this->_helper->user()->acl->isAllowed(
						$this->_helper->user()->role,
						$this->_helper->util()->getById(array(
							'model' => new Default_Model_Cresource(),
							'field' => 'id',
							'key' => 'key',
							'id' => 'admin'
						))
					)
				),
				'cedit' => array(
					'active' => $this->_helper->user()->acl->isAllowed(
						$this->_helper->user()->role,
						$this->_helper->util()->getById(array(
							'model' => new Default_Model_Cresource(),
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
					'unique' => true,
					'active' => $this->_helper->user()->acl->isAllowed(
						$this->_helper->user()->role,
						$this->_helper->util()->getById(array(
							'model' => new Default_Model_Cresource(),
							'field' => 'id',
							'key' => 'key',
							'id' => 'admin'
						))
					) || (int)$this->model->fetchOne('cedit', array('`id` = ?' => $this->getRequest('id')))
				),
				'cedit' => array(
					'active' => $this->_helper->user()->acl->isAllowed(
						$this->_helper->user()->role,
						$this->_helper->util()->getById(array(
							'model' => new Default_Model_Cresource(),
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

	function _getRoutes() {
		$model = new Default_Model_Url();
		$route = $model->fetchOne('id', array('`url` = "page/(.*)"'));
		return $route ? $this->model->fetchPairs('CONCAT(`stitle`, "|dbroute'.$route.'")', 'title', '`cedit` = 1', 'title') : array();
	}
}