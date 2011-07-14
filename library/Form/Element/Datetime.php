<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Datetime extends Zend_Form_Element_Text
{
	public $helper = 'formDatetime';

	public function init() {
		$this->addValidator('Date', true, array(
			Zend_Locale_Format::convertPhpToIsoFormat('Y-m-d H:i:s')
		));
	}

	public function render(Zend_View_Interface $view = null) {
		$a = $this->getAttribs();
		$regional = @$a['regional'] ? $a['regional'] : 'ru';
                unset($a['helper']);
		unset($a['regional']);
		$o = array(
                    'dateFormat' => 'dd.mm.yy',
                    'firstDay' => '1'
                );
    	if ($a) $o = array_merge($o, $a);

    	$js =
'$.include("/zkernel/ctl/timepicker/jquery-ui-timepicker-addon.css|link");
 $.include(["/zkernel/ctl/timepicker/jquery-ui-timepicker-addon.js", "/zkernel/js/jquery/ui/i18n/jquery.ui.datepicker-'.$regional.'.js", "/zkernel/ctl/timepicker/localization/jquery-ui-timepicker-'.$regional.'.js"], function() {
	$("input[name='.$this->getName().']").datetimepicker('.Zend_Json::encode($o).');
});';
    	$this->getView()->inlineScript('script', $js);
    	return parent::render($view);
	}

	function getValue() {
		$value = parent::getValue();
                if ($value == '0000-00-00 00:00:00') $value = '';
		else if ($value) {
			$value = strtotime($value);
			$value = date('Y-m-d H:i:s', $value);
		}
		return $value;
	}
}