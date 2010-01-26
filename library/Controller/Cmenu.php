<?php

/**
 * @zk_title   		Панель: меню
 * @zk_routable		0
 */
class Zkernel_Controller_Cmenu extends Zkernel_Controller_Action {
	function ctlinit() {
		$m = new Default_Model_Cresource();
		$ttt = array('0' => '[ без ограничений ]');
		$tt = $m->fetchPairs();
		if ($tt) $ttt += $tt;

		$this->_helper->control()->config->set(array(
			'tree' => true,
			'field' => array(
				'title' => array(
					'title' => 'Название',
					'required' => true,
					'order' => 1
				),
				'orderid' => array(
					'active' => false
				),
				'parentid' => array(
					'active' => false
				),
				'resource' => array(
					'title' => 'Ресурс',
					'type' => 'select',
					'param' => array(
						'multioptions' => $ttt
					)
				),
				'controller' => array(
					'title' => 'Контроллер',
					'unique' => true,
					'order' => 2
				),
				'action' => array(
					'title' => 'Действие',
					'order' => 3
				),
				'param' => array(
					'title' => 'Параметры',
					'order' => 4
				),
				'show_it' => array(
					'title' => 'Отображать',
					'type' => 'select',
					'param' => array(
						'multioptions' => array(
							'1' => 'Да',
							'0' => 'Нет'
						)
					)
				)
			),
			'orderby' => 'orderid',
			'func_success' => 'php_function:Zend_Controller_Action_HelperBroker::getStaticHelper("js")->addEval("c.load_menu();");'
		));
	}

	public function ctlshowAction()
    {
    	$this->_helper->control()->config->set(array(
    		'field' => array(
    			'resource' => array(
    				'active' => false
    			),
    			'show_it' => array(
    				'formatter' => 'function',
    				'formatoptions' => 'return Number(data.show_it) ? "Да" : "Нет";'
    			)
    		)
		));

    	$this->_helper->control()->routeDefault();
    }

	public function ctldragAction()
    {
    	$this->_helper->control()->config->set(array(
    		'func_success' => 'php_function:Zend_Controller_Action_HelperBroker::getStaticHelper("js")->addEval("c.load_menu();");'
		));

    	$this->_helper->control()->routeDefault();
    }
}