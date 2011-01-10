<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Merge {
	static $_content = '';
	static $_parsed = array();
	static $_written = array();
	static $_na = array();
	static $_exclude = array(
		/*'/Zend\/Acl/i',
		'/Zend\/Amf/i',
		'/Zend\/Auth/i',
		'/Zend\/Barcode/i',
		'/Zend\/Cache/i',
		'/Zend\/Captcha/i',
		'/Zend\/Cloud/i',
		'/Zend\/CodeGenerator/i',
		'/Zend\/Console/i',
		'/Zend\/Crypt/i',
		'/Zend\/Currency/i',
		'/Zend\/Date/i',
		'/Zend\/Dom/i',
		'/Zend\/Dojo/i',
		'/Zend\/Feed/i',
		'/Zend\/File/i',
		'/Zend\/Gdata/i',
		'/Zend\/Http/i',
		'/Zend\/InfoCard/i',
		'/Zend\/Locale/i',
		'/Zend\/Log/i',
		'/Zend\/Mail/i',
		'/Zend\/Markup/i',
		'/Zend\/Measure/i',
		'/Zend\/Memory/i',
		'/Zend\/Pdf/i',
		'/Zend\/Oauth/i',
		'/Zend\/OpenId/i',
		'/Zend\/ProgressBar/i',
		'/Zend\/Queue/i',
		'/Zend\/Rest/i',
		'/Zend\/Search/i',
		'/Zend\/Serializer/i',
		'/Zend\/Service/i',
		'/Zend\/Soap/i',
		'/Zend\/Tag/i',
		'/Zend\/Test/i',
		'/Zend\/Text/i',
		'/Zend\/Tool/i',
		'/Zend\/Translate/i',
		'/Zend\/Uri/i',
		'/Zend\/TimeSync/i',
		'/Zend\/XmlRpc/i',
		'/Zend\/Ldap/i',
		'/Zend\/Wildfire/i'*/
	);

	/*function library() {
		if (!self::get('library')) {
			self::set('library');
			return false;
		}
		return true;
	}*/

	function get_fn($hash) {
		$dir = DATA_PATH.'/merge';
		@mkdir($dir);
		@chmod($dir, 0777);
		$fn = realpath($dir).'/'.($hash == 'library' ? $hash : md5(var_export($hash, 1))).'.php';
		return $fn;
	}

	function get($hash) {
		//return false;
		$fn = self::get_fn($hash);
		if (file_exists($fn)) {
			require_once($fn);
			//return false;
			return true;
		}
		return false;
	}

	function set($hash) {
		self::$_content = '';
		self::$_parsed = array();
		self::$_written = array();
		set_time_limit(300);
		$i = array();
		if ($hash == 'library') {
			$data = array();
			self::go_deeper(APPLICATION_PATH.'/../library/Zend', $i);
			self::go_deeper(APPLICATION_PATH.'/../library/Zkernel', $i);
		}
		else $i = get_included_files();
		if ($i) {
			
			self::$_na = array(
				realpath(PUBLIC_PATH.'/index.php'),
				realpath(APPLICATION_PATH.'/../library/Zkernel/Merge.php')/*,
				realpath(APPLICATION_PATH.'/Bootstrap.php')*/
			);
			foreach ($i as $el) self::process_file($el);
		}
		$fn = self::get_fn($hash);
		file_put_contents($fn, "<?php\n".self::$_content);
		file_put_contents($fn, php_strip_whitespace($fn));
	}

	function process_file($tfn, $dir = null) {
		$fn = realpath($tfn);
		if (!$fn) {
			$fn = realpath(APPLICATION_PATH.'/../library/'.$tfn);
			if (!$fn && $dir) {
				$fn = realpath($dir.'/'.$tfn);
			}
		}
		$is_written = in_array($fn, self::$_written);
		$is_parsed = in_array($fn, self::$_parsed);
		if ($is_parsed && $is_written) return;
		self::$_parsed[] = $fn;
		if (!$fn || in_array($fn, self::$_na) || stripos($fn, '.php') === false || stripos($fn, '.phtml') !== false/* || strpos($fn, '/application/') !== false || strpos($fn, '\\application\\') !== false || stripos($fn, 'chain') !== false || stripos($fn, 'navigation') !== false || stripos($fn, 'controller\\plugin') !== false || stripos($fn, 'controller/plugin') !== false*/) return;
		$fn_l = str_replace('\\', '/', $fn);
		foreach (self::$_exclude as $el) if (preg_match($el, $fn_l)) return;
		$go_deeper = $is_parsed && !$is_written ? false : true;
		$c = file_get_contents($fn);
		preg_match_all('/((require|include)(\_once|))(\ |\()(\'|\")(.+?)(\'|\")(\)|)\;/i', $c, $res);
		if ($res) {
			foreach ($res[6] as $n => $el) {
				if ($go_deeper) self::process_file($el, dirname($fn));
				$c = str_replace($res[0][$n], '', $c);
			}
		}
		preg_match_all('/(extends|implements)\ (.+?)(\ |\{|\n|\t|\r|\,|\r\n)/si', $c, $res);
		if ($res) {
			foreach ($res[2] as $el) if ($go_deeper) self::process_file(str_replace('_', '/', $el).'.php', dirname($fn));
		}
		if (!in_array($fn, self::$_written)) {
			self::$_content .= preg_replace(array(
				'/\<\?php/si',
				'/\n\?\>/i'
			), array(
				'',
				"\n"
			), $c);
			self::$_written[] = $fn;
		}
	}

	function go_deeper($dir, &$data) {
		$handle = @opendir($dir);
		if ($handle) {
			while ($path = @readdir($handle)) {
				if ($path == '.' || $path == '..') continue;
				$fn = realpath($dir.'/'.$path);
				if (is_dir($fn)) self::go_deeper($fn, $data);
				else $data[] = $fn;
			}
			closedir($handle);
		}
	}
}
