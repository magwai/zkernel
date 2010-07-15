<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Model_Update extends Zkernel_Db_Table {
	function info() {
		return array(
			'id' => array(
				'COLUMN_NAME' => 'id'
			),
			'title' => array(
				'COLUMN_NAME' => 'title'
			)
		);
	}

	function fetchCount($where = null) {
		return 0;
	}

    function fetchNextId() {
    	return 0;
    }

    function fetchOne() {
    	return '';
    }

	function fetchControlList($where, $order, $count, $offset) {
		$ret = array();
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		if ($config['update']) {
			foreach ($config['update'] as $k => $v) {
				$data = $this->fetchControlCard($k);
				if (count($data)) $ret[] = array(
					'id' => $k,
					'title' => $data->title
				);
			}
		}
		return new Zkernel_View_Data($ret);
	}

	function fetchControlUpdate($name) {
		$ret = array();
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		if ($config['update'][$name]) {
			$data = $config['update'][$name];
			$client = new Zend_Http_Client($data['server']);
			$client->setParameterPost(array(
			    'name' => $name,
			    'password' => $data['password'],
			    'action' => 'list'
			));
			$response = $client->request('POST');
			$data = Zend_Json::decode($response->getBody());
			if ($data) $ret = $data;
		}
		return new Zkernel_View_Data($ret);
	}

	function fetchControlMd5($name) {
		$ret = '';
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		if ($config['update'][$name]) {
			$data = $config['update'][$name];
			$client = new Zend_Http_Client($data['server']);
			$client->setParameterPost(array(
			    'name' => $name,
			    'password' => $data['password'],
			    'action' => 'md5'
			));
			$response = $client->request('POST');
			$data = $response->getBody();
			if ($data) $ret = $data;
		}
		return $ret;
	}

	function fetchControlFile($name, $list) {
		$ret = '';
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		if ($config['update'][$name]) {
			$data = $config['update'][$name];
			$client = new Zend_Http_Client($data['server']);
			$client->setParameterPost(array(
			    'name' => $name,
			    'password' => $data['password'],
			    'action' => 'file',
				'list' => $list
			));
			$response = $client->request('POST');
			$data = $response->getBody();
			if ($data) $ret = $data;
		}
		return $ret;
	}

	function fetchControlCard($name) {
		$ret = array();
		$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOptions();
		if ($config['update'][$name]) {
			$data = $config['update'][$name];
			$client = new Zend_Http_Client($data['server']);
			$client->setParameterPost(array(
			    'name' => $name,
			    'password' => $data['password'],
			    'action' => 'info'
			));
			$response = $client->request('POST');
			$data = Zend_Json::decode($response->getBody());
			if ($data) $ret = $data;
		}
		return new Zkernel_View_Data($ret);
	}


	function getListFull($config) {
		$list = array();
		if ($config) {
			foreach ($config as $k => $v) {
				$this->readFolder(PUBLIC_PATH.'/'.$v['path'], $v['exclude'], $k, $list);
			}
		}
		return $list;
	}

	function getMd5Full($config) {
		$md5 = '';
		if ($config) {
			foreach ($config as $k => $v) $md5 .= $this->getMd5(PUBLIC_PATH.'/'.$v['path'], $v['exclude'], $k);
			$md5 = md5($md5);
		}
		return $md5;
	}

	function getMd5($path, $exclude = '', $key = '') {
		$list = array();
		$this->readFolder($path, $exclude, $key, $list);
		$md5 = '';
		if ($list) {
			ksort($list);
			$md5 = md5(implode('', $list));
		}
		return $md5;
	}

	function getFile($config, $list) {
		$ret = '';
		if ($list) {
			$fn1 = sys_get_temp_dir();
			$fn2 = 'zupdate'.time();
			$dir = $fn1.'/'.$fn2;
			foreach ($list as $el) {
				$p = explode(':', $el);
				$dn = dirname($dir.'/'.$p[0].'/'.$p[1]);
				@mkdir($dn, 0755, true);
				@copy($config['source'][$p[0]]['path'].'/'.$p[1], $dir.'/'.$p[0].'/'.$p[1]);
			}
			exec('cd '.$dir.'; zip -r9 "update.zip" *');
			$ret = file_get_contents($dir.'/update.zip');
			exec('rm -r '.$dir);
		}
		return $ret;
	}

	function recurseCopy($src, $dst) {
	    $dir = opendir($src);
	    @mkdir($dst);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            if ( is_dir($src . '/' . $file) ) {
	                $this->recurseCopy($src . '/' . $file,$dst . '/' . $file);
	            }
	            else {
	                copy($src . '/' . $file,$dst . '/' . $file);
	            }
	        }
	    }
	    closedir($dir);
	}

	function readFolder($basedir, $exclude, $k, &$list, $dir = '') {
		$basedir = realpath($basedir);
		if (!$dir) $dir = $basedir;
		$handle = opendir($dir);
		$ilist = array();
		while ($path = @readdir($handle)) {
			if ($path == '.' || $path == '..' || ($exclude && preg_match('/('.$exclude.')/si', str_replace($basedir.'/', '', $dir).'/'.$path, $fake))) continue;
			$ilist[] = $dir.'/'.$path;
		}
		@closedir($handle);
		if ($ilist) {
			foreach ($ilist as $el) {
				if (is_dir($el)) $this->readFolder($basedir, $exclude, $k, $list, $el);
				else $list[$k.':'.str_replace($basedir.'/', '', $el)] = md5(file_get_contents($el));
			}
		}
	}
}
