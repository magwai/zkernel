<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @zk_title   		Загрузка файлов
 * @zk_config		0
 * @zk_routable		0
 */
class Zkernel_Controller_Fu extends Zkernel_Controller_Action {
	function indexAction() {
		$this->view->post = $_POST;
		$this->view->files = $_FILES;
	}
}