<?php

class Zkernel_View_Helper_FormGmap extends Zend_View_Helper_FormHidden
{
    public function formGmap($name, $value = null, $attribs = null)
    {
    	$xhtml = parent::formHidden($name, $value, $attribs);
		$xhtml .= '<div class="c_gmap" id="gmap_'.$name.'"></div>';
        return $xhtml;
    }
}
