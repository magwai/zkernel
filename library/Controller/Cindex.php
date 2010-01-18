<?php

/**
 * @zk_title   		Панель: главная
 * @zk_routable		0
 */
class Zkernel_Controller_Cindex extends Zkernel_Controller_Action {
	public function ctlshowAction()
    {
    	$this->_helper->control()->config->set(array(
    		'type' => 'text',
    		'text' => '<p><strong>Панель управления</strong> предназначена для редактирования содержимого вашего сайта.</p><p>&nbsp;</p><p>С ее помощью Вы можете вносить изменения в содержимое статических и динамических страниц, загружать файлы на сервер. Все изменения производятся в режиме on-line и вступают в силу как только запрос на изменение был обработан на сервере.</p>',
			'navpane' => array(
    			'start' => array(array('t' => 'Главная', 'c' => 'cindex'))
    		)
    	));

    	$this->_helper->control()->routeDefault();
    }
}