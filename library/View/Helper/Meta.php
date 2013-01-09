<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Meta extends Zend_View_Helper_Abstract  {
	public function auto($data) {
		if (is_string($data)) $data = array('title' => $data);
		$data['keywords'] = isset($data['keywords']) ? $data['keywords'] : $data['title'];
		$data['description'] = isset($data['description']) ? $data['description'] : $data['title'];
		$words = preg_replace('/(\,|\-|\;|\_|\.)/si', '', $data['keywords']);
		do $words = str_replace('  ', ' ', $words, $count);
		while($count);
		$this->_set_meta('title', $data['title']);
		$this->_set_meta('keywords', str_replace(' ', ', ', $words));
		$this->_set_meta('description', $data['description']);
	}

	public function _set_meta($key, $str) {
		$f = substr($str, 0, 1);
		$data = trim($str, ' +-');
		if ($key == 'title') $this->view->headTitle($data, ($f == '+' ? 'APPEND' : ($f == '-' ? 'PREPEND' : 'SET')));
		else {
			$data = $this->view->escape($data);
			$this->view->headMeta($data, $key, 'name', array(), 'SET');
		}
	}

	public function meta($oid = null, $param = array()) {
		if ($oid == 'inst') {
			return $this;
		}
		if ($oid == 'auto') {
			$this->auto($param);
			return;
		}
		$model = new Default_Model_Meta();
		if ($oid) {
			$meta = $model->fetchOid($oid);
		}
		else {
			$meta = $model->fetchMatch(@$_SERVER['REQUEST_URI']);
		}
		if ($meta) {
			$meta = $this->view->override()->overrideSingle($meta, 'meta');
			if (@$meta['description']) $this->_set_meta('description', $meta['description']);
			if (@$meta['keywords']) $this->_set_meta('keywords', $meta['keywords']);
			if (@$meta['title']) {
				$titles = $this->view->headTitle()->getValue();
				$this->_set_meta('title', $meta['title']);
				if (@$meta['show_title']) {
					$f = substr($meta['title'], 0, 1);
					if (!$oid && $f != '+' && $f != '-') {
						$titles = is_array($titles) ? $titles[0] : $titles;
						if ($titles && $f != '+' && $f != '-') $this->view->headTitle($this->view->escape($titles), 'PREPEND');
					}
				}
				else if (@$title && $f != '+' && $f != '-') $this->view->headTitle($title, 'SET');
			}
		}
    }
}
