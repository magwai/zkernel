<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @zk_title   		Сервисный
 * @zk_routable		0
 */
class Zkernel_Controller_Z extends Zkernel_Controller_Action {
	function pointAction() {
		$this->view->color = str_replace('-', '#', $this->getRequest()->getParam('color'));
		$this->view->coord_original = $this->getRequest()->getParam('coord');
		$this->view->coord = explode(';', $this->getRequest()->getParam('coord'));
		if ($this->view->coord) {
			foreach ($this->view->coord as $k => $el) $this->view->coord[$k] = explode('|', $el);
		}
	}
}