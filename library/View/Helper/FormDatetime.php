<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_FormDatetime extends Zend_View_Helper_FormText {
    public function formDatetime($name, $value = null, $attribs = null) {
		if ($value) {
			$value = strtotime($value);
			$value = date((@$attribs['showSecond'])?'d.m.Y H:i:s':'d.m.Y H:i', $value);
		}
        return parent::formText($name, $value, $attribs);
    }
}
