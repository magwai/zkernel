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
		$this->addValidator('Date', true, array(
			Zend_Locale_Format::convertPhpToIsoFormat('Y-m-d')
		));
	}

	public function render(Zend_View_Interface $view = null)
    {
    	$js =
'$.include("/zkernel/js/jquery/ui/ui.datepicker.js", function() {
	$("input[name='.$this->getName().']").datepicker({
		dateFormat: "dd.mm.yy",
		firstDay: 1
	});
});';
    	Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval($js);

    	return parent::render($view);
	}

	function getValue() {
		$value = parent::getValue();
		if ($value == '0000-00-00 00:00:00') $value = '';
		else if ($value) {
			$value = strtotime($value);
			$value = date('Y-m-d', $value);
		}
		return $value;
	}
}