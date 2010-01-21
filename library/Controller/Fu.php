<?php

/**
 * @zk_title   		Загрузка файлов
 * @zk_config		0
 * @zk_routable		0
 */
class Zkernel_Controller_Fu extends Zkernel_Controller_Action {
	function indexAction() {
		$this->_helper->layout()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$ret = 'Ошибка передачи';
    	$name = @$_FILES['Filedata']['name'];
    	$tmp_name = @$_FILES['Filedata']['tmp_name'];
    	if ($name && $tmp_name) {
    		$ext = strrpos($name, '.');
    		if ($ext !== false) {
    			$t = substr($name, $ext + 1);
    			$name = substr($name, 0, $ext);
    			$ext = $t;
    		}
    		$stitle = $this->_helper->util()->stitle($name, 20);
    		$fn = $stitle.($ext ? '.'.$ext : '');

    		$tmp_dir = explode('/', $tmp_name);
    		array_pop($tmp_dir);
    		$tmp_dir = implode('/', $tmp_dir).'/';

    		$path = $tmp_dir.'fu_'.$fn;
    		$res = @move_uploaded_file($tmp_name, $path);
    		$ret = $res ? '1|u|'.$path : 'Ошибка копирования';
    	}
    	$this->getResponse()->setBody($ret);
	}
}