<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Table extends Zend_Db_Table_Abstract
{
	public function fetchTree($key = 'id', $value = 'title', $where = null, $order = null, $parentid = 0, $parentid_field = 'parentid') {
    	$data = array();
		$select = $this->select()
    		->from($this, array($key, $value))
    		->where('`'.$parentid_field.'` = ?', $parentid);
    	if ($where) $this->_where($select, $where);
    	if ($order) $select->order($order);
    	$result = $this->getAdapter()->fetchPairs($select);
    	if ($result) {
    		foreach ($result as $k => $v) {
    			$inner = $this->fetchTree($key, $value, $where, $order, $k);
    			if ($inner) $data[$v] = $inner;
    			else $data[$k] = $v;
    		}
    	}
    	return $data;
    }

	public function fetchCount($where = null) {
		$select = $this->select()->from($this, 'COUNT(*)');
	    if ($where) $this->_where($select, $where);
		return $this->getAdapter()->fetchOne($select);
    }

    public function fetchPairs($key = 'id', $value = 'title', $where = null, $order = null) {
    	$select = $this->select()->from($this, array($key, $value));
    	if ($where) $this->_where($select, $where);
    	if ($order) $select->order($order);
    	return $this->getAdapter()->fetchPairs($select);
    }

	public function fetchCol($col = 'id', $where = null, $order = null, $count = null, $offset = 0) {
		if ($col instanceof Zend_Db_Select) $select = $col;
		else {
			$select = $this->select()->from($this, $col);
    		if ($where) $this->_where($select, $where);
    		if ($count) $select->limit($count, $offset);
    		if ($order) $select->order($order);
		}
    	return $this->getAdapter()->fetchCol($select);
    }

	public function fetchOne($col = 'id', $where = null, $order = null) {
    	$select = $this->select()->from($this, $col);
    	if ($where) $this->_where($select, $where);
    	if ($order) $select->order($order);
    	return $this->getAdapter()->fetchOne($select);
    }

	public function fetchNextId() {
		$result = $this->getAdapter()->query('SHOW TABLE STATUS LIKE "'.$this->info('name').'"')->fetchAll();
		return @(int)$result[0]['Auto_increment'];
    }

	public function fetchMax($col = 'id', $where = null) {
		$select = $this->select()->from($this, 'MAX(`'.$col.'`)');
    	if ($where) $this->_where($select, $where);
    	return $this->getAdapter()->fetchOne($select);
    }

    public function fetchControlList($where, $order, $count, $offset) {
    	return $this->fetchAll(
	    	$where,
	    	$order,
	    	$count,
	    	$offset
	    );
    }

	public function fetchControlCard($where) {
    	return $this->fetchRow(
	    	$where
	    );
    }

    public function insertControl($data) {
    	$this->overrideMultilang($data);
    	return $this->insert($data);
    }

    public function updateControl($data, $where) {
	    $this->overrideMultilang($data);
    	return $this->update($data, $where);
    }

	public function deleteControl($where) {
    	return $this->delete($where);
    }

    public function overrideMultilang(&$data) {
    	if ($this->_multilang_field) {
			$reg = Zend_Registry::isRegistered('Zkernel_Multilang') ? Zend_Registry::get('Zkernel_Multilang') : '';
			if ($reg) {
				foreach ($data as $k => $v) {
					if (in_array($k, $this->_multilang_field)) {
						$data['ml_'.$k.'_'.$reg->id] = $v;
						unset($data[$k]);
					}
				}
			}
		}
    }

	public function init() {
		parent::init();
		if (isset($this->_multilang_field)) {
			$reg = Zend_Registry::isRegistered('Zkernel_Multilang') ? Zend_Registry::get('Zkernel_Multilang') : '';
			if ($reg) {
				$cols = $this->info('metadata');
				$m = new Default_Model_Lang();
				$ids = implode('|', $m->fetchIds());
				$ml = implode('|', $this->_multilang_field);
				foreach ($this->_multilang_field as $k => $el) {
					if (!array_key_exists('ml_'.$el.'_'.$reg->id, $cols)) $this->getAdapter()->query('ALTER TABLE `'.$this->_name.'` ADD `ml_'.$el.'_'.$reg->id.'` '.$cols[$el]['DATA_TYPE'].($cols[$el]['LENGTH'] ? '('.$cols[$el]['LENGTH'].')' : '').($cols[$el]['DEFAULT'] ? ' DEFAULT '.$cols[$el]['DEFAULT'] : ''));
				}
				foreach ($cols as $k => $el) {
					if (preg_match('/^ml\_'.$el.'\_(\d+)$/i', $k) && !preg_match('/^ml\_('.implode('|', $this->_multilang_field).')\_('.implode('|', $ids).')+$/i')) $this->getAdapter()->query('ALTER TABLE `'.$this->_name.'` DROP `'.$k.'`');
				}
				$cache = $this->getMetadataCache();
				if ($cache) $cache->clean();
			}
		}
    }
}
