<?php

/**
 * @zk_title   		Сжатие JS и CSS
 * @zk_config		0
 * @zk_routable		0
 */
class Zkernel_Controller_Minify extends Zend_Controller_Action {
	public function indexAction() {
		$this->view->ext = $this->getRequest()->getParam('ext');
		$this->view->path = $this->getRequest()->getParam('path');
    }
}

