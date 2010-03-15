<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zend_Form_Decorator_Uploadify
    extends Zend_Form_Decorator_File
{
    protected $_attribBlacklist = array('helper', 'placement', 'separator', 'value', 'path', 'url', 'required');

    public function render($content) {
        $separator = $this->getSeparator();
        $element 	= $this->getElement();
        $name      	= $element->getName();
        $attribs   	= $this->getAttribs();
        $view = $element->getView();
    	$markup[] 	= $view->formUploadify($name, $attribs);

        $markup = implode($separator, $markup);

        switch ($placement) {
            case self::PREPEND:
                return $markup . $separator . $content;
            case self::APPEND:
            default:
                return $content . $separator . $markup;
        }
    }
}
