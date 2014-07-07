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
    	if (substr($res, 0, 12) == '/* minified_') return $res;

		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		if (!@$config[$type]['compress']) return $res;

		$compressor = $type == 'js' ? 'gcc' : 'yui';
		$host = @$config['util']['host'];
		$data = urlencode($res);
		$context = stream_context_create(array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: multipart/form-data'."\r\n".'Content-Length: '.strlen($data)."\r\n",
				'content' => $data
			)
		));
		$result = file_get_contents($host.'/x/minify/type/'.$type.'/compressor/'.$compressor, false, $context);
		if ($result) {
			$result = json_decode($result, true);
			$res = "/* minified_".$compressor." */\n".$result['data'];
		}
		return $res;

		$compressed = false;
		$is_js_java = in_array('gcc', @$config[$type]['compressor']);
		$is_css_java = in_array('yui', @$config[$type]['compressor']);
		if ($is_js_java || $is_css_java) {
			$process = @popen('java -client -Xmx64m 2>&1', 'r');
			$ret = false;
			if (is_resource($process)) {
				$read = fread($process, 2096);
				pclose($process);
				if ($read) $ret = true;
			}
			if ($ret) {
				$desc = array(
				   0 => array("pipe", "r"),
				   1 => array("pipe", "w")
				);
				if ($type == 'js' && $is_js_java) {
					$cp = defined('CACHE_DIR') ? CACHE_DIR : 'pc';
					$fn = PUBLIC_PATH.'/'.$cp.'/js/'.md5(microtime()).'.js';
					file_put_contents($fn, $res);
					$process = @proc_open('java -client -Xmx64m -jar '.realpath(str_replace('\\', '/', realpath(APPLICATION_PATH.'/../library/Zkernel/Other/Jar/compiler.jar'))).' --warning_level=QUIET --js='.$fn.' 2>&1', $desc, $pipes);
					if (is_resource($process)) {
						fclose($pipes[0]);
						$res_1 = stream_get_contents($pipes[1]);
						fclose($pipes[1]);
						if ($res_1) {
							$compressed = true;
							$res = "/* minified_gcc */\n".$res_1;
						}
						proc_close($process);
					}
					unlink($fn);
				}
				else if ($is_css_java) {
					$process = @proc_open('java -client -Xmx64m -jar "'.realpath(str_replace('\\', '/', realpath(APPLICATION_PATH.'/../library/Zkernel/Other/Jar/yuicompressor.jar'))).'" --charset utf-8 --type '.$type, $desc, $pipes);
					if (is_resource($process)) {
						fwrite($pipes[0], $res);
						fclose($pipes[0]);
						$res_1 = stream_get_contents($pipes[1]);
						fclose($pipes[1]);
						if ($res_1) {
							$compressed = true;
							$res = "/* minified_yuicompressor */\n".$res_1;
						}
						proc_close($process);
					}
				}
			}
		}

		if (!$compressed) {
			if ($type == 'css' && in_array('cssmin', @$config['css']['compressor'])) {
				require_once 'Zkernel/Other/Lib/cssmin/cssmin.php';
				$res = "/* minified_cssmin */\n".CssMin::minify($res);
			}
			else if ($type == 'js' && in_array('jsmin', @$config['js']['compressor'])) {
				require_once 'Zkernel/Other/Lib/JSMin/JSMin.php';
				$res = "/* minified_jsmin */\n".JSMin::minify($res);
			}
		}
		return $res;
    }
}
