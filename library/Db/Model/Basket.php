<?php

class Zkernel_Db_Model_Basket {
	protected $_storage = 'basket';
	protected $_model = null;
	protected $_save = true;
	protected $_data = array();

	function getQuant($id = null) {
		$p = 0;
		$d = $this->get($id);
		if ($d) {
			if ($id === null) {
				foreach ($d as $el) $p += $el->quant;
			}
			else $p = $d->quant;
		}
		return $p;
	}

	function getPrice($id = null) {
		$p = 0;
		$d = $this->get($id);
		if ($d) {
			if ($id === null) {
				foreach ($d as $el) {
					if ($el->discount) $el->price -= $el->price * ($el->discount / 100);
					$p += $el->price * $el->quant;
				}
			}
			else {
				if ($d->discount) $d->price -= $d->price * ($d->discount / 100);
				$p = $d->price * $d->quant;
			}

		}
		return $p;
	}

	function clear() {
		$this->_data = array();
		$this->save();
	}

	function delete($id) {
		$ok = isset($this->_data[$id]);
		if ($ok && $this->_model) $ok = $this->_model->fetchCount(array('`id` = ?' => $id));
    	if ($ok) {
    		unset($this->_data[$id]);
	    	$this->save();
	    	return true;
    	}
    	return false;
	}

    function add($id, $quant) {
    	$ok = true;
    	if ($this->_model) $ok = $this->_model->fetchCount(array('`id` = ?' => $id));
    	if ($ok) {
    		if (isset($this->_data[$id])) $this->_data[$id] += $quant;
	    	else $this->_data[$id] = $quant;
	    	$this->save();
	    	return true;
    	}
		return false;
    }

    function set($id, $quant) {
    	$ok = true;
    	if ($this->_model) $ok = $this->_model->fetchCount(array('`id` = ?' => $id));
    	if ($ok) {
    		$this->_data[$id] = $quant;
	    	$this->save();
	    	return true;
    	}
		return false;
    }

    function get($id = null) {
    	if ($id === null) {
    		$ret = array();
    		if ($this->_data) foreach ($this->_data as $k => $v) {
    			if ($this->_model) {
    				$row = $this->_model->fetchRow(array('`id` = ?' => $k));
    				if ($row) {
    					$row = new Zkernel_View_Data($row);
	    				$row->quant = $v;
	    				$ret[$k] = $row;
    				}
	    		}
	    		else $ret[$k] = new Zkernel_View_Data(array(
	    			'id' => $k,
	    			'quant' => $v
	    		));
    		}
    	}
    	else {
    		$ret = null;
    		if (isset($this->_data[$id])) {
    			if ($this->_model) {
    				$ret = $this->_model->fetchRow(array('`id` = ?' => $id));
    				if ($ret) {
    					$ret = new Zkernel_View_Data($ret);
    					$ret->quant = $this->_data[$id];
    				}
	    		}
	    		else $ret = new Zkernel_View_Data(array(
	    			'id' => $id,
	    			'quant' => $this->_data[$id]
	    		));
    		}
    	}
    	return $ret;
    }

    private function save() {
    	if (!$this->_save) return;
    	if (is_string($this->_storage)) {
			$s = new Zend_Session_Namespace();
			$s->{$this->_storage} = $this->_data;
    	}
    	else {
			// Not yet
    	}
    }

    function __construct($opt = array(), $data = null) {
    	$this->_storage = @$opt['storage'] ? $opt['storage'] : 'basket';
    	$this->_model = @$opt['model'] ? $opt['model'] : new Default_Model_Catalogitem();
    	if ($data !== null) $this->_save = false;
    	if ($data !== null && is_array($data)) foreach ($data as $k => $v) {

    		if (is_array($v)) $this->set($v['id'], $v['quant']);
    		else $this->set($k, $v);
    	}
    	if ($data === null) {
	    	if (is_string($this->_storage)) {
				$s = new Zend_Session_Namespace();
				if (isset($s->{$this->_storage})) $this->_data = $s->{$this->_storage};
	    	}
	    	else {
				// Not yet
	    	}
    	}
    }
}