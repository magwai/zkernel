<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_FormUploadify extends Zend_View_Helper_FormFile
{
    public function formUploadify($name, $value = null, $attribs = null)
    {
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
		foreach ($value as $el) {
			Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval('zuf.add("'.$name.'", "'.$el.'", "'.$url.'", '.(int)$required.');');
		}


		/*$res =	'<div class="c_uploadify">'.
					'<em'.($value && $url ? '' : ' style="display:none;"').' class="c_uploadify_em"><span><a href="'.$url.'/'.$value.'" target="_blank">'.$value.'</a></span>'.($required ? '' : '<a'.($value && $url ? '' : ' style="display:none;"').' title="Удалить" href="#" onclick="return false"><img src="/zkernel/ctl/uploadify/cancel.png" alt="" /></a>').'</em>'.
					parent::formFile($name, $value, $attribs).
    				'<input type="hidden" name="'.$name.'" value="'.$value.'" />'.
				'</div>';*/
        return $res;
    }
}
