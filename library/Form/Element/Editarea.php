<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Editarea extends Zend_Form_Element_Textarea {
	public function render(Zend_View_Interface $view = null) {
    	$js =
'$.include("/zkernel/ctl/edit_area/edit_area_full.js", function() {
	EAL.prototype.window_loaded();
	editAreaLoader.init({
		id: "'.$this->getName().'"
		,start_highlight: true
		,allow_resize: "no"
		,toolbar: "undo,redo,highlight"
		,allow_toggle: false
		,language: "ru"
		,syntax: "'.$this->getAttrib('syntax').'"
		,change_callback: "cb_'.$this->getName().'"
	});
	window.cb_'.$this->getName().' = function(id) { $("#'.$this->getName().'").val(editAreaLoader.getValue(id)); };
});
';
    	$this->getView()->inlineScript('script', $js);
		return parent::render($view);
	}
}
