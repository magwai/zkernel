<?php

/**
 * @zk_title   		Панель: запрос к БД
 * @zk_routable		0
 */
class Zkernel_Controller_Query extends Zkernel_Controller_Action {
	function configAction() {
		$model = new Default_Model_Page();
		$o = $model->getAdapter()->getConfig();

	}
}