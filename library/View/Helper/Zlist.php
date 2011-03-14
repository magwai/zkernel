<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Zlist extends Zend_View_Helper_Abstract  {
	public function zlist($data = array()) {
		$res = '';
		$lv = array();

		$reg = Zend_Registry::isRegistered('Zkernel_Multilang') ? Zend_Registry::get('Zkernel_Multilang') : '';

		$data['fetch_data'] = isset($data['fetch_data']) ? $data['fetch_data'] : array();
		
		$data['fetch_model'] = class_exists($data['fetch_model']) ? $data['fetch_model'] : 'Default_Model_'.ucfirst($data['fetch_model']);

		$data['fetch_param'] = @$data['fetch_param'] ? (is_array($data['fetch_param']) ? $data['fetch_param'] : array($data['fetch_param'])) : array();

		$data['fetch_method'] = @$data['fetch_method'] ? $data['fetch_method'] : 'list';
		if (!method_exists($data['fetch_model'], $data['fetch_method'])) $data['fetch_method'] = 'fetch'.ucfirst($data['fetch_method']);

		$data['override_type'] = @$data['override_type'];
		if (!$data['override_type']) $data['override_type'] = strtolower(str_ireplace('Default_Model_', '', $data['fetch_model']));

		$data['view_script'] = @$data['view_script'];
		if (!$data['view_script']) $data['view_script'] = strtolower(str_ireplace('Default_Model_', '', $data['fetch_model'])).'/'.strtolower(str_ireplace('fetch', '', $data['fetch_method']));
		if (stripos($data['view_script'], '/') === false) $data['view_script'] .= '/index';
		if (stripos($data['view_script'], '.phtml') === false) $data['view_script'] .= '.phtml';

		$data['view_empty'] = isset($data['view_empty']) ? $data['view_empty'] : true;

		if (!@$data['pager'] && (@$data['pager_url'] || @$data['pager_page'] || @$data['pager_perpage'] || @$data['pager_style'] || @$data['pager_script'] || @$data['pager_param'])) $data['pager'] = true;

		$data['pager_url'] = @$data['pager_url'];
		if (!$data['pager_url']) $data['pager_url'] = ($reg ? '/'.$reg->stitle : '').'/'.strtolower(str_ireplace('Default_Model_', '', $data['fetch_model']));

		$data['pager_page'] = @$data['pager_page'];
		if (!$data['pager_page']) $data['pager_page'] = @$this->view->page ? $this->view->page : 1;

		$data['pager_perpage'] = @$data['pager_perpage'];
		if (!$data['pager_perpage']) $data['pager_perpage'] = @$this->view->perpage ? $this->view->perpage : 10;

		$data['pager_style'] = @$data['pager_style'] ? $data['pager_style'] : 'All';

		$data['pager_script'] = @$data['pager_script'] ? $data['pager_script'] : 'pager';
		if (stripos($data['pager_script'], '/') === false) $data['pager_script'] .= '/index';
		if (stripos($data['pager_script'], '.phtml') === false) $data['pager_script'] .= '.phtml';

		$data['pager_param'] = @$data['pager_param'];

		if (!$data['fetch_data']) {
			$class = $data['fetch_model'];
			$class = new $class();
		}

		$list = $data['fetch_data'] ? $data['fetch_data'] : call_user_func_array(
			array(
				$class,
				$data['fetch_method']
			),
			$data['fetch_param']
		);

		if ($list instanceOf Zend_Db_Select) {
			if (@$data['pager']) $lv = $list;
			else $list = $class->getAdapter()->fetchAll($list);
		}
		if (!$lv && count($list)) {
			$lv = $data['override_type'] == 'none' ? $list : $this->view->override($list, $data['override_type']);
			$reindex = false;
			foreach ($lv as $k => $v) if (@$v->_skip) {
				unset($lv[$k]);
				$reindex = true;
			}
			if ($reindex && count($lv)) {
				$num = 0;
				foreach ($lv as $k => $v) {
					if ($k != $num) {
						unset($lv[$k]);
						$lv[$num] = $v;
					}
					$num++;
				}
			}
			$this->view->data = $lv;
		}
		else {
			$this->view->data = array();
		}
		if (@$data['pager']) {
			$paginator = Zend_Paginator::factory($lv);
			$paginator->setItemCountPerPage($data['pager_perpage']);
			$paginator->setCurrentPageNumber($data['pager_page']);
			$this->view->data = $list instanceOf Zend_Db_Select
				? $this->view->override($paginator, $data['override_type'])
				: $paginator;
			$this->view->pager_count = $paginator->getTotalItemCount();
			$pager_param = array(
				'url' => $data['pager_url']
			);
			if ($data['pager_param']) $pager_param = array_merge($pager_param, $data['pager_param']);
			$this->view->pager = $this->view->paginationControl(
				$paginator,
				$data['pager_style'],
				$data['pager_script'],
				$pager_param
			);
		}
		return $this->view->data || $data['view_empty']
			? $this->view->render($data['view_script'])
			: '';
	}
}