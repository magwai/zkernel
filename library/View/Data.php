<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Data implements Countable, Iterator, ArrayAccess {
    protected $_data = array();
    protected $_index;
    protected $_count;
    protected $_key;
    protected $_skipNextIteration;

	public function __construct($d = null) {
		if ($d !== null) {
			if ($d instanceof Zend_Db_Table_Row) $d = $d->toArray();
			if ($d && (is_array($d) || is_object($d))) foreach ($d as $k => $v) $this->set($k, $v);
		}
    }

    function set($k, $v = null) {
		if (strpos((string)$k, '.')) {
			$kk = explode('.', (string)$k);
			if ($this->__isset($kk[0])) {
				$this->_data[$kk[0]]->set($kk[1], $v);
			}
			else{
				$this->_data[$kk[0]] = new Zkernel_View_Data();
				$this->_data[$kk[0]]->set($kk[1], $v);
			}
		}
		else {
			$this->_data[(string)$k] = $v;
		}
		$this->_count = count($this->_data);
		return $this;
	}

	function get($k) {
		return isset($this->_data[$k])
			? $this->_data[$k]
			: null;
	}

	function __get($k) {
		return $this->get($k);
	}

	function __set($k, $v = null) {
		$this->set($k, $v);
	}

	public function __isset($k) {
        return isset($this->_data[$k]);
    }

    public function __unset($k) {
		unset($this->_data[$k]);
		$this->_count = count($this->_data);
		$this->_skipNextIteration = true;
    }

    public function count() {
        return $this->_count;
    }

    public function current() {
        $this->_skipNextIteration = false;
        return current($this->_data);
    }

    public function key() {
        return key($this->_data);
    }

    public function next() {
        if ($this->_skipNextIteration) {
            $this->_skipNextIteration = false;
            return;
        }
        next($this->_data);
        $this->_index++;
    }

    public function rewind() {
        $this->_skipNextIteration = false;
        reset($this->_data);
        $this->_index = 0;
    }

    public function valid() {
        return $this->_index < $this->_count;
    }

    public function offsetExists($k) {
    	return isset($this->_data[$k]);
    }

	public function offsetGet($k) {
		return $this->get($k);
	}

	public function offsetSet($k, $v) {
		if ($k === null) {
			$k = 0;
			if ($this->_data) foreach ($this->_data as $_k => $_v) if ($k <= $_k) $k = $_k + 1;
		}
		$this->set(array($k => $v));
	}

	public function offsetUnset($k) {
		unset($this->_data[$k]);
		$this->_count = count($this->_data);
		$this->_skipNextIteration = true;
	}

	public function toArray() {
		return $this->_data;
	}
}


