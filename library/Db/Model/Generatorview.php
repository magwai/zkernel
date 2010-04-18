<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Model_Generatorview {
	function info() {
		return array(
			'id' => array(
				'COLUMN_NAME' => 'id'
			),
			'parentid' => array(
				'COLUMN_NAME' => 'parentid'
			),
			'template' => array(
				'COLUMN_NAME' => 'template'
			),
			'name' => array(
				'COLUMN_NAME' => 'name'
			)
		);
	}

	function fetchControlList($where) {
		$data = array();
		$n = $where['`parentid` = ?'];
		$dir = Zend_Controller_Front::getInstance()->getControllerDirectory();
		$dir = @$dir['default'].'/../views/scripts/'.$n;
		$handle = @opendir($dir);
		while ($path = @readdir($handle)) {
			if (is_file($dir.'/'.$path)) {
				$n = strtolower(str_ireplace('.phtml', '', $path));
				$data[] = array(
					'id' => $n,
					'name' => $n
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
