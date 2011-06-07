<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Control extends Zend_View_Helper_Abstract  {
	private $_user_inited = false;
	private $_config_inited = false;
	public $config;

	private function configInit($data = null) {
		if ($this->_config_inited) return;
		$d = array(
			'control_lang'			=> 'ru',
			'control_lang_current'	=> array(),
			'control_lang_data'		=> array(
				'en' => array(
					'login_exit' 		=> 'Logout',
					'login_enter'		=> 'Login',
					'login_password' 	=> 'password',
					'login_username' 	=> 'login',
					'login_login' 		=> 'Login',
					'login_exit' 		=> 'Logout',
					'reload' 			=> 'Reload',
					'admin_panel' 		=> 'Control panel',
					'visit' 			=> 'Visit homepage',
					'no_connect' 		=> 'No connect to server',
					'no_template' 		=> 'Template loading error',
					'no_config' 		=> 'Configuration loading error',
					'no_lang' 			=> 'Language loading error',
					'try_again' 		=> 'Try again?',
					'no_menu' 			=> 'Menu loading error',
					'txt_alert_title' 	=> 'Info',
					'no_login' 			=> 'Authorization data error',
					'no_logout' 		=> 'Logout error',
					'no_loggin' 		=> 'Login error',
					'no_login_err' 		=> 'Login / password are uncorrect',
					'no_sel' 			=> 'You should select an element',
					'no_response' 		=> 'Module loading error',
					'no_filter' 		=> 'Enter a request',
					'file_error' 		=> 'File uploading error',
					'no_multi' 			=> 'Language loading error',
					'no_multi_change' 	=> 'Language change error',
					'menu' 				=> 'Menu',
					'panel_loading' 	=> 'Control panel loading',
					'old_browser' 		=> 'Your browser is too old. The control panel works uncorrectly',
					'load_stop'			=> 'Abort loading',
					'error_javascript' 	=> 'Your browser doesn`t support JavaScript or its usage is restricted by security policy. You need to enable JavaScript to operate the control panel.',
					'generator' 		=> 'Generator',
					'action' 			=> 'Action',
					'controller' 		=> 'Controller',
					'key' 				=> 'Key',
					'value' 			=> 'Value',
					'value_type' 		=> 'Value type',
					'master' 			=> 'Master',
					'all' 				=> 'All',
					'delete' 			=> 'Delete',
					'add' 				=> 'Add',
					'edit' 				=> 'Edit',
					'add_place' 		=> 'Add',
					'edit_place' 		=> 'Edit',
					'no_controller' 	=> 'Controller not found',
					'access_error' 		=> 'You have no access this partition',
					'meta_title' 		=> 'Is shown in browser title',
					'meta_keywords' 	=> 'Separate keywords with comma',
					'meta_description' 	=> 'Description has to describe page content',
					'cancel' 			=> 'Cancel',
					'submit' 			=> 'Apply',
					'data_saved'		=> 'Data saved',
					'element_moved' 	=> 'Element moved',
					'element_not_moved' => 'Element didn\'t move',
					'element_deleted' 	=> 'Elements deleted: ',
					'home' 				=> 'Homepage',
					'home_page' 		=> '<p><strong>Control panel</strong> is specified for editing your site.</p><p>&nbsp;</p><p>You can change static and dinamic pages content here and upload files to server. All changes are applied in on-line mode.</p>',
					'loading' 			=> 'Loading',
					'not_specified'		=> 'Not specified',
					'title'				=> 'Title',
					'description'		=> 'Description',
					'message'			=> 'Message',
					'partition'			=> 'Partition',
					'partition_desc'	=> 'Choose a partition or input URL into the next field. If you fill both fields site uses URL link',
					'back' 				=> 'Back',
					'view_place' 		=> 'View',
                    'button'            => 'Browse...',
					'meta_show_title_title' => 'Show website title',
					'meta_show_title' => 'Switch on/off website title before TITLE',
					'more' => 'More'
				),
				'ru' => array(
					'login_enter' 		=> 'Войти',
					'login_password'	=> 'Пароль',
					'login_username' 	=> 'Имя пользователя',
					'login_login'		=> 'Войти',
					'login_exit' 		=> 'Выйти',
					'reload' 			=> 'Перезагрузить',
					'admin_panel' 		=> 'Панель управления',
					'visit' 			=> 'Перейти на сайт',
					'menu' 				=> 'Меню',
					'no_connect' 		=> 'Сервер не отвечает',
					'no_template' 		=> 'Ошибка загрузки шаблона',
					'no_config' 		=> 'Ошибка загрузки конфигурации',
					'no_lang' 			=> 'Ошибка загрузки языка',
					'try_again' 		=> 'Повторить попытку?',
					'no_menu' 			=> 'Ошибка загрузки меню',
					'txt_alert_title' 	=> 'Информация',
					'no_login' 			=> 'Ошибка получения сведений об авторизации',
					'no_logout' 		=> 'Ошибка выхода',
					'no_loggin' 		=> 'Ошибка входа',
					'no_login_err' 		=> 'Логин / пароль неверны',
					'no_sel' 			=> 'Элемент не выбран',
					'no_response' 		=> 'Ошибка загрузки модуля',
					'no_filter' 		=> 'Укажите запрос',
					'file_error' 		=> 'Ошибка загрузки файла',
					'no_multi' 			=> 'Ошибка загрузки языка',
					'no_multi_change' 	=> 'Ошибка смены языка',
					'panel_loading' 	=> 'загрузка панели управления',
					'old_browser' 		=> 'Вы используете устаревший браузер. Панель управления может работать некорректно',
					'load_stop' 		=> 'Отменить загрузку',
					'error_javascript' 	=> 'Ваш браузер не поддерживает JavaScript, либо их использование ограничено политикой безопасности. Для работы панели управления необходимо разрешить использование JavaScript.',
					'generator' 		=> 'Генератор',
					'action' 			=> 'Действие',
					'controller' 		=> 'Контроллер',
					'key' 				=> 'Ключ',
					'value' 			=> 'Значение',
					'value_type' 		=> 'Тип значения',
					'master' 			=> 'Мастер',
					'all' 				=> 'Все',
					'delete' 			=> 'Удалить',
					'add'	 			=> 'Добавить',
					'edit' 				=> 'Изменить',
					'add_place' 		=> 'Добавление',
					'edit_place' 		=> 'Изменение',
					'no_controller' 	=> 'Контроллер не найден',
					'access_error' 		=> 'У вас нет доступа в этот раздел',
					'meta_title' 		=> 'Отображается в заголовке окна браузера',
					'meta_keywords' 	=> 'Ключевые слова перечисляются через запятую',
					'meta_description' 	=> 'Описание должно характеризовать содержимое страницы',
					'cancel' 			=> 'Отменить',
					'submit' 			=> 'Применить',
					'data_saved' 		=> 'Данные сохранены',
					'element_moved' 	=> 'Элемент перемещен',
					'element_not_moved' => 'Элемент не был перемещен',
					'element_deleted' 	=> 'Элементов удалено: ',
					'home' 				=> 'Главная',
					'home_page' 		=> '<p><strong>Панель управления</strong> предназначена для редактирования содержимого вашего сайта.</p><p>&nbsp;</p><p>С ее помощью Вы можете вносить изменения в содержимое статических и динамических страниц, загружать файлы на сервер. Все изменения производятся в режиме on-line и вступают в силу как только запрос на изменение был обработан на сервере.</p>',
					'loading' 			=> 'Загрузка',
					'not_specified'		=> 'Не указан',
					'title'				=> 'Название',
					'description'		=> 'Описание',
					'message'			=> 'Сообщение',
					'partition'			=> 'Раздел',
					'partition_desc'	=> 'Выберите либо раздел, либо введите URL в следующем поле. Если вы выберите раздел и введете URL одновременно, то будет использован URL',
					'back' => 'Назад',
					'view_place' => 'Просмотр',
                    'button'    => 'Обзор...',
					'meta_show_title_title' => 'Показывать название сайта',
					'meta_show_title' => 'Включить/выключить отображения названия сайта в самом начале TITLE страницы',
					'more' => 'Дополнительно'
				)
			),
			'wysiwyg'				=> 'mce',
			'post'					=> array(),
			'param'					=> array(),
			'theme'					=> 'redmond',
			'model' 				=> null,
			'where'					=> null,
			'tree'					=> false,
			'tree_field'			=> 'parentid',
			'tree_opened'			=> array(),
			'field_orderid'			=> 'orderid',
			'field_title'			=> 'title',
			'field_link'			=> 'parentid',
			'param_link'			=> 'cid',
			'controller' 			=> null,
			'action' 				=> null,
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
				'controller' => '',
				'action' => '',
				'param' => ''
			),
	    	'request_cancel' 		=> array(
				'controller' => '',
				'action' => '',
				'param' => ''
			),
	    	'oac_apply' 			=> true,
			'oac_cancel'			=> true,
			'oac_ok_title'			=> 'OK',
	    	'post_field_extend'		=> array(),
	    	'post_field_unset'		=> array(),
	    	'orderby' 				=> '',
	    	'orderdir' 				=> 'asc',
	    	'field' 				=> array(),
			'formatter_function'	=> array(),
			'pager_list'			=> array(10, 20, 30, 50, 100, 200, 500, 1000),
			'pager_scroll'			=> true,
	    	'pager_perpage' 		=> 0,
		   	'pager_page' 			=> 1,
	    	'pre_view'				=> null,
	    	'func_override'			=> null,
	    	'func_success'			=> null,
	    	'func_preset'			=> null,
			'func_check'			=> null,
	    	'navpane'				=> array(
	    		'start' => array(),
	    		'middle' => true,
	    		'finish' => array()
	    	),
	    	'data' => array(),
	    	'static_field' => false,
	    	'zk_meta' => 0
		);
		if ($data !== null) $d = array_merge($d, $data);
		$this->config = new Zkernel_Config_Control($d);
		$this->config->request_cancel->controller = $this->config->controller;
		$this->config->request_ok->controller = $this->config->controller;

		if ($this->config->controller) {
			$db = Zkernel_Common::getDocblock(ucfirst($this->config->controller).'Controller');
			$this->config->zk_meta = isset($db['zk_meta']) && $db['zk_meta'] ? 1 : 0;
		}

		$this->config->control_lang_current = $this->config->control_lang_data[$this->config->control_lang];

		$this->_config_inited = true;

		ini_set('session.cookie_lifetime', 86400 * 30);
		ini_set('session.gc_maxlifetime', 86400 * 30);
	}

	public function buldMenu($parentid = 0) {
		$menu = array();
		$res = new Default_Model_Cresource();
		$db = new Default_Model_Cmenu();
		$row = $db->fetchRow(array('id = ?' => $parentid));

		$result = $db->fetchAll(array('parentid = ?' => $parentid, '`show_it` = 1'), 'orderid');

		if ($result) {
			foreach ($result as $num => $el) {
				if (!@$el['title']) continue;
				if (!$el->resource || $this->view->user()->isAllowed($this->view->user('role'), $el->resource)) {
					$item = array(
						't' => $el['title']
					);
					if (@$el['controller']) $item['c'] = $el['controller'];
					if (@$el['action']) $item['a'] = $el['action'];
					if (@$el['param']) $item['p'] = Zkernel_Common::url2array($el['param']);
					$e = $this->buldMenu($el['id']);
					if ($e) $item['e'] = $e;
					$menu[] = $item;
				}
			}
		}
		return $menu;
	}

	private function userInit() {
		if ($this->_user_inited) return;
		$user = new Zkernel_User(array(
			'role' => new Default_Model_Crole(),
			'role_refer' => new Default_Model_Crolerefer(),
			'resource' => new Default_Model_Cresource(),
			'rule' => new Default_Model_Crule(),
			'rule_role' => new Default_Model_Crulerole(),
			'rule_resource' => new Default_Model_Cruleresource(),
			'user' => new Default_Model_Cuser()
		));
		$user->initAcl();
		$user->loginAuto();
		Zend_Registry::set('Zkernel_User', $user);
		$this->_user_inited = true;
	}

	public function route() {
		$mm = new Default_Model_Cmenu();
		$menu = $mm->fetchRow(array('`controller` = ?' => $this->config->controller));
		$error = '';
		if ($this->config->controller == 'cindex' || $menu) {
			if ($this->config->controller == 'cindex' || $this->view->user()->isAllowed($this->view->user('role'), $menu->resource)) {

				$this->config->control_lang_current = $this->config->control_lang_data[$this->config->control_lang];

				if ($this->config->post) {
					foreach ($this->config->post as $k => $v) if (substr($k, 0, 1) == '_') $this->config->param[substr($k, 1)] = $v;
				}
				$s = new Zend_Session_Namespace();

				if (!is_array(@$s->control['history'][$this->config->controller])) $s->control['history'][$this->config->controller] = array();
				$this->config->param = $s->control['history'][$this->config->controller] = array_merge($s->control['history'][$this->config->controller], $this->config->param->toArray());

				$this->view->inlineScript('script', 'c.cfg.controller = "'.$this->config->controller.'";c.cfg.action = "'.$this->config->action.'";');
				$_SESSION['isLoggedIn'] = $this->view->user()->isAllowed(
					$this->view->user('role'),
					Zkernel_Common::getById(array(
						'model' => new Default_Model_Cresource(),
						'field' => 'id',
						'key' => 'key',
						'id' => 'file'
					))
				);

				try {
					$this->view->render($this->config->controller.'/'.$this->config->action.'.phtml');
				}
				catch (Zend_View_Exception $e) {
					try {
						$this->view->render($this->config->controller.'/ctl.phtml');
					}
					catch (Zend_View_Exception $e) {

					}
				}

				$type = $this->config->type;

		    	if (!$type) $type = substr($this->config->action, 3);

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


				$this->config->type = $type;
				$this->configFromType();
      				$this->configFromDb();
            			$this->configFromRequest();
				$this->$func();


			}
			else $error = $this->config->control_lang_current['access_error'];
		}
		else $error = $this->config->control_lang_current['no_controller'];
		if ($error) {
			$this->view->inlineScript('script', 'c.load_menu();c.load_auth();c.go(c.cfg.def_controller);');
			$this->config->stop_frame = true;
			$this->config->info = array($error);
		}
		return $this;
	}

	public function render() {
		if (count($this->config->info)) {
			$this->view->layout()->info = $this->config->info->toArray();
			$this->view->layout()->info_type = $this->config->info_type;
		}
		if (!$this->config->stop_frame) {
			$s = new Zend_Session_Namespace();
	    	$s->control['last'] = array(
	    		'controller' => $this->config->controller,
	    		'action' => $this->config->action
	    	);
			$navpane = array();
			if (count($this->config->navpane->start)) $navpane = array_merge($navpane, $this->config->navpane->start->toArray());
			if ($this->config->navpane->middle === true) {
				$middle = $this->buildNavpane();
				if ($middle) $navpane = array_merge($navpane, $middle);
			}
			else if ($this->config->navpane->middle !== false) $navpane = array_merge($navpane, $this->config->navpane->middle->toArray());
			if (count($this->config->navpane->finish)) $navpane = array_merge($navpane, $this->config->navpane->finish->toArray());
			$navpane[] = array('t' => $this->config->control_lang_current['view_place']);
			$this->view->layout()->navpane = $navpane;
		}
		$this->config->control_lang_current = $this->config->control_lang_data[$this->config->control_lang];
		return $this;
	}

	function buildNavpane($id = null) {
    	$ret = array();
    	$model = new Default_Model_Cmenu();
    	if ($id === null) {
    		$id = $model->fetchOne('id', array('`controller` = ?' => $this->config->controller));
    	}
    	$item = $model->fetchRow(array('`id` = ?' => $id));
    	if ($item) {
    		array_unshift($ret, array(
    			't' => $item->title,
    			'c' => $item->controller,
    			'a' => $item->action,
    			'p' => $item->param
    		));
    		$inner = $this->buildNavpane($item->parentid);
    		if ($inner) $ret = array_merge($inner, $ret);
    	}
    	return $ret;
    }

	function configFromType() {
		if ($this->config->tree) $this->config->drag = 1;
		if (!$this->config->view) {
			switch ($this->config->type) {
				case 'add':
					$this->config->oac_apply = false;
					$this->config->place = $this->config->control_lang_current['add'];
					$view = 'form';
					break;
				case 'edit':
					$view = 'form';
					$this->config->place = $this->config->control_lang_current['Edit'];
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

    	//$this->config->set($conf_s);

		if (isset($conf->use_db)) $this->config->use_db = $conf->use_db;

		// Поля из модели
    	if ($this->config->use_db && $this->config->model) {
    		$meta = method_exists($this->config->model, 'info') ? $this->config->model->info('metadata') : array();
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

		if ($this->config->field && !isset($this->config->drag) && isset($this->config->field->{$this->config->field_orderid})) $this->config->drag = 1;

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
				$d[$k] = @$v['order'];
				unset($this->config->field->$k);
			}
			array_multisort($d, SORT_ASC, SORT_NUMERIC, $d1);
			$this->config->field->set($d1);
		}

		//if (!$this->config->drag && !$this->config->tree) $this->config->pager_perpage = 100;

		if ($this->config->drag || $this->config->tree) $this->config->pager_scroll = false;


		$this->config->control_lang_current = $this->config->control_lang_data[$this->config->control_lang];
    	return $this;
	}

	function configFromRequest() {
		if ($this->config->param['orderby']) $this->config->orderby = str_replace('list_', '', $this->config->param['orderby']);
    	if ($this->config->param['orderdir']) $this->config->orderdir = $this->config->param['orderdir'];
    	if ($this->config->param['page']) $this->config->pager_page = $this->config->param['page'];
    	if ($this->config->param['rows']) $this->config->pager_perpage = $this->config->param['rows'];
    	if ($this->config->tree && $this->config->param['oid']) {
    		$this->config->tree_opened = Zkernel_Common::getOuterIds(array(
    			'model' => $this->config->model,
    			'id' => $this->config->param['oid']
    		));
    		$this->config->tree_opened[] = $this->config->param['oid'];
    	}

    	if ($this->config->use_db && $this->config->drag && $this->config->type == 'add' && $this->config->field && isset($this->config->field->{$this->config->field_orderid})) {
    		$nid = $this->config->model->fetchOne('MAX(`'.$this->config->field_orderid.'`)');
    		$this->config->post_field_extend->set(array(
    			$this->config->field_orderid => $nid + 1
    		));
    	}
		if ($this->config->tree && $this->config->type == 'add' && $this->config->field && isset($this->config->field->{$this->config->tree_field})) $this->config->post_field_extend->set(array(
    		$this->config->tree_field => $this->config->param['id']
    	));
    	if ($this->config->param['cid'] && isset($this->config->field->{$this->config->field_link})) {
    		$this->config->post_field_extend->set(array(
    			$this->config->field_link => $this->config->param['cid']
    		));
    	}

    	$this->config->orderby = str_replace('list_', '', $this->config->orderby);

    	$this->config->control_lang_current = $this->config->control_lang_data[$this->config->control_lang];
    	return $this;
	}

	public function routeShow() {
    	$menu_model = new Default_Model_Cmenu();
		$menu = $menu_model->fetchRow(array('`controller` = ?' => $this->config->controller));
    	if ($this->config->post['nd']) {
			$rows = array();
			$where = $this->config->where ? $this->config->where->toArray() : array();
			if ($this->config->post['search'] != 'false') {
				foreach ($this->config->field as $el) {
					if (isset($_POST[$el->name])) $where['`'.$el->name.'` LIKE ?'] = '%'.$_POST[$el['name']].'%';
				}
			}
			if ($this->config->tree) {
				$parentid = (string)$this->config->post['nodeid'];
				if ($parentid) {
					$s = new Zend_Session_Namespace();
					$oids = Zkernel_Common::getOuterIds(array(
		    			'model' => $this->config->model,
		    			'id' => $parentid
		    		));
					if (!in_array($parentid, $oids)) $s->control['history'][$this->config->controller]['oid'] = $parentid;
				}
				$level = $this->config->post['n_level'];
				$level = strlen($level) > 0 ? $level + 1 : 0;
				$where['`'.$this->config->tree_field.'` = ?'] = $parentid;
				$parentid = $parentid == 0 ? null : $parentid;
			}
			if ($this->config->param['cid'] && isset($this->config->field->{$this->config->field_link})) $where['`'.$this->config->field_link.'` = ?'] = $this->config->param['cid'];
			/*if ($this->config->data) {
				if (!$this->config->data_cnt) $this->config->data_cnt = count($this->config->data);
			}
			else {*/
				$rd = $this->config->model->fetchControlList(
			    	$where,
			    	$this->config->orderby.' '.$this->config->orderdir,
			    	$this->config->pager_perpage
			    		? $this->config->pager_perpage
			    		: null,
			    	$this->config->pager_perpage
			    		? ($this->config->pager_page - 1) * $this->config->pager_perpage
			    		: null
			    );

			    $data = $rd->toArray();
			    $this->config->data_cnt = $this->config->model->fetchCount($where);

			    if ($this->config->tree && $data && $this->config->field) {
			    	foreach ($data as &$el) {
			    		$el['_level'] = $level;
			    		$el['_count'] = (int)$this->config->model->fetchCount(array(
			    			'`'.$this->config->tree_field.'` = ?' => (string)$el['id']
			    		));
			    	}
			    }
			    $this->config->data = $this->view->override($data, $this->config->controller);
			//}
		}
		else {
			$menus = $menu_model->fetchAll(array('`parentid` = ?' => @(int)$menu->id, '`show_it` = 0', 'orderid'));
			if ($menus) {
				foreach ($menus as $num => $el) {
					if(!$this->view->user()->isAllowed($this->view->user('role'), $el->resource)) continue;
					$cl_0 = stripos($el->param, 'cl=0');
					$cl_1 = stripos($el->param, 'cl=1');
					$this->config->button_top[] = array(
						'inner' => $num == 0 ? 1 : 0,
						'controller' => $el->controller,
						'action' => $el->action ? $el->action : 'ctlshow',
						'field' => 'cid',
						'title' => $el->title,
						'cl' => $cl_0 !== false ? 'f' : ($cl_1 !== false ? 'a' : 't')
					);
				}
			}
			$menu = $menu_model->fetchRow(array('`id` = ?' => @(int)$menu->parentid));
			if ($menu) {
				if (strlen($this->config->param['cid']) && !$this->config->param['cid']) {
					$this->config->stop_frame = 1;
					$this->view->inlineScript('script', 'c.go("'.$menu->controller.'", "'.$menu->action.'");');
					$this->config->info[] = $this->config->control_lang_current['no_sel'];
				}
				else {
					$s = new Zend_Session_Namespace();
					$s->control['history'][$menu->controller]['oid'] = $this->config->param['cid'];
					if ($menu->controller) {
						$cl_0 = stripos($menu->param, 'cl=0');
						$this->config->button_bottom[] = array(
							'controller' => $menu->controller,
							'action' => $menu->action ? $menu->action : 'ctlshow',
							'title' => $this->config->control_lang_current['back'],
							'cl' => $cl_0 !== false ? 'f' : 't'
						);
					}
				}
			}
		}
		$this->config->pre_view;
		echo $this->view->render($this->config->view);
		return $this;
    }

	public function buildForm() {
    	$id = $this->config->param['id'];
    	$form = new Zkernel_Form(array(
	    	'accept-charset' => 'utf-8',
    		'onsubmit' => 'return c.submit()',
    		'id' => 'c_form'
    	));
    	if ($this->config->field) {

			$d = array();
			$d1 = $this->config->field->toArray();
                        foreach ($d1 as $k => $v) {
				$d[$k] = @$v['order'];
				unset($this->config->field->$k);
			}
			array_multisort($d, SORT_ASC, SORT_NUMERIC, $d1);
			$this->config->field->set($d1);

            $fields = $this->config->field;

			if ($this->config->zk_meta) {

				$fields['meta_title'] = array(
					'title' => 'Title',
					'description' => $this->config->control_lang_current['meta_title'],
					'order' => 2000
				);
				$fields['meta_keywords'] = array(
					'title' => 'Keywords',
					'description' => $this->config->control_lang_current['meta_keywords'],
					'order' => 2001
				);
				$fields['meta_description'] = array(
					'title' => 'Description',
					'description' => $this->config->control_lang_current['meta_description'],
					'order' => 2002
				);
				$fields['meta_show_title'] = array(
					'title' => $this->config->control_lang_current['meta_show_title_title'],
					'description' => $this->config->control_lang_current['meta_show_title'],
					'type' => 'select',
					'param' => array(
						'multiOptions' => array('1' => 'Да', '0' => 'Нет')
					),
					'order' => 2003
				);
			}

			foreach ($fields as $el) {
				if (!$el->active) continue;
				if (@(int)$this->config->post['sposted'] && !array_key_exists($el->name, $this->config->post->toArray())) continue;
				$p = new Zkernel_Config_Control($el->param, array(
					//'type' => 'text',
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
                                        if (!isset($p->button)) $p->button = $this->config->control_lang_current['button'];
					if (!isset($p->destination)) $p->destination = PUBLIC_PATH.'/upload/'.$this->config->controller.'_'.$el->name;
					if (!isset($p->fn)) {
						$where = $this->config->where ? $this->config->where->toArray() : array();
						$where['`id` = ?'] = $id;
						if ($this->config->use_db) $p->fn = $this->config->model->fetchOne($el->name, $where);
					}
				}
                                if ($el->type == 'mce') {
                                        if (!isset($p->lang)) $p->lang = $this->config->control_lang;
				}
				if ($el->type == 'select') $p->class = 'c_select';
				else if ($el->type == 'textarea') $p->class = 'ui-state-default ui-corner-all c_textarea';
				else $p->class = 'ui-state-default ui-corner-all c_input';
				$form->addElement($el->type, $el->name, $p->toArray());
			}

			if ($this->config->zk_meta && !@(int)$this->config->post['sposted']) {
				$form->addDisplayGroup(array('meta_title', 'meta_keywords', 'meta_description', 'meta_show_title'), 'meta', array('legend' => $this->config->control_lang_current['more'], 'class' => 'c_collapse'));
			}
		}

		$form->addElement('submit', 'oac_ok', array(
		    'label' => $this->config->oac_ok_title,
    		'class' => 'c_button'
		));

		$oac_array = array('oac_ok');
		if ($this->config->oac_cancel) {
			$oac_array[] = 'oac_cancel';
			$form->addElement('submit', 'oac_cancel', array(
				'label' => $this->config->control_lang_current['cancel'],
				'onclick' => 'return c.go("'.$this->config->request_cancel->controller.'", "'.$this->config->request_cancel->action.'", '.Zend_Json::encode(Zkernel_Common::url2array($this->config->request_cancel->param)).')',
				'class' => 'c_button'
			));
		}

		if ($this->config->oac_apply) {
			$oac_array[] = 'oac_apply';
			$form->addElement('submit', 'oac_apply', array(
				'label' => $this->config->control_lang_current['submit'],
				'onclick' => 'return c.submit(1)',
				'class' => 'c_button'
			));
		}

		$form->addDisplayGroup($oac_array, 'oac');

    	return $form;
    }

	public function routeForm() {
    	$id = $id_old = $this->config->param['id'];

    	if ($this->config->type == 'edit' && !$id) {
			$this->config->info[] = $this->config->control_lang_current['no_sel'];
			$this->config->stop_frame = true;
		}
    	else {
		    $this->config->form = $this->buildForm();

			if (@(int)$this->config->post['cposted'] || @(int)$this->config->post['sposted']) {
				if ($this->config->form->isValid($this->config->post->toArray())) {
					if ($this->config->type == 'add') $id = $this->config->use_db
						? (method_exists($this->config->model, 'fetchNextId') ? $this->config->model->fetchNextId() : 0)
						: 0;

					$this->config->data = $this->config->form->getValues();

					if ($this->config->static_field && !@$this->config->data->{$this->config->static_field->field_dst} && $this->config->type == 'add') {
						$stitle = Zkernel_Common::stitle($this->config->data[$this->config->static_field->field_src], $this->config->static_field->length);
    					$stitle = $stitle ? $stitle : '_';
    					$stitle_n = $stitle;
						if ($this->config->static_field->unique && $this->config->use_db) {
							$stitle_p = -1;
							do {
								$stitle_p++;
								$stitle_n = $stitle.($stitle_p == 0 ? '' : $stitle_p);
								$w = array('`'.$this->config->static_field->field_dst.'` = ?' => $stitle_n);
								if ($this->config->type == 'edit') $w['`id` != ?'] = $id;
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
							$m2m_orderid = (int)$this->config->field->$k->m2m->orderid;
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
											'`id` = ?' => $m2m_el->id
										));
									}
									else $m2m_ids[] = $m2m_el->$m2m_foreign;
								}
								// Добавляем
								if ($m2m_new) foreach ($m2m_new as $m2m_el) {
									if (!in_array($m2m_el, $m2m_ids)) {
										$m2m_changed = true;
										$m2md = array(
											$m2m_self => $id,
											$m2m_foreign => $m2m_el
										);
										if ($m2m_orderid) {
											$nid = $m2m_model->fetchOne('MAX(`orderid`)');
											$m2md['orderid'] = $nid + 1;
										}
										$yyy = $m2m_model->insert($m2md);
									}
								}
							}
							unset($this->config->data->$k);
						}
					}

					if ($this->config->zk_meta) {
						$empty = !$this->config->data->meta_title && !$this->config->data->meta_keywords && !$this->config->data->meta_description/* && !$this->config->data->meta_show_title*/;
						$md = array(
							'title' => $this->config->data->meta_title,
							'keywords' => $this->config->data->meta_keywords,
							'description' => $this->config->data->meta_description,
							'show_title' => $this->config->data->meta_show_title
						);
						$mm = new Default_Model_Meta();
						$me = (int)$mm->fetchOne('id', array('`oid` = "'.$this->config->controller.'_'.$id.'"'));

						if ($me) {
							if ($empty) $mm->delete(array('`id` = ?' => $me));
							else $mm->update($md, array('`id` = ?' => $me));
						}
						else {
							$md['oid'] = $this->config->controller.'_'.$id;
							$mm->insert($md);
						}
					}

					$ok = false;
					$this->config->func_override;
					$this->config->func_check;
					if ($this->config->use_db && count($this->config->info) == 0) {
						$data_db = $this->config->data->toArray();
						foreach ($data_db as $k => $v) {
							if (!array_key_exists($k, method_exists($this->config->model, 'info') ? $this->config->model->info('metadata') : array())) unset($data_db[$k]);
						}
						if ($this->config->type == 'edit') {
							$where = $this->config->where ? $this->config->where->toArray() : array();
							$where['`id` = ?'] = $id;
							$ok = $data_db ? $this->config->model->updateControl($data_db, $where) : false;
							if ($ok) {
								$keys = array_keys($data_db);
								$dd = array();
								foreach ($keys as $key) {
									if (@(int)$this->config->field->$key->single) $dd[$key] = '';
								}
								if ($dd) {
									unset($where['`id` = ?']);
									$where['`id` != ?'] = $id;
									$this->config->model->updateControl($dd, $where);
								}
							}
						}
						else $ok = $this->config->data->id =  $this->config->model->insertControl($data_db);
					}
					if ($ok || $m2m_changed) {
						$this->config->info[] = $this->config->control_lang_current['data_saved'];
						$this->config->func_success;
					}
					else {
						//$this->config->info[] = 'Изменений данных не было';
					}
					if (!@$this->config->post['is_apply'] && !@$this->config->post['sposted']) $this->view->inlineScript('script', 'c.go("'.$this->config->request_ok->controller.'", "'.$this->config->request_ok->action.'", '.Zend_Json::encode(Zkernel_Common::url2array($this->config->request_ok->param)).');');
				}
				else {
					$this->config->info_type = 'e';
					foreach ($this->config->form->getErrors() as $k => $el) {
						if ($el) $this->config->info[] = $this->config->form->getElement($k)->getLabel().': '.implode(', ', $el);
					}
				}
				if ($this->config->scroll_top) $this->view->inlineScript('script', 'window.scroll(0, 0);');
				$this->config->stop_frame = true;
			}
			else {
				if ($this->config->type == 'edit') {
					$where = $this->config->where ? $this->config->where->toArray() : array();
					$where['`id` = ?'] = $id;
					$data = $this->config->model->fetchControlCard($where);

					$data = $this->view->override()->overrideSingle($data, $this->config->controller, array('multilang_nofall' => true, 'module_nofall' => true));

					$this->config->data->set($data->toArray());

					if ($this->config->field && isset($this->config->field->{$this->config->field_title}) && $this->config->data->{$this->config->field_title}) {
						$this->config->navpane->finish[] = array(
		    				't' => $this->config->data->{$this->config->field_title},
							'c' => $this->config->controller,
							'a' => $this->config->action,
							'p' => ''
		    			);
		    		}
					//$data = $data ? $data->toArray() : array();
					$els = $this->config->form->getElements();
					if ($els) {
						foreach ($els as $el) {
							$k = $el->getName();
							if (@$this->config->field->$k->m2m) {
								$m2m_model = $this->config->field->$k->m2m->model;
								$m2m_self = $this->config->field->$k->m2m->self;
								$m2m_foreign = $this->config->field->$k->m2m->foreign;
								$this->config->data->$k = $m2m_model->fetchCol($m2m_foreign, array(
									'`'.$m2m_self.'` = ?' => $id
								));
							}
						}
					}

					if ($this->config->zk_meta) {
						$mm = new Default_Model_Meta();
						$md = $mm->fetchRow(array('`oid` = "'.$this->config->controller.'_'.$id.'"'));
						if ($md) {
							foreach ($md as $k => $v) $this->config->data['meta_'.$k] = $v;
						}
					}

					//$this->config->data->set($this->config->data->toArray());
					$this->config->func_preset;
					$this->config->form->setDefaults($this->config->data->toArray());
				}
			}
    		if ($this->config->tree && $id) {
				$s = new Zend_Session_Namespace();
				$s->control['history'][$this->config->controller]['oid'] = $id;
			}
		}
		echo $this->view->render($this->config->view);
		return $this;
    }

    public function routeDrag() {
    	$cur = $this->config->model->fetchRow(array('`id` = ?' => (int)$this->config->param['id']));
    	$prev = $this->config->model->fetchRow(array('`id` = ?' => (int)$this->config->param['prev']));
    	$ok = false;
    	$s = new Zend_Session_Namespace();
		unset($s->control['history'][$this->config->controller]['prev']);
    	if ($cur) {
	    	$cur->{$this->config->field_orderid} = @(int)$prev->{$this->config->field_orderid} + 1;
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
    		$this->config->info[] = $this->config->control_lang_current['element_moved'];
    		$this->config->func_success;
    	}
    	else $this->config->info[] = $this->config->control_lang_current['element_not_moved'];
    	$this->config->stop_frame = true;
    	echo $this->view->render($this->config->view);
		return $this;
    }

	public function routeDelete() {
    	$ids = $this->config->param['ids'];
    	$ids = $ids ? explode(',', $ids) : array();

    	if (!$ids) {
    		$id = $this->config->param['id'];
    		$ids = $id ? array($id) : array();
    	}
    	if ($this->config->tree && @$ids[0]) {
    		$where = $this->config->where ? $this->config->where->toArray() : array();
    		$where['`id` = ?'] = $ids[0];
    		$pid = (string)$this->config->model->fetchOne($this->config->tree_field, $where);
    		$s = new Zend_Session_Namespace();
			$s->control['history'][$this->config->controller]['oid'] = $pid;
		}
    	$cnt = 0;
    	if ($ids) {
	    	foreach ($ids as $el) {
	    		$where = $this->config->where ? $this->config->where->toArray() : array();
				$where['`id` = ?'] = $el;
	    		$data = $this->config->model->fetchControlCard($where);
				$data = $this->view->override()->overrideSingle($data, $this->config->controller, array('multilang_nofall' => true, 'module_nofall' => true));
				$this->config->data->set($data->toArray());
				$this->config->skip = false;
	    		$this->config->func_check;
	    		if (!$this->config->skip) {
		    		$ok = $this->config->use_db ? $this->config->model->deleteControl($where) : true;
		    		if ($ok) {
		    			$form = $this->buildForm();
		    			$els = $form->getElements();
		    			if ($els) {
		    				foreach ($els as $k => $v) {
								if ($v->getType() == 'Zkernel_Form_Element_Uploadify') {
									if (isset($v->destination) && isset($this->config->data->$k)) @unlink($v->destination.'/'.$this->config->data->$k);
								}
		    				}
		    			}
		    			$cnt++;
		    		}
	    		}
	    	}
	    	if ($cnt) $this->config->info[] = $this->config->control_lang_current['element_deleted'].$cnt;
		}
		else $this->config->info[] = $this->config->control_lang_current['no_sel'];
		if ($cnt) {
			$this->config->func_success;
			$this->view->inlineScript('script', 'c.go("'.$this->config->request_ok->controller.'", "'.$this->config->request_ok->action.'", '.Zend_Json::encode(Zkernel_Common::url2array($this->config->request_ok->param)).');');
		}
		else $this->config->stop_frame = true;
		echo $this->view->render($this->config->view);
		return $this;
    }

    public function routeNone() {
    	$this->view->render($this->config->view);
		return $this;
    }

	public function control($data = null) {
		$this->configInit($data);
		$this->userInit();
		if ($data !== null) $this->config->set($data);
		return $this;
	}
}
