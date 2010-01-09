<?php

class Helper_Control extends Zend_Controller_Action_Helper_Abstract  {
	public $config;

	function init($action = null, $controller = null) {
		$controller = $controller === null
			? $this->getRequest()->getControllerName()
			: $controller;
		$action = $action === null
			? $this->getRequest()->getActionName()
			: $action;

		$m = 'Site_Model_'.ucfirst($controller);

		if (@class_exists($m)) {
			$model = new $m();
			if ($this->getActionController() && $model->info('name') == $this->getActionController()->model->info('name')) $model = $this->getActionController()->model;
		}

		$this->config = new Magwai_Config_Control(array(
			'theme'					=> 'redmond',
			'model' 				=> $model,
			'tree'					=> false,
			'tree_field'			=> 'parentid',
			'cotroller' 			=> $controller,
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
	    	'button_top' 			=> array(),
	    	'orderby' 				=> '',
	    	'orderdir' 				=> 'asc',
	    	'field' 				=> array(),
			'formatter_function'	=> array(),
	    	'pager_perpage' 		=> 0,
		   	'pager_page' 			=> 1,
	    	'pre_view'				=> null,
	    	'func_success'			=> null
		));
		return $this;
	}

	function configFromType() {
		if (!$this->config->view) {
			switch ($this->config->type) {
				case 'add':
				case 'edit':
					$view = 'form';
					break;
				case 'list':
					$view = 'jqgrid';
					$this->config->button_top = array('add', 'edit', 'delete');
					break;
				case 'text':
					$view = 'text';
					break;
				case 'delete':
					$view = 'delete';
					break;
				default:
					$view = 'error';
					break;
			}

			$this->config->view = 'control/'.$view.'.phtml';
		}

		return $this;
	}

	function configFromDb() {
		// Получаем настройки из БД
    	$mc = new Site_Model_Cconf();

    	$conf = $mc->fetchPairs(null, '', '');
    	if (!$conf) $conf = array();

    	$conf_c = $mc->fetchPairs(null, '', $this->config->controller);
    	if ($conf_c) $conf = array_merge($conf, $conf_c);

    	$conf_s = $mc->fetchPairs(null, $this->config->action, $this->config->controller);
    	if ($conf_s) $conf = array_merge($conf, $conf_s);

    	//print_r($conf);
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

		if ($this->config->field && !$this->config->orderby) foreach ($this->config->field as $k => $el) {
			if ($el->active && !$el->hidden) {
				$this->config->orderby = $k;
				break;
			}
		}

		//print_r($this->config);
		//echo 'ggg';
    	//exit();
		//$c = new Magwai_Config_Control($conf);

		$this->config->set($conf);

		//print_r($this->config);
    	//exit();

    	return $this;
	}

	function configFromRequest() {
		$request = $this->getRequest();
    	if ($request->getParam('orderby')) $this->config->orderby = $request->getParam('orderby');
    	if ($request->getParam('orderdir')) $this->config->orderdir = $request->getParam('orderdir');
    	if ($request->getParam('page')) $this->config->pager_page = $request->getParam('page');
    	return $this;
	}

    function viewControl() {
    	$jquery = Zend_Controller_Action_HelperBroker::getStaticHelper('jquery');
    	$js = Zend_Controller_Action_HelperBroker::getStaticHelper('js');
    	Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->disableLayout();
    	Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNoRender(true);

    	$request = $this->getRequest();
    	$s = new Zend_Session_Namespace();
    	$s->control['last'] = array(
			'controller' => $request->getControllerName(),
			'action' => $request->getActionName(),
			'param' => $request->getParams()
		);

		$view = $this->getActionController()->view;

		switch ($this->config->response_type) {
			case 'jqphp':
				if (count($this->config->info)) $jquery->direct()->addMessage(($this->config->info_type == 'i' ? '' : 'e:').implode('<br />', $this->config->info->toArray()));
				if (!$this->config->stop_frame) {
					$jquery->direct('#c_navpane')->html($view->placeholder('navpane')->getValue());
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

	public function routeShow()
    {
    	$request = $this->getRequest();
    	$view = $this->getActionController()->view;

		if ($request->getPost('nd')) {

			$rows = array();
			$where = array();
			if ($request->getParam('search')) {
				foreach ($this->config->field as $el) {
					if (isset($_POST[$el->name])) $where['`'.$el->name.'` = ?'] = $_POST[$el['name']];
				}
			}
			if ($this->config->tree) {
				$parentid = (int)$request->getPost('nodeid');
				$level = $request->getPost('n_level');
				$level = strlen($level) > 0 ? $level + 1 : 0;
				$where['`'.$this->config->tree_field.'` = ?'] = $parentid;
				$parentid = $parentid == 0 ? null : $parentid;
			}
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
		    $this->config->data_cnt = $this->config->model->fetchCount();
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
		$this->config->pre_view;
		$view->render($this->config->view);
		return $this;
    }

	public function routeForm()
    {
    	$js = Zend_Controller_Action_HelperBroker::getStaticHelper('js');
    	$request = $this->getRequest();
    	$view = $this->getActionController()->view;
    	$id = $id_old = (int)$request->getParam('id');
		if ($this->config->type == 'edit' && !$id) {
			$this->config->info[] = 'Элемент не выбран';
			$this->config->stop_frame = true;
		}
    	else {
		    $this->config->form = new Magwai_Form(array(
		    	'accept-charset' => 'utf-8',
	    		'onsubmit' => 'return c.submit()',
	    		'id' => 'c_form'
	    	));
	    	if ($this->config->field) foreach ($this->config->field as $el) {
			    if (!$el->active) continue;
				$p = new Magwai_Config_Control($el->param, array(
					'label' => $el->title,
					'description' => $el->description,
		    		'required' => $el->required ? true : false,
					'validators' => array()
				));
				if ($el->unique) {
					$p->validators[] = array(
						'validator' => 'Db_NoRecordExists',
						'options' => array(
							$this->config->model->info('name'),
							$el->name,
							array('field' => 'id', 'value' => $id)
						)
					);
				}
				if ($el->type == 'textarea') $p->rows = 10;
				if ($el->type == 'editarea') $p->rows = 15;

			    $this->config->form->addElement($el->type, $el->name, $p->toArray());
			}

			$this->config->form->addElement('submit', 'oac_ok', array(
			    'label' => 'ОК',
	    		'class' => 'c_button'
			));
			$this->config->form->addElement('submit', 'oac_cancel', array(
			    'label' => 'Отмена',
				'onclick' => 'return c.go("'.$this->config->request_cancel->controller.'", "'.$this->config->request_cancel->action.'", "'.$this->config->request_cancel->param.'")',
	    		'class' => 'c_button'
			));
			if ($this->config->oac_apply) $this->config->form->addElement('submit', 'oac_apply', array(
			    'label' => 'Применить',
	    		'onclick' => 'return c.submit(1)',
	    		'class' => 'c_button'
			));
	    	$this->config->form->addDisplayGroup(array('oac_ok', 'oac_cancel', 'oac_apply'), 'oac');

			if (@(int)$_POST['cposted']) {
				if ($this->config->form->isValid($_POST)) {
					if ($this->config->type == 'add') $id = $this->config->model->fetchNextId();

					$data = $this->config->form->getValues();
					if (count($this->config->post_field_unset)) {
						foreach ($this->config->post_field_unset as $k) unset($data[$k]);
					}
					if (count($this->config->post_field_extend)) $data = array_merge($data, $this->config->post_field_extend->toArray());
					$m2m_changed = false;
					foreach ($data as $k => $v) {
						if (@$this->config->field->$k->m2m) {
							$m2m_new = @$data[$k];
							$m2m_model = $this->config->field->$k->m2m->model;
							$m2m_model = new $m2m_model();
							$m2m_self = $this->config->field->$k->m2m->self;
							$m2m_foreign = $this->config->field->$k->m2m->foreign;

							$m2m_old = $m2m_model->fetchAll(array(
								'`'.$m2m_self.'` = ?' => $id
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
										$m2m_model->insert(array(
											$m2m_self => $id,
											$m2m_foreign => $m2m_el
										));
									}
								}
							}
							unset($data[$k]);
						}
					}
					$ok = false;
					if ($this->config->use_db) {
						foreach ($data as $k => $v) {
							if (!array_key_exists($k, $this->config->model->info('metadata'))) unset($data[$k]);
						}
						if ($this->config->type == 'add') {
							if ($this->config->tree) $data[$this->tree_field] = $id_old;
							$ok = $this->config->model->insert($data);
						}
						if ($this->config->type == 'edit') $ok = $this->config->model->update($data, array('`id` = ?' => $id));
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
					$data = $this->config->model->fetchRow(array('`id` = ?' => $id));
					$data = $data ? $data->toArray() : array();
					$els = $this->config->form->getElements();
					if ($els) {
						foreach ($els as $el) {
							$k = $el->getName();
							if ($this->config->field->$k->m2m) {
								$m2m_model = $this->config->field->$k->m2m->model;
								$m2m_self = $this->config->field->$k->m2m->self;
								$m2m_foreign = $this->config->field->$k->m2m->foreign;
								$data[$k] = $m2m_model->fetchCol($m2m_foreign, array(
									'`'.$m2m_self.'` = ?' => $id
								));
							}
						}
					}
					$this->config->form->setDefaults($data);
				}
			}
		}
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
    	$cnt = 0;
    	if ($ids) {
	    	foreach ($ids as $el) {
	    		$ok = $this->config->model->delete(array('`id` = ?' => $el));
	    		if ($ok) $cnt++;
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