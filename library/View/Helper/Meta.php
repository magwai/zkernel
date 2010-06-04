<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Meta extends Zend_View_Helper_Abstract  {
	public function meta() {
		$model = new Default_Model_Meta();
		$meta = $model->fetchMatch(@$_SERVER['REQUEST_URI']);
		if ($meta) {
			if (@$meta['keywords']) $this->view->headMeta($this->view->escape($meta['keywords']), 'keywords');
			if (@$meta['description']) $this->view->headMeta($this->view->escape($meta['description']), 'description');
			if (@$meta['title']) $this->view->headTitle($this->view->escape($meta['title']), 'SET');
		}
    }
}