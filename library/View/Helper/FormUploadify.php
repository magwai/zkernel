<?php

class Zkernel_View_Helper_FormUploadify extends Zend_View_Helper_FormFile
{
    public function formUploadify($name, $value = null, $attribs = null)
    {
    	$url = @$attribs['url'];
    	$required = @(int)$attribs['required'];
    	unset($attribs['url']);
    	unset($attribs['destination']);
    	unset($attribs['required']);
		$res =	'<div class="c_uploadify">'.
					'<em'.($value && $url ? '' : ' style="display:none;"').' class="c_uploadify_em"><span><a href="'.$url.'/'.$value.'" target="_blank">'.$value.'</a></span>'.($required ? '' : '<a'.($value && $url ? '' : ' style="display:none;"').' title="Удалить" href="#" onclick="return false"><img src="/zkernel/ctl/uploadify/cancel.png" alt="" /></a>').'</em>'.
					parent::formFile($name, $value, $attribs).
    				'<input type="hidden" name="'.$name.'" value="'.$value.'" />'.
				'</div>';
        return $res;
    }
}
