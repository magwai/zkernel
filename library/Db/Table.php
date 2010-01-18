<?php

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

	public function fetchCol($col = 'id', $where = null, $order = null) {
		if ($col instanceof Zend_Db_Select) $select = $col;
		else {
			$select = $this->select()->from($this, $col);
    		if ($where) $this->_where($select, $where);
    		if ($order) $select->order($order);
		}
    	return $this->getAdapter()->fetchCol($select);
    }

	public function fetchOne($col = 'id', $where = null) {
    	$select = $this->select()->from($this, $col);
    	if ($where) $this->_where($select, $where);
    	if ($order) $select->order($order);
    	return $this->getAdapter()->fetchOne($select);
    }

	public function fetchNextId() {
		$result = $this->getAdapter()->query('SHOW TABLE STATUS LIKE "'.$this->info('name').'"')->fetchAll();
		return @(int)$result[0]['Auto_increment'];
    }

}
