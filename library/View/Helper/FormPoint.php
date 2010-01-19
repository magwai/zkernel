<?php

class Zkernel_View_Helper_FormPoint extends Zend_View_Helper_FormHidden
{
    public function formPoint($name, $value = null, $attribs = null)
    {
    	$xhtml = parent::formHidden($name, $value, $attribs);
		$xhtml .= '<div class="c_point" id="point_'.$name.'"></div>';
        return $xhtml;
    }
}
