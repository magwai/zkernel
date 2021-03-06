<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_FormUploadify3 extends Zend_View_Helper_FormFile {
    public function formUploadify3($name, $value = null, $attribs = null) {
    	$url = @$attribs['url'];
    	$required = @(int)$attribs['required'];
    	$url = $attribs['url'];
    	$required = $attribs['required'];
    	unset($attribs['url']);
    	unset($attribs['destination']);
    	unset($attribs['required']);
		$res =	'<div class="c_uploadify">'.
					parent::formFile($name, $value, $attribs).
    				'<input type="hidden" name="'.$name.'" value="'.$value.'" />'.
				'</div>';
		$value = explode('*', $value);
		/*foreach ($value as $el) {
			$this->view->inlineScript('script', 'zuf.add("'.$name.'", "'.$el.'", "'.$url.'", '.(int)$required.');');
		}*/
        return $res;
    }
}
