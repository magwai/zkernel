<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Point extends Zend_Form_Element_Text {
	public $helper = 'formPoint';

	public function render(Zend_View_Interface $view = null) {
    	$color = $this->getAttrib('color');
    	$type = $this->getAttrib('type');
    	$value = $this->getValue();
    	$value = explode(';', $value);
    	if (count($value) == 1) $val = explode('|', $value[0]);
    	else {
    		$val = array();
    		foreach ($value as $el) $val[] = explode('|', $el);
    	}

    	$js =
'$.include("/zkernel/ctl/point/point.js", function() {
	point.init("'.$this->getName().'", '.($value ? Zend_Json::encode($val) : 'null').', {
    	"url": "'.$this->getAttrib('url').'",'.
    	($type ? '"type": "'.$type.'",' : '').
    	($color ? '"color": "'.$color.'",' : '').
    '});
});
';
    	$js = str_replace(',}', '}', $js);
    	$this->getView()->inlineScript('script', $js);
    	return parent::render($view);
	}
}
