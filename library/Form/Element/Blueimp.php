<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Blueimp extends Zend_Form_Element_Hidden {
	public $helper = 'formBlueimp';

	public function render(Zend_View_Interface $view = null) {
    	$o = array(
            'dataType' => 'json',
			'url' => '/z/fu'
    	);

    	$o = array_merge($o,$this->getAttribs());

    	$s = new Zend_Session_Namespace();
    	$js =
'$.include(["/zkernel/ctl/blueimp/js/vendor/jquery.ui.widget.js", "/zkernel/ctl/blueimp/js/jquery.iframe-transport.js", "/zkernel/ctl/blueimp/js/jquery.fileupload.js"], function() {
	$("#'.$this->getName().'").fileupload('.Zend_Json::encode($o, false, array('enableJsonExprFinder' => true)).');
});';
    	if (!isset($this->url)) $this->url = str_ireplace(PUBLIC_PATH, '', $this->destination);
		$this->required = $this->isRequired();

		$s->form[$this->getName()] = array(
			'folder' => $this->destination,
			'value' => $this->getValue(),
			'validators' => $this->getValidators()
		);
		$this->getView()->inlineScript('script', $js);
    	return parent::render($view);
	}
/*
	public function getValue() {
    	$value = parent::getValue();
		if (!isset($this->url)) $this->url = str_ireplace(PUBLIC_PATH, '', $this->destination);
    	$values = explode('*', $value);
    	if (!$this->getAttrib('multi')) $values = array($values[0]);
    	foreach ($values as $num => $v) {
	    	$ss = substr($v, 0, 2);
	    	if ($ss == 'u|') {
	    		$values[$num] = $v = str_replace('u|', '', $v);
	    		$this->getView()->inlineScript('script', 'zuf.add("'.$this->getName().'", "'.$v.'", "'.$this->url.'", '.(int)$this->isRequired().');');
	    	}
	    	else if ($ss == 'd|') {
	    		$v = str_replace('d|', '', $v);
	    		@unlink($this->destination.'/'.$v);
	    		unset($values[$num]);
	    		$this->setValue(implode('*', $values));
	    		$this->getView()->inlineScript('script', 'zuf.remove("'.$this->getName().'", "'.$v.'");');
	    	}
    	}
    	return implode('*', $values);
    }

    public function isValid($value, $context = null) {
    	$this->clearValidators();
    	return parent::isValid($value, $context = null);
    }*/
}
