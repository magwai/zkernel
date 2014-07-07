<?php

$tt = array('' => '[ не указан ]');
$data = Zkernel_Common::getControllerDocblock();
if ($data) {
	foreach ($data as $n => $db) {
		$nn = $n;
		$c = ucfirst($n).'Controller';
		if (isset($db['zk_title'])) $nn = $db['zk_title'];
		$inner = array();
		$r = new Zend_Reflection_Class($c);
		$met = $r->getMethods();
		if (@$met) {
			$exist = false;
			foreach ($met as $el) {
				if ($el->name == '_getRoutes') {
					$rq = new Zend_Controller_Request_Http();
					$rq->setControllerName($n);
					$ci = new $c(
						$rq,
						new Zend_Controller_Response_Http()
					);
					$ci->init();
					$rs = $ci->_getRoutes();
					if ($rs) foreach ($rs as $k_1 => $el_1) {
						$is_route = strpos($k_1, '|');
						$ap = 'index';
						$pp = explode('@', $k_1);
						if (count($pp) == 2) {
							$ap = $pp[0];
							$k_1 = $pp[1];
						}
						else $k_1 = $pp[0];
						$inner[$n.'|'.($is_route === false ? $k_1 : $ap.'|'.$k_1)] = $el_1;
					}
					break;
				}
			}
    		if (!$exist) foreach ($met as $el) {
    			$db1 = Zkernel_Common::getDocblock($el, 'method');
    			if (substr($el->name, -6) == 'Action') {
    				$db1 = Zkernel_Common::getDocblock($el, 'method');
    				if ($db1 && array_key_exists('zk_routable', $db1) && !(int)$db1['zk_routable']) continue;
    				if (!isset($db1['zk_title'])) continue;
    				$inner[$n.'|'.substr($el->name, 0, -6)] = $db1['zk_title'];
    			}
    		}
    	}
		if ($db && array_key_exists('zk_routable', $db) && !(int)$db['zk_routable']) {
			if ($inner) $tt[$nn] = $inner;
		}
		else {
			$tt[$n] = $nn;
			if ($inner) {
				foreach ($inner as $k => $v) $inner[$k] = '---'.$v;
				$tt = array_merge($tt, $inner);
			}
		}
	}
}

function cb_before(&$control) {
	if (@$control->config->data->cap && !@$control->config->data->url) {
		$p = explode('|', $control->config->data->cap);
		$control->config->data->controller = @$p[0];
		$control->config->data->action = @$p[1];
		$control->config->data->param = @$p[2];
		$control->config->data->route = @$p[3];
	}
	else {
		$control->config->data->controller = '';
		$control->config->data->action = '';
		$control->config->data->param = '';
		$control->config->data->route = '';
	}
}

$cap_value = '';
if ($this->config->action == 'edit') {
	$item = $this->config->model->fetchRow(array("`id` = ?" => (int)$this->config->param["id"]));
	$cap_value = $item ? @$item->controller.
		(@$item->action ? "|".$item->action : "").
		(@$item->param ? "|".$item->param : "").
		(@$item->route ? "|".$item->route : "") : "";
}

$this->control(array(
	'tree' => true,
	'field' => array(
		'title' => array(
			'title' => 'Название',
			'order' => 1,
			'required' => true
		),
		'key' => array(
			'title' => 'Ключ',
			'order' => 2,
			'active' => $this->view->user()->is_allowed_by_key('admin')
		),
		'cap' => array(
			'title' => 'Раздел сайта',
			'type' => 'select',
			'item' => $tt,
			'value' => $cap_value,
			'order' => 3
		),
		'url' => array(
			'title' => 'URL',
			'description' => 'Если указан URL, то он будет использован вместо указанного выше раздела сайта',
			'order' => 4
		),
		'controller' => array(
			'active' => false
		),
		'action' => array(
			'active' => false
		),
		'param' => array(
			'active' => false
		),
		'route' => array(
			'active' => false
		),
		'map' => array(
			'active' => false
		)
	),
	'callback' => array(
		'before' => function($control) {
			$control->view->navigation()->control_encode($control);
		},
		'preset' => function($control) {
			$control->view->navigation()->control_decode($control);
		}
	),
	'config_action' => array(
		'index' => array(
			'field' => array(
				'url' => array(
					'active' => false
				),
				'cap' => array(
					'active' => false
				)
			)
		),
		'add' => array(
			'callback' => array(
				'before' => function(&$control) {
					cb_before($control);
				}
			)
		),
		'edit' => array(
			'callback' => array(
				'before' => function(&$control) {
					cb_before($control);
				}
			)
		)
	)
));