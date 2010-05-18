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
			'zk_title' => array(
				'COLUMN_NAME' => 'zk_title'
			),
			'parent' => array(
				'COLUMN_NAME' => 'parent'
			),
			'action' => array(
				'COLUMN_NAME' => 'action'
			),
			'zk_routable' => array(
				'COLUMN_NAME' => 'zk_routable'
			),
			'zk_config' => array(
				'COLUMN_NAME' => 'zk_config'
			),
			'zk_routes' => array(
				'COLUMN_NAME' => 'zk_routes'
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
					'zk_title' => $nn/*,
					'model' => (int)$model*/
				);
			}
		}
		@closedir($handle);
		return new Zkernel_View_Data($data);
	}

	function fetchCount() {
		return 0;
	}

    public function insertControl($data) {
		$ok = true;

		$name = ucfirst(strtolower($data['name']));

		$controller = new Zend_CodeGenerator_Php_Class();
		$controller->setName($name.'Controller');
		if ($data['parent']) $controller->setExtendedClass($data['parent']);

		if ($data['action']) {
			$ml = explode(',', str_replace(array(' '), array(''), trim($data['action'])));
			$act = array();
			foreach ($ml as $el) {
				$el = strtolower($el);
				$act[] = array(
					'name' => $el.'Action'
				);
			}
			if ($act) $controller->setMethods($act);
		}

		$doc['tags'] = array();
		if ($data['zk_title']) $doc['tags'][] = array(
			'name' => 'zk_title',
			'description' => $data['zk_title']
		);
		if (!$data['zk_routable']) $doc['tags'][] = array(
			'name' => 'zk_routable',
			'description' => 0
		);
		if (!$data['zk_config']) $doc['tags'][] = array(
			'name' => 'zk_config',
			'description' => 0
		);
		if ($data['zk_routes']) $doc['tags'][] = array(
			'name' => 'zk_routes',
			'description' => $data['zk_routes']
		);
		if ($doc['tags']) $controller->setDocblock(new Zend_CodeGenerator_Php_Docblock($doc));

		$c = '<?php'."\n\n".$controller->generate();

		$ok = file_put_contents(APPLICATION_PATH.'/controllers/'.$name.'Controller.php', $c);
		if ($ok) @chmod(APPLICATION_PATH.'/controllers/'.$name.'Controller.php', 0777);

		return $ok;
    }

    public function fetchNextId() {
    	return 0;
    }
}
