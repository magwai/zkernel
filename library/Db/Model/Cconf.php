<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Model_Cconf extends Zkernel_Db_Table {
	protected $_name = 'cconf';

	public function fetchValue($key, $action = null, $controller = null) {
		if ($controller === null) $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		if ($action === null) $action = substr(Zend_Controller_Front::getInstance()->getRequest()->getActionName(), 3);

		return $this->getAdapter()->fetchOne(
    		$this->select()
    			->from($this, array('value'))
    			->where('`controller` = ?', $controller)
    			->where('`action` = ?', $action)
    			->where('`key` = ?', $key)
    	);
    }

	public function fetchPairs($where = null, $action = null, $controller = null) {
		if ($controller === null) $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		if ($action === null) $action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		$select = $this->select()
    		//->from($this, array('CONCAT(`type`, ":", `key`)', 'value'))
    		->from($this, array('key', 'IF (`type` != "text", CONCAT(`type`, ":", `value`), `value`)'))
    		->where('`controller` = ?', $controller)
    		->where('`action` = ?', $action);
    	if ($where !== null) $select->where($where);
		return $this->getAdapter()->fetchPairs($select);
    }

	function getTypes() {
		return array(
			'text' => 'Текст',
			'php_function' => 'PHP функция',
			'js_function' => 'JS функция'
		);
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
				//'func_success' => 'php_function:$control->view->inlineScript("script", "window.location = \'.\';");'
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
}
