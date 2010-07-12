<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_LinkSingle extends Zend_View_Helper_HeadLink  {
	function linkSingle() {
		$css = array();
		foreach ($this as $offset => $item) {
			if ($item->rel == 'stylesheet' && $item->type == 'text/css' && stripos($item->href, 'http://') === false) {
				$css[$item->media] = isset($css[$item->media]) ? $css[$item->media] : array();
				$css[$item->media][$offset] = $item;
				$this->offsetUnset($offset);
			}
		}
		if ($css) {
			$zpath = realpath(PUBLIC_PATH.'/zkernel');
			foreach ($css as $media => $els) {
				$c = '';
				$items = array();
				foreach ($els as $item) {
		            $i = PUBLIC_PATH.$item->href;
		            $items[] = $i;
		            $m .= filemtime($i);
		        }
				$md5 = substr(md5($m), 0, 5);
        		$nm = '/pc/css/'.$md5.'.css';
				if (!file_exists(PUBLIC_PATH.$nm)) {
					foreach ($items as $k => $el) {
						$dir_full = dirname($el);
						$dir = str_ireplace(PUBLIC_PATH, '', $dir_full);
						$str = file_get_contents($el);

						$matches = $files = array();
						preg_match_all('/url\((\'|\"|)(.*?)(\'|\"|)\)/si', $str, $res);
						if (@$res[2]) foreach ($res[2] as $k_1 => $el_1) {
							$matches[] = $res[0][$k_1];
							$files[] = $el_1;
						}
						if ($files) {
							foreach ($files as $k_1 => $el_1) {
								if (stripos($el_1, 'http://') !== false || substr($el_1, 0, 1) == '/') continue;
								$su = realpath($dir_full.'/'.$el_1);
								if (!$su) continue;
								$su = str_ireplace(array(
									PUBLIC_PATH,
									$zpath
								), array(
									'',
									'/zkernel'
								), $su);
								$str = str_ireplace($matches[$k_1], 'url('.$su.'?'.filemtime($dir_full.'/'.$el_1).')', $str);
							}
						}

						$matches = $files = array();
						preg_match_all('/src\=(\'|\"|)(.*?)(\'|\"|\,\))/si', $str, $res);
						if (@$res[2]) foreach ($res[2] as $k_1 => $el_1) {
							$matches[] = $res[0][$k_1];
							$files[] = $el_1;
						}
						if ($files) {
							foreach ($files as $k_1 => $el_1) {
								if (stripos($el_1, 'http://') !== false || substr($el_1, 0, 1) == '/') continue;
								$su = realpath($dir_full.'/'.$el_1);
								if (!$su) continue;
								$su = str_ireplace(array(
									PUBLIC_PATH,
									$zpath
								), array(
									'',
									'/zkernel'
								), $su);
								$str = str_ireplace($matches[$k_1], 'src="'.$su.'?'.filemtime($dir_full.'/'.$el_1).'"', $str);
							}
						}

						$c .= $str."\n";
					}

					$c = trim($c);
					if (!@file_exists(PUBLIC_PATH.'/pc/css')) {
						@mkdir(PUBLIC_PATH.'/pc/css', 0777, true);
						@chmod(PUBLIC_PATH.'/pc/css', 0777);
					}
					file_put_contents(PUBLIC_PATH.$nm, $this->view->minify($c, 'css'));
					@chmod(PUBLIC_PATH.$nm, 0777);
				}
				$this->appendStylesheet($nm, $media);
			}
		}
		return $this->headLink();
	}
}