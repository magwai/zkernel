<?php

class Zkernel_Form_Element_Uploadify extends Zend_Form_Element_Hidden
{
	public $helper = 'formUploadify';

	public function render(Zend_View_Interface $view = null)
    {
    	$js =
'$.include("/zkernel/ctl/uploadify/uploadify.css|link");
$.include(["/zkernel/js/swfobject.js", "/zkernel/ctl/uploadify/jquery.uploadify.js", "/zkernel/ctl/uploadify/zuploadify.js"], function() {
	var hh = $("input[name='.$this->getName().'][type=file]");
	hh.uploadify({
		"uploader"  : "/zkernel/ctl/uploadify/uploadify.swf",
		"cancelImg" : "/zkernel/ctl/uploadify/cancel.png",
		"script"    : "/fu/",
		"onComplete": function(e, queueID, fileObj, response, data) { hh.trigger("complete", {queueID: queueID, fileObj: fileObj, response: response, data: data}); },
		"onError"	: function(e, queueID, fileObj, errorObj) { hh.trigger("error", {queueID: queueID, fileObj: fileObj, errorObj: errorObj}); },
		"onSelect"	: function() { if ($("input[name='.$this->getName().'][type=hidden]").hasClass("zuf_deleted")) hh.prevAll("em").find(">a").click();  }
	});
	zuf.init("'.$this->getName().'");
});';
    	Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval($js);
		$url = $this->getAttrib('url');
		if (!$url) {
			$path = $this->getAttrib('path');
			$url = str_ireplace(PUBLIC_PATH, '', $path);
			$this->setAttrib('url', $url);
		}
		$this->setAttrib('required', $this->isRequired());
    	return parent::render($view);
	}

	public function getValue()
    {
    	$path = $this->getAttrib('path');
    	$value = parent::getValue();
    	$ss = substr($value, 0, 2);
    	if ($ss == 'u|') {
    		$value = substr($value, 2);
			if (file_exists($value)) {
				$fn = $this->getAttrib('fn');

		    	$name = explode('/', $value);
		    	$name = array_pop($name);
		    	$name = substr($name, 3);

		    	$ext = strrpos($name, '.');
		    	if ($ext !== false) {
		    		$t = substr($name, $ext + 1);
		    		$name = substr($name, 0, $ext);
		    		$ext = $t;
		    	}
		    	$p = '';
		    	$full_name = $name.$p.($ext ? '.'.$ext : '');
		    	if ($fn && $fn == $full_name) $full_name = $fn;
		    	else {
		    		if ($fn && $fn != $full_name) unlink($path.'/'.$fn);
		    		while (file_exists($path.'/'.$name.$p.($ext ? '.'.$ext : ''))) $p = $p ? $p + 1 : 1;
		    		$full_name = $name.$p.($ext ? '.'.$ext : '');
		    	}
		    	if (!file_exists($path)) @mkdir($path);
		    	$res = @copy($value, $path.'/'.$full_name);
		    	if ($res) @unlink($value);
		        $value = $full_name;
		        $this->setValue($full_name);
		        Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval('$("input[name='.$this->getName().'][type=hidden]").val("'.$full_name.'");');
			}
    	}
    	else if ($ss == 'd|') {
    		$value = substr($value, 2);
    		unlink($path.'/'.$value);
    		$this->setValue('');
    		$value = '';
    	}
    	return $value;
    }
}