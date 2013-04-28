<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Uploadify extends Zend_Form_Element_Hidden {
	public $helper = 'formUploadify';

	public function render(Zend_View_Interface $view = null) {
    	$o = array(
            'buttonText' => /*$this->button ? $this->button : */'Browse...',
    		'fileDataName' => $this->getName(),
	    	'folder' => '/'.$this->destination,
	    	'scriptData' => array(
    			'length' => $this->getAttrib('length') ? $this->getAttrib('length') : 20,
				'old' => $this->getAttrib('multi') ? 'multi' : $this->getValue(),
    			'sid' => session_id()
    		)
    	);

    	$o = array_merge($o, $this->getAttribs());

    	if ($this->getAttrib('multi')) $o['multi'] = 1;
		$crop = $this->getAttrib('jcrop');

    	$s = new Zend_Session_Namespace();
    	$js =
'$.include("/zkernel/ctl/uploadify/uploadify.css|link");
'.($crop ? '$.include("/zkernel/ctl/jcrop/css/jquery.jcrop.css|link");' : '').'
$.include(["/zkernel/js/swfobject.js", "/zkernel/ctl/uploadify/jquery.uploadify.js", "/zkernel/ctl/uploadify/zuploadify.js"'.($crop ? ', "/zkernel/ctl/jcrop/js/jquery.jcrop.js"' : '').'], function() {
	zuf.init('.Zend_Json::encode($o, false, array('enableJsonExprFinder' => true)).');
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

	public function getValue() {
    	$value = parent::getValue();
		if (!isset($this->url)) $this->url = str_ireplace(PUBLIC_PATH, '', $this->destination);
    	$values = explode('*', $value);
    	if (!$this->getAttrib('multi')) $values = array($values[0]);
    	foreach ($values as $num => $v) {
	    	$ss = substr($v, 0, 2);
	    	/*if ($ss == 'u|') {
	    		$values[$num] = $v = str_replace('u|', '', $v);
	    		$this->getView()->inlineScript()->offsetSetScript(500 + $num, 'zuf.add("'.$this->getName().'", "'.$v.'", "'.$this->url.'", '.(int)$this->isRequired().');');
	    	}
	    	else */
			if ($ss == 'd|') {
	    		$v = str_replace('d|', '', $v);
	    		@unlink($this->destination.'/'.$v);
	    		unset($values[$num]);
	    		$this->setValue(implode('*', $values));
	    		$this->getView()->inlineScript()->offsetSetScript(501 + $num, 'zuf.remove("'.$this->getName().'", "'.$v.'");');
	    	}
			else if ($v) {
				$values[$num] = $v = str_replace('u|', '', $v);
	    		$this->getView()->inlineScript()->offsetSetScript(500 + $num, 'zuf.add("'.$this->getName().'", "'.$v.'", "'.$this->url.'", '.(int)$this->isRequired().');');
			}
    	}
    	return implode('*', $values);
    }

    public function isValid($value, $context = null) {
    	$this->clearValidators();
    	return parent::isValid($value, $context = null);
    }
}
