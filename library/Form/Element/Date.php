<?php

class Zkernel_Form_Element_Date extends Zend_Form_Element_Text
{
	public $helper = 'formDate';

	public function render(Zend_View_Interface $view = null)
    {
    	$js =
'$.include("/zkernel/js/jquery/ui/ui.datepicker.js", function() {
	$("input[name='.$this->getName().']").datepicker({
		dateFormat: "dd.mm.yy"
	});
});';
    	Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval($js);

    	return parent::render($view);
	}

	function getValue() {
		$value = parent::getValue();
		if ($value) {
			$value = strtotime($value);
			$value = date('Y-m-d', $value);
		}
		return $value;
	}
}