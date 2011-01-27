<?php

/**
 * @zk_title   		Страницы
 * @zk_routable		0
 * @zk_routes		page/(.*)|id
 */
class Zkernel_Controller_Page extends Zkernel_Controller_Action {
	function indexAction() {
		$this->view->id = $this->getRequest()->getParam('id');
	}

	function _getRoutes() {
		$model = new Default_Model_Url();
		$route = $model->fetchOne('id', array('`url` = "page/(.*)"'));
		$ret = array();
		$res = $this->model->fetchAll('`cedit` = 1', 'title');
		if ($res) {
			$res = $this->view->override($res, 'page');
			foreach ($res as $el) $ret[$el->stitle.'|dbroute'.$route] = $el->title;
		}
		return $ret;
	}
}