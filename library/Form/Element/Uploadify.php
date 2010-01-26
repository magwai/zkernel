<?php

class Zkernel_Form_Element_Uploadify extends Zend_Form_Element_Hidden
{
	public $helper = 'formUploadify';

	public function render(Zend_View_Interface $view = null)
    {
    	$s = new Zend_Session_Namespace();
    	$js =
'$.include("/zkernel/ctl/uploadify/uploadify.css|link");
$.include(["/zkernel/js/swfobject.js", "/zkernel/ctl/uploadify/jquery.uploadify.js", "/zkernel/ctl/uploadify/zuploadify.js"], function() {
	zuf.init({
    	fileDataName: "'.$this->getName().'",
    	folder: "'.$this->destination.'",
    	scriptData: {old: "'.$this->getValue().'", sid: "'.session_id().'"}
    });
});';
    	Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval($js);
		if (!isset($this->url)) $this->url = str_ireplace(PUBLIC_PATH, '', $this->destination);
		$this->required = $this->isRequired();

		$s->form[$this->getName()] = array(
			'folder' => $this->destination,
			'value' => $this->getValue(),
			'validators' => $this->getValidators()
		);
    	return parent::render($view);
	}

	public function getValue()
    {
    	$value = parent::getValue();
    	$ss = substr($value, 0, 2);
    	if ($ss == 'u|') {
    		$value = substr($value, 2);
    		if (!isset($this->url)) $this->url = str_ireplace(PUBLIC_PATH, '', $this->destination);
    		Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval('zuf.set("'.$this->getName().'", "'.$value.'", "'.$this->url.'/'.$value.'", '.(int)$this->isRequired().');');
    	}
    	else if ($ss == 'd|') {
    		$value = substr($value, 2);
    		@unlink($this->destination.'/'.$value);
    		$value = '';
    		$this->setValue($value);
    		Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval('zuf.remove("'.$this->getName().'");');
    	}
    	return $value;
    }

    public function isValid($value, $context = null) {
    	$this->clearValidators();
    	return parent::isValid($value, $context = null);
    }
}