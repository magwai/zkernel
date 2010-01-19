<?php

class Zkernel_View_Helper_FormDate extends Zend_View_Helper_FormText
{
    public function formDate($name, $value = null, $attribs = null)
    {
		if ($value) {
			$value = strtotime($value);
			$value = date('d.m.Y', $value);
		}
        return parent::formText($name, $value, $attribs);
    }
}
