<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Model_Generator {
	function info() {
		return array(
			'id' => array(
				'COLUMN_NAME' => 'id'
			),
			'name' => array(
				'COLUMN_NAME' => 'name'
			),
			'title' => array(
				'COLUMN_NAME' => 'title'
			),
			'model' => array(
				'COLUMN_NAME' => 'model'
			)
		);
	}

	function fetchControlList() {
		$data = array();
		$dir = Zend_Controller_Front::getInstance()->getControllerDirectory();
		$dir = @$dir['default'];
		$handle = @opendir($dir);
		while ($path = @readdir($handle)) {
			if (is_file($dir.'/'.$path)) {
				$n = $nn = strtolower(str_ireplace('Controller.php', '', $path));
				$c = ucfirst($n).'Controller';
				if (!class_exists($c)) include $dir.'/'.$path;
				$db = Zkernel_Common::getDocblock($c);
				if (isset($db['zk_title'])) $nn = $db['zk_title'];
				$model = file_exists($dir.'/../models/'.ucfirst($n).'.php');

				$data[] = array(
					'id' => $n,
					'name' => $n,
					'title' => $nn,
					'model' => (int)$model
				);
			}
		}
		@closedir($handle);
		return new Zkernel_View_Data($data);
	}

	function fetchCount() {
		return 0;
	}
}
