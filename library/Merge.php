<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Merge {
	function get() {

	}

	function set() {
		$i = get_included_files();
		if ($i) {
			foreach ($i as $el) {



				//print_r($el);
			}
		}
	}



	function merge($dir, $include = null, $exclude = null) {
		$f_d = DATA_PATH.'/merge';
		$f_n = md5(var_export(array($dir), 1)).'.php';
		$f_f = $f_d.'/'.$f_n;
		//if (!file_exists($f_f)) {
			$dir = realpath(APPLICATION_PATH.'/../library/'.$dir);
			$str = '';
			$list = array();
			self::merge_recursive($dir, $str, $list, $include, $exclude);
			@mkdir($f_d, 0777, true);
			@chmod($f_d, 0777);
			@file_put_contents($f_f, "<?php\n".$str."\n?>");
			@chmod($f_f, 0777);
		//}
		require_once $f_f;
	}

	function merge_recursive($dir, &$str, &$list, $include, $exclude) {
		$handle = opendir($dir);
		while ($path = @readdir($handle)) {
			if ($path == '.' || $path == '..') continue;
			$fn = $dir.'/'.$path;
			if (is_dir($fn)) self::merge_recursive($fn, $str, $list, $include, $exclude);
			else if (!in_array($fn, $list) && substr($path, -4) == '.php') {
				$str .= "\n".self::clean($fn);
				$list[] = $fn;

			}
		}
		@closedir($handle);
	}

	function clean($fn) {
		$res = '';
		$c = file_get_contents($fn);


		//$c = preg_replace('/(require|include)([^\;]*)\;/si', '', $c);
		//$c = str_replace('  ', ' ', $c);
		$tokens = token_get_all($c);
		$was_io = $was_i = $was_ro = $was_r = $was_shit = 0;
		$was_require_once = 0;
		$was_shit_require_once = 0;
		$skip = array(T_COMMENT, T_OPEN_TAG, T_CLOSE_TAG, T_DOC_COMMENT/*, T_ML_COMMENT*/);
		foreach ($tokens as $token) {
			if (is_array($token)) {
				if (in_array($token[0], $skip)) continue;
				if ($token[0] == T_WHITESPACE) {
					$res .= "\n";//' ';
					continue;
				}
				if ($was_require_once) {
					if ($token[0] == T_CONSTANT_ENCAPSED_STRING) {
						$was_shit_require_once = 1;
					}
					else {
						$res .= 'require_once '.$token[1];
					}

					$was_require_once = 0;
					continue;
				}
				if ($token[0] == T_REQUIRE_ONCE) {
					$was_require_once = 1;
				}
				else {
					$res .= $token[1];
				}
			}
			else {
				if (!$was_shit_require_once) $res .= $token;
				$was_shit_require_once = 0;
			}





			//if (is_string($token)) $res .= '*'.$token.'*';
			//else {
				@list($id, $text) = $token;
				switch ($id) {
					case T_INCLUDE:
						$was_i = 1;
						break;
					case T_INCLUDE_ONCE:
						$was_io = 1;
						break;
					case T_REQUIRE:
						$was_r = 1;
						break;
					case T_REQUIRE_ONCE:
						$was_ro = 1;
						break;
					case T_COMMENT:
					//case T_ML_COMMENT:
					case T_DOC_COMMENT:
					case T_OPEN_TAG:
					case T_CLOSE_TAG:
						break;
					case T_WHITESPACE:
						$res .= ' ';
						break;
					default:
						if ($was_r || $was_ro || $was_i || $was_io) {
							if ($id == T_CONSTANT_ENCAPSED_STRING) $was_shit = 1;
							else if ($id == T_VARIABLE) {/*$res .= '*';*/$was_io = false;}
							else if ($text) $res .=
									($was_r ? 'require' : '').
									($was_ro ? 'require_once' : '').
									($was_i ? 'include' : '').
									($was_io ? 'include_once' : '').
									$text;

							$was_io = $was_i = $was_ro = $was_r = 0;
						}
						else {
							if ($was_shit) $was_shit = 0;
							//else $res .= $text;
						}
						break;
				}
			//}
		}
		//echo $res;
		//if (stripos($fn, 'Modules.php') !== false) {
		//	exit();
		//}
		//exit();
		//$res = preg_replace('/\nclass\ ([^\ ]+)\ /si', "\n".'if (!class_exists(\'$1\')) class $1 ', $res);
		return $res;
	}
}
