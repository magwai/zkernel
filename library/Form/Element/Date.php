<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Date extends Zend_Form_Element_Text
{
	public $helper = 'formDate';

	public function init() {
		if (parent::getAttrib('type') !== 'multyrange') $this->addValidator('Date', true, array(Zend_Locale_Format::convertPhpToIsoFormat('Y-m-d')));
	}

	public function render(Zend_View_Interface $view = null) {
		$a = $this->getAttribs();
		$regional = @$a['regional'] ? $a['regional'] : 'ru';
		unset($a['regional']);
		unset($a['helper']);

		if (@$a['type'] == 'birth') {
			$lang = array(
				'en' => array(),
				'ru' => array(
					'months' => array(
						'short' => array('Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'),
						'long' => array('Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь')
					),
					'titles' => array('Год', 'Месяц', 'День')
				)
			);
			$o = array(
				'maxAge' => '90',
				'maxYear' => date('Y') - 5,
				'monthFormat' => 'long',
				'dateFormat' => 'littleEndian'
			);
			$o = array_merge($o, $lang[$regional]);
			$js = '$.include(["/zkernel/js/jquery/jquery.birthdaypicker.js"], function() {$("input[name='.$this->getName().']").birthdaypicker('.Zend_Json::encode($o, false, array('enableJsonExprFinder' => true)).');});';
		}
		else {
			$o = array(
				'dateFormat' => 'dd.mm.yy',
				'firstDay' => '1'
			);
			if ($a) $o = array_merge($o, $a);

			$callbacks = array('create', 'beforeShow', 'beforeShowDay', 'onChangeMonthYear', 'onClose', 'onSelect');
			foreach ($o as $k=>$val){
				if (in_array($k, $callbacks)){
					$o[$k] = new Zend_Json_Expr($val);
				}
			}
			$js = @$o['type'] == 'multyrange' ?
				'$.include(["/zkernel/js/jquery/ui/ui.datepicker.js", "/zkernel/js/jquery/ui/i18n/jquery.ui.datepicker-'.$regional.'.js", "/zkernel/js/jquery/ui/ui.multidatespicker.js"], function() {$("input[name='.$this->getName().']").multiDatesPicker('.Zend_Json::encode($o).');});'
				:
				'$.include(["/zkernel/js/jquery/ui/ui.datepicker.js", "/zkernel/js/jquery/ui/i18n/jquery.ui.datepicker-'.$regional.'.js"], function() {$("input[name='.$this->getName().']").datepicker('.Zend_Json::encode($o, false, array('enableJsonExprFinder' => true)).');});';
		}
		$this->getView()->inlineScript('script', $js);
    	return parent::render($view);
	}

	function getValue() {
		$value = parent::getValue();
		if ($value == '0000-00-00 00:00:00') $value = '';

		else if ($value && parent::getAttrib('type') !== 'multyrange') {
			$value = strtotime($value);
			$value = date('Y-m-d', $value);
		}
		return $value;
	}
}
