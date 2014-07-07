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
				$cp = defined('CACHE_DIR') ? CACHE_DIR : 'pc';
		        $nm = '/'.$cp.'/js/'.$md5.'.js';
				if (!file_exists(PUBLIC_PATH.$nm)) {
					if (!@file_exists(PUBLIC_PATH.'/'.$cp.'/js')) {
						@mkdir(PUBLIC_PATH.'/'.$cp.'/js', 0777, true);
						@chmod(PUBLIC_PATH.'/'.$cp.'/js', 0777);
					}
					$c = $this->view->minify($c, 'js');
					file_put_contents(PUBLIC_PATH.$nm, $c);
					@chmod(PUBLIC_PATH.$nm, 0777);

					$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
					if (@$config['js']['static'] && function_exists('gzopen')) {
						$zp = gzopen(PUBLIC_PATH.$nm.'.gz', 'wb9');
						gzwrite($zp, $c);
						gzclose($zp);
						@chmod(PUBLIC_PATH.$nm.'.gz', 0777);
					}
				}
				$c = $type == 'file' ? '<script type="text/javascript" src="'.$nm.'"></script>' : $nm;
			}
			else $c = $type == 'script_clean' ? $c : '<script type="text/javascript">try { '.$c.' } catch (e) {}</script>';
        }
		return $c;
	}
}