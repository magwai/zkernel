<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_HeadSingle extends Zend_View_Helper_HeadScript  {
	function headSingle($name = null) {
		$m = 0;
		$aitems = $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->_isValid($item)) continue;
        	if (stripos($item->attributes['src'], 'maps.google.com') !== false || stripos($item->attributes['src'], 'api-maps.yandex.ru') !== false || preg_match('/^http\:\/\//i', $item->attributes['src'])) {
            	$aitems[] = $item->attributes['src'];
            	continue;
            }
            $i = PUBLIC_PATH.$item->attributes['src'];

            $items[] = $i;
            $m .= filemtime($i);
        }
        $md5 = $name == null ? substr(md5($m), 0, 5) : $name;
        $cp = defined('CACHE_DIR') ? CACHE_DIR : 'pc';
        $nm = '/'.$cp.'/js/'.$md5.'.js';
        $mod = false;
        $ex = file_exists(PUBLIC_PATH.$nm);
       	if ($ex) {
        	$str = file_get_contents(PUBLIC_PATH.$nm);
        	preg_match('/\/\*\ hash\:\ ([^\ ]+)\ \*\//si', $str, $res);
        	if ($res[1] != md5($m)) $mod = true;
        }
        else $mod = true;
        $c = '';
		if ($mod) {
			$include = $jquery = false;
			$inc = array();
			foreach ($items as $el) {
				$inc[str_replace(PUBLIC_PATH, '', $el)] = true;
				if (strpos($el, 'jquery.js')) $jquery = true;
				if (strpos($el, 'jquery.include.js')) $include = true;
				$c .= file_get_contents($el)."\n";
			}
			$c = trim($c);
			if ($include/* && $jquery*/ && $inc) $c .= 'jQuery.includedScripts = '.Zend_Json::encode($inc);
			if (!@file_exists(PUBLIC_PATH.'/'.$cp.'/js')) {
				@mkdir(PUBLIC_PATH.'/'.$cp.'/js', 0777, true);
				@chmod(PUBLIC_PATH.'/'.$cp.'/js', 0777);
			}
			$c = "/* hash: ".md5($m)." */\n".$this->view->minify($c, 'js');
			file_put_contents(PUBLIC_PATH.$nm, $c);
			@chmod(PUBLIC_PATH.$nm, 0777);

			if (function_exists('gzopen')) {
				$zp = gzopen(PUBLIC_PATH.$nm.'.gz', 'wb9');
				gzwrite($zp, $c);
				gzclose($zp);
			}

		}
		$this->headScript('file', $nm, 'set');
		if ($aitems) foreach ($aitems as $el) $this->headScript('file', $el);
		return $this->headScript();
	}
}