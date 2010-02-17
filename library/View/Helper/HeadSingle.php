<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_HeadSingle extends Zend_View_Helper_HeadScript  {
	function headSingle() {
		$m = 0;
		$items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->_isValid($item)) continue;
            $i = PUBLIC_PATH.$item->attributes['src'];
            $items[] = $i;
            $m .= filemtime($i);
        }
        $md5 = substr(md5($m), 0, 5);
        $nm = '/pc/js/'.$md5.'.js';
		if (!file_exists(PUBLIC_PATH.$nm)) {
			$include = $jquery = false;
			$inc = array();
			foreach ($items as $el) {
				$inc[str_replace(PUBLIC_PATH, '', $el)] = true;
				if (strpos($el, 'jquery.js')) $jquery = true;
				if (strpos($el, 'jquery.include.js')) $include = true;
				$c .= file_get_contents($el)."\n";
			}
			$c = trim($c);
			if ($include &&  $jquery && $inc) $c .= 'jQuery.includedScripts = '.Zend_Json::encode($inc);
			if (!@file_exists(PUBLIC_PATH.'/pc/js')) mkdir(PUBLIC_PATH.'/pc/js', 0755, true);
			file_put_contents(PUBLIC_PATH.$nm, $c);
			@chmod(PUBLIC_PATH.$nm, 0755);
		}
		$this->headScript('file', $nm, 'set');
		return $this->headScript();
	}
}