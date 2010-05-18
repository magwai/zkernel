<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Model_Generatormodel {
	function info() {
		return array(
			'id' => array(
				'COLUMN_NAME' => 'id'
			),
			'parent' => array(
				'COLUMN_NAME' => 'parent'
			),
			'parentid' => array(
				'COLUMN_NAME' => 'parentid'
			),
			'table' => array(
				'COLUMN_NAME' => 'table'
			),
			'table_create' => array(
				'COLUMN_NAME' => 'table_create'
			),
			'method' => array(
				'COLUMN_NAME' => 'method'
			),
			'multilang' => array(
				'COLUMN_NAME' => 'multilang'
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
		$dir = @$dir['default'];
		$model = file_exists($dir.'/../models/'.ucfirst($n).'.php');
		if ($model) $data[] = array(
			'id' => $n,
			'name' => ucfirst($n)
		);
		return new Zkernel_View_Data($data);
	}

	function fetchExist($id) {
		$m = $this->fetchControlList(array('`parentid` = ?' => $id));
		return count($m);
	}

	function fetchCount() {
		return 0;
	}

	public function insertControl($data) {
		$ok = true;

		$name = ucfirst($data['parentid']);

		$model = new Zend_CodeGenerator_Php_Class();
		$model->setName('Default_Model_'.$name);
		if ($data['parent']) $model->setExtendedClass($data['parent']);

		$prop = array();
		if ($data['table']) $prop[] = array(
			'name' => '_name',
			'visibility' => 'protected',
			'defaultValue' => trim($data['table']),
		);
		if ($data['multilang']) {
			$ml = explode(',', str_replace(array(' '), array(''), trim($data['multilang'])));
			$prop[] = array(
				'name' => '_multilang_field',
				'visibility' => 'protected',
				'defaultValue' => $ml,
			);
		}
		if ($prop) $model->setProperties($prop);

		if ($data['method']) {
			$met = array();
			foreach ($data['method'] as $el) {
				$mn = $mb = '';
				$mp = array();
				switch ($el) {
					case 'list':
						$mn = 'fetchList';
						$mb = 'return $this->fetchAll();';
						break;
					case 'list_join':
						$mn = 'fetchList';
						$mb =
'$m = new Default_Model_Temp();
$s = $this->getAdapter()->select()
	->from(array(\'i\' => $this->info(\'name\')))
	->join(array(\'m\' => $m->info(\'name\')), \'i.parentid = m.id\', array(
		\'temp\' => \'o.title\'
	))
	->group(\'i.id\')
	->order(\'i.orderid\');
return $this->fetchAll($s);';
						break;
					case 'card':
						$mn = 'fetchCard';
						$mp = array(
							array('name' => 'id')
						);
						$mb = 'return $this->fetchRow(array(\'`stitle` = ?\' => $id));';
						break;
					case 'card_join':
						$mn = 'fetchCard';
						$mp = array(
							array('name' => 'id')
						);
						$mb =
'$m = new Default_Model_Temp();
$s = $this->getAdapter()->select()
	->from(array(\'i\' => $this->info(\'name\')))
	->join(array(\'m\' => $m->info(\'name\')), \'i.parentid = m.id\', array(
		\'temp\' => \'o.title\'
	))
	->group(\'i.id\')
	->where(\'`stitle` = ?\', $id);
return $this->fetchRow($s);';
						break;
					case 'idtitle':
						$mn = 'fetchIdTitle';
						$mb = 'return $this->fetchPairs(\'id\', \'title\', null, \'orderid\');';
						break;
				}
				if ($mn) $met[] = array(
					'name' => $mn,
					'body' => $mb,
					'parameters' => $mp
				);
			}
			if ($met) $model->setMethods($met);
		}

		$c = '<?php'."\n\n".$model->generate();

		$ok = file_put_contents(APPLICATION_PATH.'/models/'.$name.'.php', $c);
		if ($ok) @chmod(APPLICATION_PATH.'/models/'.$name.'.php', 0777);

		if ($data['table'] && $data['table_create']) {
			$n = substr($data['table_create'], 3);
			$model = new Default_Model_Page();
			switch (substr($data['table_create'], 0, 3)) {
				case '_e_':
					$model->getAdapter()->query('CREATE TABLE `'.$data['table'].'` LIKE `'.$n.'`');
					break;
				case '_t_':
					$c = file_get_contents(APPLICATION_PATH.'/../library/Zkernel/Other/Template/Db/'.$n);
					if ($c) {
						$c = str_replace(array('%name%'), array($data['table']), $c);
						$model->getAdapter()->query($c);
					}
					break;
			}
		}

		return $ok;
	}

    public function fetchNextId() {
    	return 0;
    }
}
