<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Minify extends Zend_View_Helper_Abstract {
    public function minify($res, $type = 'js') {
    	//return $res;
    	if (substr($res, 0, 12) == '/* minified_') return $res;
    	$process = popen('java -client -Xmx64m', 'r');
		$ret = false;
		if (is_resource($process)) {
			$read = fread($process, 1024);
			pclose($process);
			if ($read) $ret = true;
		}
		if ($ret) {
			$desc = array(
			   0 => array("pipe", "r"),
			   1 => array("pipe", "w")
			);
			$process = proc_open('java -client -Xmx64m -jar "'.realpath(APPLICATION_PATH.'/../library/Zkernel/Other/Jar/yuicompressor.jar').'" --charset utf-8 --type '.$type, $desc, $pipes);
			if (is_resource($process)) {
				fwrite($pipes[0], $res);
				fclose($pipes[0]);
				$res_1 = stream_get_contents($pipes[1]);
				fclose($pipes[1]);
				if ($res_1) {
					$res = "/* minified_yuicompressor */\n".$res_1;
				}
				proc_close($process);
			}
		}
		else if ($type == 'js') {
			require_once 'Zkernel/Other/Lib/JSMin/JSMin.php';
			$res = "/* minified_jsmin */\n".JSMin::minify($res);
		}
		return $res;
    }
}
