<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @zk_title   		Панель управления
 * @zk_config		0
 * @zk_routable		0
 */
class Zkernel_Controller_Control extends Zkernel_Controller_Action {
	public function authAction() {
		$this->view->post = $_POST;
	}

	public function multiAction() {
		$this->view->id = @(int)$this->getRequest()->getParam('id');
	}
}

