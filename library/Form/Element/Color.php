<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Color extends Zend_Form_Element_Text {
	public function render(Zend_View_Interface $view = null) {
    	$js =
'$.include("/zkernel/ctl/colorpicker/css/colorpicker.css|link");
$.include("/zkernel/ctl/colorpicker/colorpicker.js", function() {
	$("input[name='.$this->getName().']").ColorPicker({
    	color: "'.$this->getValue().'",
    	onBeforeShow: function () {
			$(this).ColorPickerSetColor(this.value);
		},
    	onSubmit: function(hsb, hex, rgb, el) {
    		$(el).val("#" + hex);
    		$(el).ColorPickerHide();
    	}
    });
});
';
    	$this->getView()->inlineScript('script', $js);
		return parent::render($view);
	}
}
