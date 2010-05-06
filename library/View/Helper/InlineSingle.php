<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_InlineSingle extends Zend_View_Helper_InlineScript  {
	function inlineSingle($type = 'script') {
		$m = 0;
		$items = array();
        $this->getContainer()->ksort();

        foreach ($this as $item) {
            if (!$this->_isValid($item)) continue;
            $items[] = $item->source;
        }
        $c = trim(implode("\n", $items));
        if ($c) {
			if ($type == 'file' || $type == 'filename' ) {
				$md5 = substr(md5($c), 0, 5);
		        $nm = '/pc/js/'.$md5.'.js';
				if (!file_exists(PUBLIC_PATH.$nm)) {
					if (!@file_exists(PUBLIC_PATH.'/pc/js')) mkdir(PUBLIC_PATH.'/pc/js', 0777, true);
					file_put_contents(PUBLIC_PATH.$nm, $c);
					@chmod(PUBLIC_PATH.$nm, 0777);
				}
				$c = $type == 'file' ? '<script type="text/javascript" src="'.$nm.'"></script>' : $nm;
			}
			else $c = $type == 'script_clean' ? $c : '<script type="text/javascript">try { '.$c.' } catch (e) {}</script>';
        }
		return $c;
	}
}