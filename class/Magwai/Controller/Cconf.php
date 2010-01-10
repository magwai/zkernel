<?php

class Magwai_Controller_Cconf extends Magwai_Controller_Action {
	function ctlinit() {
		$this->_helper->control()->config->set(array(
			'actions' => array('add', 'edit', 'delete', 'wizard'),
			'field' => array(
				'controller' => array(
					'title' => 'Контроллер',
					'sortable' => true
				),
				'action' => array(
					'title' => 'Действие',
					'sortable' => true
				),
				'key' => array(
					'title' => 'Ключ',
					'sortable' => true
				),
				'value' => array(
					'title' => 'Значение',
					'sortable' => true
				),
				'type' => array(
					'title' => 'Тип значения',
					'sortable' => true
				)
			)
		));
	}

	public function ctlwizardAction()
    {
    	$request = $this->getRequest();

    	$this->_helper->control()->config->set(array(
    		'type' => 'add',
    		'use_db' => false,
			'oac_apply' => false,
    		'field' => array(
				'controller' => array(
					'active' => false
				),
				'type' => array(
					'active' => false
				),
				'key' => array(
					'active' => false
				),
				'value' => array(
					'active' => false
				),
				'action' => array(
					'active' => false
				)
			)
    	));

    	switch ($request->getParam('step')) {
    		case 'select':
    			$cnr = $request->getParam('wcontroller');
    			if ($cnr) {
	    			$ft = array(
	    				'active' => true
	    			);
			    	$tt = array('' => '[ любое действие ]');
			    	$dir = $this->getFrontController()->getControllerDirectory();
			    	$dir = @$dir['default'];
			    	$c = ucfirst($request->getParam('wcontroller')).'Controller';
			    	@include $dir.'/'.$c.'.php';
			    	if (class_exists($c)) {
			    		$r = new Zend_Reflection_Class($c);
			    		$met = $r->getMethods();
			    	}
			    	if (@$met) {
			    		foreach ($met as $el) {
			    			if (substr($el->name, 0, 3) == 'ctl' && substr($el->name, strlen($el->name) - 6) == 'Action') {
			    				$k = substr($el->name, 0, strlen($el->name) - 6);
			    				if ($k) $tt[$k] = $k;
			    			}
			    		}
			    	}
					if (count($tt) > 1) $ft = array_merge($ft, array(
						'type' => 'select',
						'param' => array(
							'value' => $request->getParam('waction'),
							'multioptions' => $tt
						)
					));
					$fld = array(
						'kind' => array(
							'title' => 'Тип ключа',
							'type' => 'select',
							'param' => array(
								'value' => $request->getParam('kind'),
								'multioptions' => array(
									'action' => 'Значение действия',
									'field' => 'Значение поля'
								)
							)
						),
						'action' => $ft
					);
    			}
    			else {
    				$fld = array(
    					'kind' => array(
    						'title' => 'Тип ключа',
    						'type' => 'select',
    						'param' => array(
			   	 				'value' => 'global',
			   	 				'multioptions' => array(
									'global' => 'Глобальный параметр'
								)
			   	 			)
    					)
    				);
    			}

		    	$this->_helper->control()->config->set(array(
					'request_ok' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' => '&_waction='.$request->getPost('action').'&_step='.$request->getPost('kind')
					),
					'request_cancel' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' => '_step=default&_waction=0&_kind=0'
					),
		    		'field' => $fld
				));
    			break;
    		case 'action':
    			$tt = array();
		    	$t = $this->getActionKeys();
		    	if ($t) foreach ($t as $k => $el) $tt[$k] = $el['title'];
		    	$this->_helper->control()->config->set(array(
					'request_ok' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' =>	'_wkey='.$request->getPost('key').'&_step=type'
					),
					'request_cancel' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' => '_step=select&_kind=action'
					),
		    		'field' => array(
						'key' => array(
				    		'active' => true,
				    		'type' => 'select',
					    	'param' => array(
					    		'multioptions' => $tt
					    	)
				    	)
					)
				));
    			break;
    		case 'global':
    			$tt = array();
		    	$t = $this->getGlobalKeys();
		    	if ($t) foreach ($t as $k => $el) $tt[$k] = $el['title'];
		    	$this->_helper->control()->config->set(array(
					'request_ok' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' =>	'_wkey='.$request->getPost('key').'&_step=type'
					),
					'request_cancel' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' => '_step=select'
					),
		    		'field' => array(
						'key' => array(
				    		'active' => true,
				    		'type' => 'select',
					    	'param' => array(
					    		'multioptions' => $tt
					    	)
				    	)
					)
				));
    			break;
    		case 'field':
    			$tt = array();
		    	$t = $this->getFieldKeys();
		    	if ($t) foreach ($t as $k => $el) $tt[$k] = $el['title'];
		    	$ft = array(
		    		'active' => true,
		    		'type' => 'select',
			    	'param' => array(
			    		'multioptions' => $tt
			    	)
		    	);
				$ff = array(
					'active' => true,
					'title' => 'Название поля'
				);
		    	$c = $request->getParam('wcontroller');
		    	if ($c) {
			    	$hc = new Helper_Control();
			    	$hc->init('', $c);
			    	$hc->configFromDb();
			    	$tt = array();
			    	foreach ($hc->config->field as $k => $v) $tt[$k] = $k;
			    	$ff = array_merge($ff, array(
			    		'type' => 'select',
				    	'param' => array(
				    		'multioptions' => $tt
				    	)
			    	));
		    	}
		    	$this->_helper->control()->config->set(array(
					'request_ok' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' =>	'_wkey=field_'.$request->getPost('field').'_'.$request->getPost('key').'&_step=type'
					),
					'request_cancel' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' => '_step=select&_kind=field'
					),
		    		'field' => array(
						'key' => $ft,
						'field' => $ff
					)
				));
    			break;
    		case 'type':

		    	$ex = array();
		    	$type = 'add';
		    	$ft = array(
		   	 		'active' => true
		   	 	);
		    	$exist = $this->_helper->control()->config->model->fetchRow(array(
					'`controller` = ?' => $request->getParam('wcontroller'),
					'`action` = ?' => $request->getParam('waction'),
					'`key` = ?' => $request->getParam('wkey')
				));
				if ($exist) {
					$request->setParam('id', $exist->id);
					$type = 'edit';
				}
				else $ex = array(
					'controller' => $request->getParam('wcontroller'),
					'action' => $request->getParam('waction'),
					'key' => $request->getParam('wkey')
				);

				$id = (int)$request->getParam('id');

		   		$p = $this->getFeildParam($request->getParam('wkey'));
		   	 	$tt = array();
		   	 	if ($p['vtype']) {
		   	 		$ks = array_keys($p['vtype']);
		   	 		$t = $this->getTypes();
		   	 		foreach ($t as $k => $v) {
		   	 			if (array_key_exists($k, $p['vtype'])) $tt[$k] = $v;
		   	 		}
		   	 	}
		   	 	$ft = array_merge($ft, $tt
		   	 		?	array(
				   	 		'type' => 'select',
				   	 		'param' => array(
				   	 			'value' => @$ks[0],
				   	 			'multioptions' => $tt
				   	 		)
				   	 	)
				   	:	array(
			   	 			'param' => array(
			   	 				'value' => 'text',
			   	 				'disabled' => true
			   	 			)
		   	 			)
		   	 	);
		   	 	$kk = $request->getParam('wkey');
		   	 	$step = (substr($kk, 0, 6) == 'field_' ? 'field' : 'action');
		   	 	$ks = $this->getActionKeys();
		   	 	if (!array_key_exists($kk, $ks)) $step = 'global';

		    	$this->_helper->control()->config->set(array(
					'type' => $type,
		    		'post_field_extend' => $ex,
					'request_ok' => array(
						'controller' => 'cconf',
						'action' => 'ctledit',
						'param' =>	'_wcontroller=0&_waction=0&_wkey=0&_step=default&_kind=0&_id='.($id ? $id : $this->_helper->control()->config->model->fetchNextId())
					),
					'request_cancel' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' => '_wkey=0&_step='.$step
					),
					'use_db' => true,
		    		'field' => array(
						'type' => $ft
					)
				));
    			break;
    		default:
    			$ft = array(
    				'active' => true
    			);
		    	$tt = array('' => '[ любой контроллер ]');
		    	$dir = $this->getFrontController()->getControllerDirectory();
		    	$dir = @$dir['default'];
		    	$handle = @opendir($dir);@readdir($handle);@readdir($handle);
				while ($path = @readdir($handle)) {
					if (!is_dir($dir.$path)) {
						$n = strtolower(str_ireplace('Controller.php', '', $path));
						$tt[$n] = $n;
					}
				}
				@closedir($handle);
		    	if (count($tt) > 1) $ft = array_merge($ft, array(
		    		'type' => 'select',
			    	'param' => array(
		    			'value' => $request->getParam('wcontroller'),
			    		'multioptions' => $tt
			    	)
		    	));
		    	$this->_helper->control()->config->set(array(
					'request_ok' => array(
						'controller' => 'cconf',
						'action' => 'ctlwizard',
						'param' => '_wcontroller='.$request->getPost('controller').'&_step=select'
					),
					'request_cancel' => array(
						'controller' => 'cconf',
						'action' => 'ctlshow',
						'param' => '&_wcontroller=0'
					),
		    		'field' => array(
						'controller' => $ft
					)
				));
    			break;
    	}

    	$this->_helper->control()->routeDefault();
    }

	public function ctladdAction()
    {
    	$this->_helper->control()->config->set(array(
			'type' => 'add',
    		'field' => array(
				'value' => array(
					'active' => false
				),
				'type' => array(
		    		'type' => 'select',
			    	'param' => array(
			    		'multioptions' => $this->getTypes()
			    	)
		    	)
			)
		));

    	$this->_helper->control()->routeDefault();
    }

	public function ctleditAction()
    {
    	$request = $this->getRequest();

		$item = $this->model->fetchRow(array(
    		'`id` = ?' => $request->getParam('id')
    	));

    	$vl = $vl_sf = array();
    	if ($item) {
    		$p = $this->getFeildParam($item->key);
    		if ($p) $vl = $p;
    		$pp = @$p['vtype'][$item->type];
    		if ($pp) {
    			$vl = array_merge(
    				$vl,
    				$pp
    			);
    		}
    		if (@$p['func_success']) $vl_sf = $p['func_success'];
    	}

    	$this->_helper->control()->config->set(array(
			'type' => 'edit',
			'button_top' => array(array(
				'title' => 'Удалить',
				'controller' => 'cconf',
				'action' => 'ctldelete',
				'param' => '_id='.$request->getParam('id')
			)),
    		'field' => array(
				'action' => array(
					'active' => false
				),
				'type' => array(
					'active' => false
				),
				'controller' => array(
					'active' => false
				),
				'key' => array(
					'active' => false
				),
				'value' => $vl
			),
			'func_success' => $vl_sf
		));

		$this->_helper->control()->routeDefault();
    }

	public function ctlshowAction()
    {
    	$this->_helper->control()->config->set(array(
			'button_top' => array(
				'add',
				'edit',
				'delete',
				array(
					'title' => 'Мастер',
					'controller' => 'cconf',
					'action' => 'wizard',
					'param' => '_step=default'
				)
			),
    		'field' => array(
				'type' => array(
					'active' => false
				),
				'controller' => array(
					'formatter' => 'function',
					'formatoptions' => 'return value.length ? value : "Все";'
				),
				'action' => array(
					'formatter' => 'function',
					'formatoptions' => 'return value.length ? value : "Все";'
				)
			)
		));

    	$this->_helper->control()->routeDefault();
    }

    function getGlobalKeys($key = null) {
		$data = array(
			'theme' => array(
				'title' => 'Тема панели управления',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'redmond' => 'Redmond (по умолчанию)',
								'le-frog' => 'Le-frog',
								'blitzer' => 'Blitzer'
							)
						)
					)
				),
				'func_success' => 'php_function:Zend_Controller_Action_HelperBroker::getStaticHelper("js")->addEval("window.location = \'.\';");'
			)
    	);
		return $key === null ? $data : @$data[$key];
	}

	function getActionKeys($key = null) {
		$data = array(
			'orderby' => array(
				'title' => 'Поле сортировки',
				'description' => 'Хранимое значение: NAME поля. По-умолчанию: первое видимое поле',
				'vtype' => array(
					'text' => array(
						'type' => 'text',
						'param' => array(
							'validators' => array(
								array(
			    					'validator' => 'StringLength',
			    					'options' => array(1, 255)
								)
		    				)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'orderdir' => array(
				'title' => 'Направление сотрировки',
				'description' => 'Хранимое значение: asc или desc. По-умолчанию: asc',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'asc' => 'по возрастанию',
								'desc' => 'по убыванию'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)

			),
			'pager_perpage' => array(
				'title' => 'Количество элементов на странице',
				'description' => 'Хранимое значение: число. По-умолчанию: 0. Если задан ноль, то будут загружены все страницы',
				'vtype' => array(
					'text' => array(
						'type' => 'text',
						'param' => array(
							'validators' => array(
								array(
			    					'validator' => 'GreaterThan',
			    					'options' => array(-1)
								)
		    				)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'button_top' => array(
				'title' => 'Кнопки сверху',
				'description' => 'Хранимое значение: php массив кнопок. По-умолчанию: пусто. Это либо строки add, edit, delete, либо ассоциативный массив c ключами: title (заголовок), controller (контроллер, на который переходим), action (действияе, на которое переходим, param (доп. параметры в формате key=value&key1=value1), confirm (требуется ли подтверждение перехода: 1 или 0)',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'button_bottom' => array(
				'title' => 'Кнопки снизу',
				'description' => 'Хранимое значение: php массив кнопок. По-умолчанию: пусто. Это либо строки add, edit, delete, либо ассоциативный массив c ключами: title (заголовок), controller (контроллер, на который переходим), action (действияе, на которое переходим, param (доп. параметры в формате key=value&key1=value1), confirm (требуется ли подтверждение перехода: 1 или 0)',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'func_success' => array(
				'title' => 'Функция успешного действия',
				'description' => 'Хранимое значение: php код. По-умолчанию: пусто',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'post_field_extend' => array(
				'title' => 'Дополнительные поля результата формы',
				'description' => 'Хранимое значение: php код, возвращающий ассоциативный массив дополнительных данных. По-умолчанию: пусто',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'post_field_unset' => array(
				'title' => 'Удаляемые поля результата формы',
				'description' => 'Хранимое значение: php код, возвращающий ассоциативный массив удаляемых данных. По-умолчанию: пусто',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'request_ok' => array(
				'title' => 'Параметры перехода при удачном действии формы',
				'description' => 'Хранимое значение: php код, возвращающий ассоциативный массив с ключами: controller (контроллер, на который переходим), action (действияе, на которое переходим, param (доп. параметры в формате key=value&key1=value1). По-умолчанию: переход на текущий контроллер',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'request_cancel' => array(
				'title' => 'Параметры перехода при отмене действия формы',
				'description' => 'Хранимое значение: php код, возвращающий ассоциативный массив с ключами: controller (контроллер, на который переходим), action (действияе, на которое переходим, param (доп. параметры в формате key=value&key1=value1). По-умолчанию: переход на текущий контроллер',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'stop_frame' => array(
				'title' => 'Остановить переход на действие',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 0. Если переход останавливается, то после завершения действия контент и навигация не будут перезаписаны новыми данными. Действие просто выполнится и вернет ошибки. Это нужно в тех действиях, которые должны выполниться, но текущее содержимое админки не должно изменяться',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'info' => array(
				'title' => 'Дополнительный массив информацинных сообщений',
				'description' => 'Хранимое значение: php код, возвращающий массив сообщений. По-умолчанию: пусто. Этот массив будет добавлен к существующим сообщениям',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'text' => array(
				'title' => 'Текст перед содержимым действия',
				'description' => 'Хранимое значение: HTML текст. По-умолчанию: пусто',
				'vtype' => array(
					'text' => array(
						'type' => 'mce'
					)
				)
			),
			'oac_apply' => array(
				'title' => 'Показывать кнопку "Применить"',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 1',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'use_db' => array(
				'title' => 'Использовать БД',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 1. Если задан ноль, то действие не будет производить изменений в базе',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'model' => array(
				'title' => 'Модель',
				'description' => 'Хранимое значение: php код, возвращающий экземпляр модели',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'tree' => array(
				'title' => 'Древовидный список',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 0',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'tree_field' => array(
				'title' => 'Древовидный список: поле для связи',
				'description' => 'Хранимое значение: название поля для связи ID <= X',
				'vtype' => array(
					'text' => array(
						'type' => 'text',
						'param' => array(
							'validators' => array(
								array(
			    					'validator' => 'StringLength',
			    					'options' => array(1, 255)
								),
								'Alnum'
		    				)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'info_type' => array(
				'title' => 'Информационное сообщение: тип',
				'description' => 'Хранимое значение: пусто, "i", "e". Если пусто, то ошибка. Также ошибка имеет код "e"',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => 'Ошибка',
								'1' => 'Информация'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'scroll_top' => array(
				'title' => 'Прокручивать станицу наверх при окончнии действия',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 1',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			)
		);
		return $key === null ? $data : @$data[$key];
	}

	function getFieldKeys($key = null) {
		$data = array(
			'title' => array(
				'title' => 'Заголовок поля',
				'description' => 'Хранимое значение: текст. По-умолчанию: NAME поля',
				'vtype' => array(
					'text' => array(
						'type' => 'text',
						'validators' => array(
							array(
		    					'validator' => 'StringLength',
		    					'options' => array(1, 255)
							)
	    				)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'required' => array(
				'title' => 'Обязательное поле или нет',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 0',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'description' => array(
				'title' => 'Описание контрола в форме',


			),
			'active' => array(
				'title' => 'Отображается ли поле',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 1',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'sortable' => array(
				'title' => 'Участвует ли поле в сортировке',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 0',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'hidden' => array(
				'title' => 'Скрыто ли поле',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 0',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'type' => array(
				'title' => 'Тип поля',
				'description' => 'Хранимое значение: строка типа поля. По-умолчанию: text',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
		    					'text' => 'text',
		    					'textarea' => 'textarea',
		    					'select' => 'select',
		    					'password' => 'password',
		    					'editarea' => 'editarea',
		    					'mce' => 'mce',
		    					'multiCheckbox' => 'multicheckbox',
		    					'gmap' => 'gmap',
		    					'date' => 'date',
		    					'point' => 'point',
		    					'rubr' => 'rubr'
		    				)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'formatter' => array(
				'title' => 'Форматтер поля',
				'description' => 'Хранимое значение: строка название форматтера. По-умолчанию: пусто',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
		    					'integer' => 'integer',
		    					'number' => 'number',
		    					'currency' => 'currency',
		    					'date' => 'date',
		    					'email' => 'email',
		    					'link' => 'link',
		    					'showlink' => 'showlink',
		    					'checkbox' => 'checkbox',
		    					'select' => 'select',
		    					'function' => 'Собственная функция'
		    				)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'formatoptions' => array(
				'title' => 'Настройки форматтера',
				'description' => 'Хранимое значение: js код параметров форматтера. По-умолчанию: пусто. Для стандартных форматтеров хэлп: http://www.trirand.com/jqgridwiki/doku.php?id=wiki:predefined_formatter. Для своей функции нужно указать JavaScript код, завершающийся return форматированного содержимого. Доспупны переменные: value, options, row, action',
				'vtype' => array(
					'text' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'js'
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'unique' => array(
				'title' => 'Может ли значение совпадать с другими значениями этого поля',
				'description' => 'Хранимое значение: 1 или 0. По-умолчанию: 0',
				'vtype' => array(
					'text' => array(
						'type' => 'select',
						'param' => array(
							'multioptions' => array(
								'' => '[ не задано ]',
								'1' => 'Да',
								'0' => 'Нет'
							)
						)
					),
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'param_multioptions' => array(
				'title' => 'Параметры: multioptions',
				'description' => 'Хранимое значение: php ассоциативный массив ключ => значение. По-умолчанию: пусто',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'param_m2m' => array(
				'title' => 'Параметры: связь многие ко многим',
				'description' => 'Хранимое значение: php массив ассоциативных значений. По-умолчанию: пусто. Каждый ассоциативный массив должен состоять из ключей: model (модель таблицы со связями), self (поле, связанное с редактируемой моделью по ключу ID), foreign (поле, связанное со сторонней моделью по ключу ID)',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			),
			'param_rubr' => array(
				'title' => 'Параметры: контрол rurb',
				'description' => 'Хранимое значение: php массив ассоциативных значений. По-умолчанию: пусто. Каждый ассоциативный массив должен состоять из ключей: model (модель таблицы с деревом), parentid (поле, через которое связаны узлы в дереве, по-умолчанию parentid)',
				'vtype' => array(
					'php_function' => array(
						'type' => 'editarea',
						'param' => array(
							'syntax' => 'php'
						)
					)
				)
			)
		);
		return $key === null ? $data : @$data[$key];
	}

	function getFeildParam($key) {
		$k = explode('_', $key);
    	if (@$k[0] == 'field') {
    		array_shift($k);array_shift($k);
    		$kk = implode('_', $k);
    		$p = $this->getFieldKeys($kk);
    	}
    	else {
    		$p = $this->getActionKeys($key);
    		if (!$p) $p = $this->getGlobalKeys($key);
    	}
    	return $p;
	}

	function getTypes() {
		return array(
			'text' => 'Текст',
			'php_function' => 'PHP функция',
			'js_function' => 'JS функция'
		);
	}


}