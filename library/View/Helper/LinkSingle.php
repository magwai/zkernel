<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_LinkSingle extends Zend_View_Helper_HeadLink  {
	function linkSingle($name = null, $mtime = true) {
		$css = array();
		$i = (array)$this->getIterator();
		foreach ($i as $offset => $item) {
			if ($item->rel == 'stylesheet' && $item->type == 'text/css' && stripos($item->href, 'http://') === false) {
				$css[$item->media.'_'.$item->conditionalStylesheet] = isset($css[$item->media.'_'.$item->conditionalStylesheet]) ? $css[$item->media.'_'.$item->conditionalStylesheet] : array();
				$css[$item->media.'_'.$item->conditionalStylesheet][$offset] = $item;
				$this->offsetUnset($offset);
			}
		}
		if ($css) {
			$zp = defined('ZKERNEL_DIR') ? ZKERNEL_DIR : 'zkernel';
			$zpath = defined('ZKERNEL_PATH') ? ZKERNEL_PATH : realpath(PUBLIC_PATH.'/'.$zp);
			$cp = defined('CACHE_DIR') ? CACHE_DIR : 'pc';
			$m = '';
			foreach ($css as $media => $els) {
				$c = '';
				$items = array();
				foreach ($els as $item) {
		            $i = PUBLIC_PATH.$item->href;
		            $items[] = $i;
		            $m .= filesize($i).filemtime($i);
		        }
				$md5 = $name == null ? substr(md5($m), 0, 5) : $name;
        		$nm = '/'.$cp.'/css/'.$md5.'.css';
        		$mod = false;
		        $ex = file_exists(PUBLIC_PATH.$nm);
		       	if ($ex) {
		        	$str = file_get_contents(PUBLIC_PATH.$nm);
		        	preg_match('/\/\*\ hash\:\ ([^\ ]+)\ \*\//si', $str, $res);
		        	if ($res[1] != md5($m)) $mod = true;
		        }
		        else $mod = true;
				if ($mod) {
					foreach ($items as $k => $el) {
						$dir_full = dirname($el);
						$dir = str_ireplace(PUBLIC_PATH, '', $dir_full);
						$str = $this->preprocessUtil(file_get_contents($el), $el);

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
									$zpath,
									'\\'
								), array(
									'',
									'/'.$zp,
									'/'
								), $su);
								$str = str_ireplace($matches[$k_1], 'url('.Zkernel_Common::gen_static_url($su).($mtime ? '?'.filemtime($dir_full.'/'.$el_1) : '').')', $str);
							}
						}

						$matches = $files = array();
						preg_match_all('/src\=(\'|\"|)(.*?)(\'|\"|\,|\))/si', $str, $res);

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
									$zpath,
									'\\'
								), array(
									'',
									'/zkernel',
									'/'
								), $su);
								$str = str_ireplace($matches[$k_1], 'src="'.Zkernel_Common::gen_static_url($su).($mtime ? '?'.filemtime($dir_full.'/'.$el_1) : '').'"', $str);
							}
						}

						$c .= $str."\n";
					}
					$c = trim($c);
					if (!@file_exists(PUBLIC_PATH.'/'.$cp.'/css')) {
						@mkdir(PUBLIC_PATH.'/'.$cp.'/css', 0777, true);
						@chmod(PUBLIC_PATH.'/'.$cp.'/css', 0777);
					}
					$c = "/* hash: ".md5($m)." */\n".$this->view->minify($c, 'css');
					file_put_contents(PUBLIC_PATH.$nm, $c);
					@chmod(PUBLIC_PATH.$nm, 0777);

					$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
					if (@$config['css']['static'] && function_exists('gzopen')) {
						$zp1 = gzopen(PUBLIC_PATH.$nm.'.gz', 'wb9');
						gzwrite($zp1, $c);
						gzclose($zp1);
						@chmod(PUBLIC_PATH.$nm.'.gz', 0777);
					}
				}
				$media = explode('_', $media);
				$this->appendStylesheet($nm, $media[0], $media[1]);
			}
		}
		return $this->headLink();
	}

	public function preprocessUtil($content, $file) {
		if (substr($file, -5) == '.scss') {
			$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
			$cp = defined('CACHE_DIR') ? CACHE_DIR : 'pc';
			$host = @$config['util']['host'];
			$path = PUBLIC_PATH.'/img';
			$d = array(
				basename($file) => md5(file_get_contents($file))
			);
			if (!function_exists('recursive_scss')) {
				function recursive_scss($path, $dir, &$d) {
					$cur = $path.($dir ? '/'.$dir : '');
					foreach (scandir($cur) as $fn) {
						if ($fn == '.' || $fn == '..') continue;
						if (is_dir($cur.'/'.$fn) && !in_array($fn, array('font'))) {
							recursive_scss($path, $dir.($dir ? '/' : '').$fn, $d);
						}
						else if (preg_match('/\.('.($dir == 'sprites' ? 'png|' : '').'scss)/i', $fn)) {
							$d[$dir.($dir ? '/' : '').$fn] = md5(file_get_contents($cur.'/'.$fn));
						}
					}

				}
			}
			recursive_scss($path, '', $d);
			if ($d) {
				$res = file_get_contents($host.'/x/scss/ch/get/host/'.$_SERVER['HTTP_HOST'].'/file/'.basename($file));
				if ($res) {
					if (!class_exists('Zip')) require 'Zkernel/Other/Lib/ekernel/lib/Zip.php';
					$zip = new Zip();
					$res = json_decode($res, true);
					$cnt = 0;
					foreach ($d as $k => $v) {
						if ($v != @$res[$k]) {
							$zip->addFile(file_get_contents($path.'/'.$k), $k);
							$cnt++;
						}
					}
					if ($cnt) {
						$data = urlencode($zip->getZipData());
						$context = stream_context_create(array(
							'http' => array(
								'method' => 'POST',
								'header' => 'Content-Type: multipart/form-data'."\r\n".'Content-Length: '.strlen($data)."\r\n",
								'content' => $data
							)
						));
						$res = file_get_contents($host.'/x/scss/ch/set/host/'.$_SERVER['HTTP_HOST'].'/file/'.basename($file), false, $context);
						if ($res) {
							$res = json_decode($res, true);
							file_put_contents(PUBLIC_PATH.'/'.$cp.'/css/temp.zip', urldecode($res['data']));
							require 'Zkernel/Other/Lib/ekernel/lib/Unzip.php';
							$zip = new Unzip();
							$zip->extract(PUBLIC_PATH.'/'.$cp.'/css/temp.zip', PUBLIC_PATH.'/'.$cp.'/css');
							unlink(PUBLIC_PATH.'/'.$cp.'/css/temp.zip');
							$nfn = str_replace('.scss', '.css', basename($file));
							$content = @file_get_contents(PUBLIC_PATH.'/'.$cp.'/css/'.$nfn);
							unlink(PUBLIC_PATH.'/'.$cp.'/css/'.$nfn);
						}
					}
				}
			}

		}
		return $content;
	}

	public function preprocess($content, $file) {
		if (substr($file, -5) == '.scss') {
			$dir = PUBLIC_PATH.'/'.$cp.'/css/'.microtime(true);
			$dir_file = dirname($file);
			exec('mkdir "'.$dir.'" ; cd "'.$dir.'" ; compass create; chmod 777 "'.$dir.'/sass" ; mkdir "'.$dir.'/'.$cp.'" ; ln -s "'.PUBLIC_PATH.'/img" "'.$dir.'/'.$cp.'/css"');
			file_put_contents($dir.'/sass/style.scss', $content);
			file_put_contents($dir.'/config.rb', "line_comments = false\nimages_dir = \"".$cp."/css\"\nfonts_dir = \"img\"\nadditional_import_paths = [\"".$dir_file."\", \"".PUBLIC_PATH."/zkernel/ctl/ekernel/img\"]", FILE_APPEND);
			exec('cd "'.$dir.'" ; compass compile');
			exec('cd "'.$dir.'/'.$cp.'/css" ; cp sprites-* "'.PUBLIC_PATH.'/'.$cp.'/css" ; rm sprites-*; chmod 777 '.PUBLIC_PATH.'/'.$cp.'/css/*');
			$content = @file_get_contents($dir.'/stylesheets/style.css');
			exec('rm -R "'.$dir.'"');
		}
		return $content;
	}
}