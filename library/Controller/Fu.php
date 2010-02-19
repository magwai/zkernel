<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @zk_title   		Загрузка файлов
 * @zk_config		0
 * @zk_routable		0
 */
class Zkernel_Controller_Fu extends Zkernel_Controller_Action {
	function indexAction() {
		$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$ret = array();
    	$ok = '';
    	$key = array_keys($_FILES);
    	$key = @$key[0];
    	if ($key){
    		Zend_Session::setId(@$_POST['sid']);
    		$s = new Zend_Session_Namespace();
			$tmp_name = $_FILES[$key]['tmp_name'];
    		$validators = array('Zend_Validate_File_Upload' => new Zend_Validate_File_Upload());
    		if (@$s->form[$key]['validators']) $validators = array_merge($validators, $s->form[$key]['validators']);

    		$valid = true;
    		foreach ($validators as $k => $el) {
    			$el = clone $el;
    			$tocheck = $k == 'Zend_Validate_File_Upload' ? $key : $tmp_name;
    			if (!$el->isValid($tocheck, $_FILES[$key])) {
    				$valid = false;
    				$e = $el->getErrors();
    				if ($e) foreach ($e as $el_1) if (!in_array($el_1, $ret)) $ret[] = $el_1;
    			}
    		}
    		if ($valid) {
    			$name = $_FILES[$key]['name'];
		    	$new_dir = @$_POST['folder'];
				if ($new_dir && !file_exists($new_dir)) @mkdir($new_dir, 0755, true);
	    		if (file_exists($new_dir)) {
		    		if (@$_POST['old'] && $_POST['old'] != 'multi') @unlink($new_dir.'/'.$_POST['old']);
	    			$filter = new Zkernel_Filter_File_Uploadify(array(
		    			'directory' => $new_dir
		    		));
		    		$name = $filter->filter($name);
		    		$path = $new_dir.'/'.$name;
		    		$res = @move_uploaded_file($tmp_name, $path);
					if ($res) {
						$ok = 'u|'.$name;
						@chmod($path, 0755);
					}
					else $ret[] = 'uploadifyNocopy';
	    		}
	    		else $ret[] = 'uploadifyNofolder';
    		}
    	}
    	$ret = Zkernel_Form::translateErrors($ret);
    	$this->getResponse()->setBody($ok ? $ok : implode('|', $ret));
	}
}