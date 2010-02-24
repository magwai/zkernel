<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Controller_Action_Helper_Control extends Zend_Controller_Action_Helper_Abstract  {
	public $config;

	function init($action = null, $controller = null) {
		$controller = $controller === null
			? $this->getRequest()->getControllerName()
			: $controller;
		$action = $action === null
			? $this->getRequest()->getActionName()
			: $action;

		$m = 'Default_Model_'.ucfirst($controller);

		if (@class_exists($m)) {
			$model = new $m();
			if ($this->getActionController() && $model->info('name') == $this->getActionController()->model->info('name')) $model = $this->getActionController()->model;
		}

		$this->config = new Zkernel_Config_Control(array(
			'theme'					=> 'redmond',
			'model' 				=> $model,
			'where'					=> null,
			'tree'					=> false,
			'tree_field'			=> 'parentid',
			'tree_opened'			=> array(),
			'field_orderid'			=> 'orderid',
			'field_title'			=> 'title',
			'field_link'			=> 'parentid',
			'param_link'			=> 'cid',
			'controller' 			=> $controller,
			'action' 				=> $action,
			'stop_frame' 			=> false,
			'info' 					=> array(),
			'info_type' 			=> 'i',
			'type' 					=> '',
			'text' 					=> '',
			'button_top' 			=> array(),
			'button_bottom' 		=> array(),
			'scroll_top' 			=> true,
			'use_db' 				=> true,
			'place'					=> 'Просмотр',
			'request_ok' 			=> array(
	    		'controller' 			=> $controller,
	    		'action' 				=> '',
	    		'param' 				=> ''
	    	),
	    	'request_cancel' 		=> array(
	    		'controller' 			=> $controller,
	    		'action' 				=> '',
	    		'param' 				=> ''
	    	),
	    	'oac_apply' 			=> true,
	    	'post_field_extend'		=> array(),
	    	'post_field_uset'		=> array(),
	    	'orderby' 				=> '',
	    	'orderdir' 				=> 'asc',
	    	'field' 				=> array(),
			'formatter_function'	=> array(),
	    	'pager_perpage' 		=> 0,
		   	'pager_page' 			=> 1,
	    	'pre_view'				=> null,
	    	'func_override'			=> null,
	    	'func_success'			=> null,
	    	'func_preset'			=> null,
	    	'navpane'				=> array(
	    		'start' => array(),
	    		'middle' => true,
	    		'finish' => array()
	    	),
	    	'data' => array(),
	    	'static_field' => false
		));
		return $this;
	}

	function configFromType() {
		if ($this->config->tree) $this->config->drag = 1;

		if (!$this->config->view) {
			switch ($this->config->type) {
				case 'add':
					$this->config->oac_apply = false;
					$this->config->place = 'Добавление';
					$view = 'form';
					break;
				case 'edit':
					$view = 'form';
					$this->config->place = 'Изменение';
					break;
				case 'list':
					$view = 'jqgrid';
					if (!count($this->config->button_top)) $this->config->button_top = array('add', 'edit', 'delete');
					break;
				case 'text':
					$view = 'text';
					break;
				case 'delete':
					$view = 'delete';
					break;
				case 'drag':
					$view = 'drag';
					break;
				case 'none':
					$view = 'none';
					break;
				case 'form':
					$view = 'form';
					break;
				default:
					$view = 'error';
					break;
			}

			$this->config->view = 'control/'.$view.'.phtml';
		}

		if (isset($this->config->action_config->{$this->config->action})) $this->config->set($this->config->action_config->{$this->config->action});

		return $this;
	}

	function configFromDb() {
		// Получаем настройки из БД
    	$mc = new Default_Model_Cconf();

    	$conf = $mc->fetchPairs(null, '', '');
    	if (!$conf) $conf = array();

    	$conf_c = $mc->fetchPairs(null, '', $this->config->controller);
    	if ($conf_c) $conf = array_merge($conf, $conf_c);

    	$conf_s = $mc->fetchPairs(null, $this->config->action, $this->config->controller);
    	if ($conf_s) $conf = array_merge($conf, $conf_s);

    	//print_r($this->config->action);
    	//exit();

    	//$this->config->set($conf_s);

		if (isset($conf->use_db)) $this->config->use_db = $conf->use_db;


		// Поля из модели
    	if ($this->config->use_db && $this->config->model) {
    		$meta = $this->config->model->info('metadata');
    		if ($meta) {
    			foreach ($meta as $el) {
					if (!isset($this->config->field->{$el['COLUMN_NAME']})) $this->config->field->{$el['COLUMN_NAME']} = array();
    			}
    		}
    	}

    	// Поля из конфига
		foreach ($conf as $_k => $_v) {
    		$kk = 'field_';
			if (substr($_k, 0, strlen($kk)) == $kk) {
				$k = explode('_', $_k);
				$k = @$k[1];

				$kk = 'field_'.$k.'_param_';

				if (substr($_k, 0, strlen($kk)) == $kk) {
					$ak = str_replace($kk, '', $_k);
					$this->config->field->$k->param->$ak = $_v;

				}
				else {
					$ak = str_replace('field_'.$k.'_', '', $_k);
					$this->config->field->set($k, array($ak => $_v));

				}
				unset($conf[$_k]);
			}
		}

		$this->config->set($conf);

		if ($this->config->field && !$this->config->drag && isset($this->config->field->{$this->config->field_orderid})) $this->config->drag = 1;

		if ($this->config->field && ($this->config->drag || $this->config->type == 'drag') && !$this->config->orderby && isset($this->config->field->{$this->config->field_orderid})) $this->config->orderby = $this->config->field_orderid;

		if ($this->config->field && !$this->config->orderby) foreach ($this->config->field as $k => $el) {
			if ($el->active && !$el->hidden) {
				$this->config->orderby = $k;
				break;
			}
		}

		if ($this->config->field) {
			$d = array();
			$d1 = $this->config->field->toArray();
			foreach ($d1 as $k => $v) {
				$d[$k] = $v['order'];
				unset($this->config->field->$k);
			}
			array_multisort($d, SORT_ASC, SORT_NUMERIC, $d1);
			$this->config->field->set($d1);
		}

		if (!$this->config->drag && !$this->config->tree) $this->config->pager_perpage = 100;

    	return $this;
	}

	function configFromRequest() {
		$request = $this->getRequest();
    	if ($request->getParam('orderby')) $this->config->orderby = $request->getParam('orderby');
    	if ($request->getParam('orderdir')) $this->config->orderdir = $request->getParam('orderdir');
    	if ($request->getParam('page')) $this->config->pager_page = $request->getParam('page');
    	if ($this->config->tree && $request->getParam('oid')) {
    		$util = Zend_Controller_Action_HelperBroker::getStaticHelper('util');
    		$this->config->tree_opened = $util->getOuterIds(array(
    			'model' => $this->config->model,
    			'id' => $request->getParam('oid')
    		));
    		$this->config->tree_opened[] = $request->getParam('oid');
    	}

    	if ($this->config->drag && $this->config->type == 'add' && $this->config->field && isset($this->config->field->{$this->config->field_orderid})) {
    		$nid = $this->config->model->fetchOne('MAX(`'.$this->config->field_orderid.'`)');
    		$this->config->post_field_extend->set(array(
    			$this->config->field_orderid => $nid + 1
    		));
    	}
		if ($this->config->tree && $this->config->type == 'add' && $this->config->field && isset($this->config->field->{$this->config->tree_field})) $this->config->post_field_extend->set(array(
    		$this->config->tree_field => (int)$request->getParam('id')
    	));
    	if ($request->getParam('cid') && isset($this->config->field->{$this->config->field_link})) {
    		$this->config->post_field_extend->set(array(
    			$this->config->field_link => $request->getParam('cid')
    		));
    	}
    	return $this;
	}

    function viewControl() {
    	$jquery = Zend_Controller_Action_HelperBroker::getStaticHelper('jquery');
    	$js = Zend_Controller_Action_HelperBroker::getStaticHelper('js');
    	Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
    	Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);

    	if (!$this->config->stop_frame) {
    		$request = $this->getRequest();
	    	$s = new Zend_Session_Namespace();
	    	$s->control['last'] = array(
				'controller' => $request->getControllerName(),
				'action' => $request->getActionName(),
				'param' => $request->getParams()
			);
    	}

		$view = $this->getActionController()->view;

		switch ($this->config->response_type) {
			case 'jqphp':
				if (count($this->config->info)) $jquery->direct()->addMessage(($this->config->info_type == 'i' ? '' : 'e:').implode('<br />', $this->config->info->toArray()));
				if (!$this->config->stop_frame) {
					$navpane = array();
					if (count($this->config->navpane->start)) $navpane = array_merge($navpane, $this->config->navpane->start->toArray());
					if ($this->config->navpane->middle === true) {
						$middle = $this->buildNavpane();
						if ($middle) $navpane = array_merge($navpane, $middle);
					}
					else if ($this->config->navpane->middle !== false) $navpane = array_merge($navpane, $this->config->navpane->middle->toArray());
					if (count($this->config->navpane->finish)) $navpane = array_merge($navpane, $this->config->navpane->finish->toArray());

					$navpane[] = array('t' => $this->config->place);
					$js->addEval('c.build_navpane('.Zend_Json::encode($navpane).');');
					$jquery->direct('#c_content')->html($view->placeholder('content')->getValue());
				}
	    		$jquery->direct()->evalScript($js->renderEval());

	    		$this->getResponse()
					->setHeader('Content-type', 'application/json')
					->sendHeaders();

			    $jquery->direct()->getResponse();
				break;
			case 'json':
				$this->getResponse()
					->setHeader('Content-type', 'application/json')
					->setBody($view->placeholder('content')->getValue());
				break;
		}
    }

    function buildNavpane($id = null) {
    	$ret = array();
    	$model = new Default_Model_Cmenu();
    	if ($id === null) {
    		$id = $model->fetchOne('id', array('`controller` = ?' => $this->getRequest()->getControllerName()));
    	}
    	$item = $model->fetchRow(array('`id` = ?' => $id));
    	if ($item) {
    		array_unshift($ret, array(
    			't' => $item->title,
    			'c' => $item->controller,
    			'a' => $item->action,
    			'p' => $item->param
    		));
    		$inner = $this->buildNavpane((int)$item->parentid);
    		if ($inner) $ret = array_merge($inner, $ret);
    	}
    	return $ret;
    }

	public function routeShow() {
    	$js = Zend_Controller_Action_HelperBroker::getStaticHelper('js');
    	$menu_model = new Default_Model_Cmenu();
		$menu = $menu_model->fetchRow(array('`controller` = ?' => $this->getRequest()->getControllerName()));
    	$request = $this->getRequest();
    	$view = $this->getActionController()->view;
		if ($request->getPost('nd')) {
			$rows = array();
			$where = $this->config->where ? $this->config->where->toArray() : array();
			if ($request->getParam('search') != 'false') {
				foreach ($this->config->field as $el) {
					if (isset($_POST[$el->name])) $where['`'.$el->name.'` LIKE ?'] = '%'.$_POST[$el['name']].'%';
				}
			}
			if ($this->config->tree) {
				$parentid = (int)$request->getPost('nodeid');
				if ($parentid) {
					$s = new Zend_Session_Namespace();
					$s->control['history'][$request->getControllerName()]['oid'] = $parentid;
				}
				$level = $request->getPost('n_level');
				$level = strlen($level) > 0 ? $level + 1 : 0;
				$where['`'.$this->config->tree_field.'` = ?'] = $parentid;
				$parentid = $parentid == 0 ? null : $parentid;
			}
			if ($request->getParam('cid') && isset($this->config->field->{$this->config->field_link})) $where['`'.$this->config->field_link.'` = ?'] = $request->getParam('cid');

			$rd = $this->config->model->fetchAll(
		    	$where,
		    	$this->config->orderby.' '.$this->config->orderdir,
		    	$this->config->pager_perpage
		    		? $this->config->pager_perpage
		    		: null,
		    	$this->config->pager_perpage
		    		? ($this->config->pager_page - 1) * $this->config->pager_perpage
		    		: null
		    );

		    $this->config->data_cnt = $this->config->model->fetchCount($where);
		   	$data = $rd->toArray();
		    if ($this->config->tree && $data && $this->config->field) {
		    	foreach ($data as &$el) {
		    		$el['_level'] = $level;
		    		$el['_count'] = (int)$this->config->model->fetchCount(array(
		    			'`'.$this->config->tree_field.'` = ?' => (int)$el['id']
		    		));
		    	}
		    }
		    $this->config->data = $data;
		}
		else {
			$menus = $menu_model->fetchAll(array('`parentid` = ?' => @(int)$menu->id, '`show_it` = 0'));
			if ($menus) {
				foreach ($menus as $el) {
					$cl_0 = stripos($el->param, 'cl=0');
					$this->config->button_top[] = array(
						'controller' => $el->controller,
						'action' => $el->action ? $el->action : 'ctlshow',
						'field' => 'cid',
						'title' => $el->title,
						'cl' => $cl_0 !== false ? 'f' : 't'
					);
				}
			}
			$menu = $menu_model->fetchRow(array('`id` = ?' => @(int)$menu->parentid));
			if ($menu) {
				if (strlen($request->getParam('cid')) && !$request->getParam('cid')) {
					$this->config->stop_frame = 1;
					$js->addEval('c.go("'.$menu->controller.'", "'.$menu->action.'", "");');
					$this->config->info[] = 'Элемент не выбран';
				}
				else {
					$s = new Zend_Session_Namespace();
					$s->control['history'][$menu->controller]['oid'] = $request->getParam('cid');
				}
			}

		}
		$this->config->pre_view;
		$view->render($this->config->view);
		return $this;
    }

    public function buildForm()
    {
    	$id = (int)$this->getRequest()->getParam('id');
    	$form = new Zkernel_Form(array(
	    	'accept-charset' => 'utf-8',
    		'onsubmit' => 'return c.submit()',
    		'id' => 'c_form'
    	));
    	if ($this->config->field) foreach ($this->config->field as $el) {
		    if (!$el->active) continue;
			$p = new Zkernel_Config_Control($el->param, array(
				'label' => $el->title,
				'description' => $el->description,
	    		'required' => $el->required ? true : false,
				'validators' => $el->validators ? $el->validators : array()
			));
			if ($el->unique) {
				$select = $this->config->model->getAdapter()->select()->where('`id` != ?', $id);
				if (isset($el->unique->where)) $select->where($el->unique->where);
				$p->validators[] = array(
					'validator' => 'Db_NoRecordExists',
					'options' => array(
						$this->config->model->info('name'),
						$el->name,
						implode(' ', $select->getPart(Zend_Db_Select::WHERE))
					)
				);
			}
			if ($el->type == 'textarea') $p->rows = 10;
			if ($el->type == 'editarea') $p->rows = 15;
			if ($el->type == 'uploadify') {
				if (!isset($p->destination)) $p->destination = PUBLIC_PATH.'/upload/'.$this->config->controller.'_'.$el->name;
				if (!isset($p->fn)) $p->fn = $this->config->model->fetchOne($el->name, array('`id` = ?' => $id));
			}
		   $form->addElement($el->type, $el->name, $p->toArray());
		}

		$form->addElement('submit', 'oac_ok', array(
		    'label' => 'ОК',
    		'class' => 'c_button'
		));
		$form->addElement('submit', 'oac_cancel', array(
		    'label' => 'Отмена',
			'onclick' => 'return c.go("'.$this->config->request_cancel->controller.'", "'.$this->config->request_cancel->action.'", "'.$this->config->request_cancel->param.'")',
    		'class' => 'c_button'
		));
		if ($this->config->oac_apply) $form->addElement('submit', 'oac_apply', array(
		    'label' => 'Применить',
    		'onclick' => 'return c.submit(1)',
    		'class' => 'c_button'
		));
    	$form->addDisplayGroup(array('oac_ok', 'oac_cancel', 'oac_apply'), 'oac');
    	return $form;
    }

	public function routeForm()
    {
    	$js = Zend_Controller_Action_HelperBroker::getStaticHelper('js');
    	$request = $this->getRequest();
    	$view = $this->getActionController()->view;
    	$this->config->id = $id_old = (int)$request->getParam('id');

    	if ($this->config->type == 'edit' && !$this->config->id) {
			$this->config->info[] = 'Элемент не выбран';
			$this->config->stop_frame = true;
		}
    	else {
		    $this->config->form = $this->buildForm();

			if (@(int)$_POST['cposted']) {
				if ($this->config->form->isValid($_POST)) {
					if ($this->config->type == 'add') $this->config->id = $this->config->model->fetchNextId();

					$this->config->data = $this->config->form->getValues();

					if (count($this->config->static_field) != @$this->config->data[$this->config->static_field->field_dst]) {
						$util = Zend_Controller_Action_HelperBroker::getStaticHelper('util');
						$stitle = $util->stitle($this->config->data[$this->config->static_field->field_src], $this->config->static_field->length);
    					$stitle = $stitle ? $stitle : '_';
    					$stitle_n = $stitle;
						if ($this->config->static_field->unique && $this->config->use_db) {
							$stitle_p = -1;
							do {
								$stitle_p++;
								$stitle_n = $stitle.($stitle_p == 0 ? '' : $stitle_p);
								$w = array('`'.$this->config->static_field->field_dst.'` = ?' => $stitle_n);
								if ($this->config->type == 'edit') $w['`id` != ?'] = $this->config->id;
								$stitle_c = (int)$this->config->model->fetchCount($w);
							}
							while ($stitle_c > 0);
						}
						$this->config->data[$this->config->static_field->field_dst] = $stitle_n;
					}

					if (count($this->config->post_field_unset)) {
						foreach ($this->config->post_field_unset as $k) unset($this->config->data[$k]);
					}

					if (count($this->config->post_field_extend)) $this->config->data->set($this->config->post_field_extend);
					$m2m_changed = false;

					foreach ($this->config->data as $k => $v) {
						if (@$this->config->field->$k->m2m) {
							$m2m_new = isset($this->config->data->$k) ? $this->config->data->$k->toArray() : array();
							$m2m_model = $this->config->field->$k->m2m->model;
							$m2m_model = new $m2m_model();
							$m2m_self = $this->config->field->$k->m2m->self;
							$m2m_foreign = $this->config->field->$k->m2m->foreign;

							$m2m_old = $m2m_model->fetchAll(array(
								'`'.$m2m_self.'` = ?' => $this->config->id
							));
							if ($m2m_old) {
								$m2m_ids = array();
								// Удаляем несуществующие связи
								foreach ($m2m_old as $m2m_el) {
									if (!$m2m_new || !in_array($m2m_el->$m2m_foreign, $m2m_new)) {
										$m2m_changed = true;
										$m2m_model->delete(array(
											'`'.$m2m_foreign.'` = ?' => $m2m_el->$m2m_foreign
										));
									}
									else $m2m_ids[] = $m2m_el->$m2m_foreign;
								}
								// Добавляем
								if ($m2m_new) foreach ($m2m_new as $m2m_el) {
									if (!in_array($m2m_el, $m2m_ids)) {
										$m2m_changed = true;
										$yyy = $m2m_model->insert(array(
											$m2m_self => $this->config->id,
											$m2m_foreign => $m2m_el
										));
									}
								}
							}
							unset($this->config->data->$k);
						}
					}

					$ok = false;
					$this->config->func_override;
					if ($this->config->use_db && count($this->config->info) == 0) {
						$data_db = $this->config->data->toArray();
						foreach ($data_db as $k => $v) {
							if (!array_key_exists($k, $this->config->model->info('metadata'))) unset($data_db[$k]);
						}
						if ($this->config->type == 'edit') $ok = $this->config->model->update($data_db, array('`id` = ?' => $this->config->id));
						else $ok = $this->config->data->id =  $this->config->model->insert($data_db);
					}
					if ($ok || $m2m_changed) {
						$this->config->info[] = 'Данные сохранены';
						$this->config->func_success;
					}
					else {
						//$this->config->info[] = 'Изменений данных не было';
					}
					if (!@$_POST['is_apply']) $js->addEval('c.go("'.$this->config->request_ok->controller.'", "'.$this->config->request_ok->action.'", "'.$this->config->request_ok->param.'");');
				}
				else {
					$this->config->info_type = 'e';
					foreach ($this->config->form->getErrors() as $k => $el) {
						if ($el) $this->config->info[] = $this->config->form->getElement($k)->getLabel().': '.implode(', ', $el);
					}
				}
				if ($this->config->scroll_top) $js->addEval('window.scroll(0, 0);');
				$this->config->stop_frame = true;
			}
			else {
				if ($this->config->type == 'edit') {
					$data = $this->config->model->fetchRow(array('`id` = ?' => $this->config->id));
					$this->config->data->set($data->toArray());

					if ($this->config->field && isset($this->config->field->{$this->config->field_title})) {
						$this->config->navpane->finish[] = array(
		    				't' => $this->config->data->{$this->config->field_title},
							'c' => $this->getRequest()->getControllerName(),
							'a' => $this->getRequest()->getActionName(),
							'p' => ''
		    			);
		    		}
					//$data = $data ? $data->toArray() : array();
					$els = $this->config->form->getElements();
					if ($els) {
						foreach ($els as $el) {
							$k = $el->getName();
							if ($this->config->field->$k->m2m) {
								$m2m_model = $this->config->field->$k->m2m->model;
								$m2m_self = $this->config->field->$k->m2m->self;
								$m2m_foreign = $this->config->field->$k->m2m->foreign;
								$this->config->data->$k = $m2m_model->fetchCol($m2m_foreign, array(
									'`'.$m2m_self.'` = ?' => $this->config->id
								));
							}
						}
					}
					//$this->config->data->set($this->config->data->toArray());
					$this->config->func_preset;
					$this->config->form->setDefaults($this->config->data->toArray());
				}
			}
    		if ($this->config->tree && $this->config->id) {
				$s = new Zend_Session_Namespace();
				$s->control['history'][$request->getControllerName()]['oid'] = $this->config->id;
			}
		}
		$view->render($this->config->view);
		return $this;
    }

    public function routeDrag()
    {
    	$view = $this->getActionController()->view;
    	$request = $this->getRequest();

    	$cur = $this->config->model->fetchRow(array('`id` = ?' => $request->getParam('id')));
    	$prev = $this->config->model->fetchRow(array('`id` = ?' => $request->getParam('prev')));
    	$ok = false;
    	if ($cur) {
	    	$cur->orderid = @(int)$prev->orderid + 1;
	    	$ok = $cur->save();
	    	if ($ok) {
	    		$w = array('`id` != ?' => $cur->id);
	    		if ($this->config->tree) $w['`'.$this->config->tree_field.'` = ?'] = $cur->{$this->config->tree_field};
		    	if ($prev) $w['`'.$this->config->orderby.'` > ?'] = $prev->{$this->config->orderby};
	    		$next = $this->config->model->fetchCol('id', $w);
	    		if ($next) $ok = $this->config->model->update(array($this->config->orderby => new Zend_Db_Expr('`'.$this->config->orderby.'` + 1')), '`id` IN ('.implode(',', $next).')');
	    	}
    	}

    	if ($ok) {
    		$this->config->info[] = 'Элемент перемещен';
    		$this->config->func_success;
    	}
    	else $this->config->info[] = 'Элемент не был перемещен';
    	$this->config->stop_frame = true;
    	$view->render($this->config->view);
		return $this;
    }

	public function routeDelete()
    {
    	$js = Zend_Controller_Action_HelperBroker::getStaticHelper('js');
    	$request = $this->getRequest();
    	$view = $this->getActionController()->view;

    	$ids = $request->getParam('ids');
    	$ids = $ids ? explode(',', $request->getParam('ids')) : array();

    	if (!$ids) {
    		$id = (int)$request->getParam('id');
    		$ids = $id ? array($id) : array();
    	}
    	if ($this->config->tree && @$ids[0]) {
    		$pid = (int)$this->config->model->fetchOne($this->config->tree_field, array('`id` = ?' => $ids[0]));
    		$s = new Zend_Session_Namespace();
			$s->control['history'][$request->getControllerName()]['oid'] = $pid;
		}
    	$cnt = 0;
    	if ($ids) {
	    	foreach ($ids as $el) {
	    		$item = $this->config->model->fetchRow(array('`id` = ?' => $el));
	    		$ok = $this->config->model->delete(array('`id` = ?' => $el));
	    		if ($ok) {
	    			$form = $this->buildForm();
	    			$els = $form->getElements();
	    			if ($els) {
	    				foreach ($els as $k => $v) {
							if ($v->getType() == 'Zkernel_Form_Element_Uploadify') {
								if (isset($v->destination) && isset($item->$k)) @unlink($v->destination.'/'.$item->$k);
							}
	    				}
	    			}
	    			$cnt++;
	    		}
	    	}
	    	$this->config->info[] = $cnt
				? 'Удалено элементов: '.$cnt
				: 'Ошибка удаления';
		}
		else $this->config->info[] = 'Элемент не выбран';
		if ($cnt) $js->addEval('c.go("'.$this->config->request_ok->controller.'", "'.$this->config->request_ok->action.'", "'.$this->config->request_ok->param.'");');
		else $this->config->stop_frame = true;
		$view->render($this->config->view);
		return $this;
    }

    function routeNone() {
    	$view = $this->getActionController()->view;
    	$view->render($this->config->view);
		return $this;
    }

    function routeDefault() {
    	$type = $this->config->type;
    	if (!$type) $type = substr($this->getRequest()->getActionName(), 3);
    	switch ($type) {
    		case 'list':
    		case 'text':
    			$func = 'show';
    			break;
    		case 'add':
    		case 'edit':
    			$func = 'form';
    			break;
    		default:
    			$func = $type;
    			break;
    	}

    	$type = $type == 'show' ? 'list' : $type;

    	$func = 'route'.ucfirst($func);

    	$this	->config->type = $type;

    	$this	->configFromType()
    			->configFromDb()
	    		->configFromRequest()
				->$func()
	    		->viewControl();
    }

	public function direct()
    {
        return $this;
    }
}