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
	public function unicontrolAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$app_dir = str_replace(PUBLIC_PATH.'/', '', APPLICATION_PATH);
		define('PATH_ROOT', PUBLIC_PATH);
		define('DIR_APPLICATION', $app_dir);
		define('DIR_LIBRARY', $app_dir.'/../library/Zkernel/Other/Lib/ekernel');
		define('DIR_ZLIBRARY', $app_dir.'/../library/Zkernel/Other/Lib/ekernel-application');
		define('DIR_KERNEL', 'zkernel/ctl/ekernel');
		define('DIR_UPLOAD', 'upload');
		define('DIR_CACHE', 'pc');
		define('DIR_DATA', $app_dir.'/../data');

		require_once PATH_ROOT.'/'.DIR_LIBRARY.'/application.php';

		k_application::get_instance()->bootstrap()->run();
		exit();
	}

	public function authAction() {
		$this->view->post = $_POST;
	}

	public function multiAction() {
		$this->view->id = @(int)$this->getRequest()->getParam('id');
	}
}

