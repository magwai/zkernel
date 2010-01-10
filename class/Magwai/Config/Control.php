<?php

class Magwai_Config_Control implements Countable, Iterator, ArrayAccess {
    protected $_data = array();
    protected $_index;
    protected $_count;
    protected $_key;
    protected $_skipNextIteration;

	public function __construct() {
		$a = func_get_args();
        if ($a) foreach ($a as $e) $this->set($e);
    }

    function set($k, $v = null) {
		if (is_array($k) || $k instanceof Magwai_Config_Control) {
			if ($k) foreach ($k as $_k => $_v) {
				if (isset($this->_data[$_k]) && $this->_data[$_k] instanceof Magwai_Config_Control && (is_array($_v) || $_v instanceof Magwai_Config_Control)) $this->_data[$_k]->set($_v);
				else $this->set($_k, $_v);
			}
		}
		else {
			if (is_array($v)) {
				$v = new Magwai_Config_Control($v);
				$v->_key = $k;
			}
			$this->correct($k, $v);
			if ($v instanceof Magwai_Config_Control) $v->_key = $k;
			$this->_data[$k] = $v;
			$this->_count = count($this->_data);
		}
		return $this;
	}

	function correct($k, &$v) {
		if ($this->_key === 'field') {
			$t = $v;
			$v = new Magwai_Config_Control(array(
				'stype' => '',
				'editoptions' => '',
				'required' => false,
				'type' => 'text',
				'align' => '',
				'width' => '',
				'description' => '',
				'sortable' => false,
				'active' => $k != 'id',
				'name' => $k,
				'title' => $k,
				'hidden' => false,
				'formatter' => '',
				'formatoptions' => '',
				'param' => array()
			));

			$v->set($t);
		}
		else if (($this->_key === 'button_top' || $this->_key === 'button_bottom')) {
			if (is_string($v)) {
				$action = $v;
				$default = 0;
				$confirm = 0;
				$param = '';
			}
			else {
				$action = $v->action;
				$default = (int)$v->default;
				$confirm = (int)$v->confirm;
				$param = $v->param;
			}

			switch ($action) {
				case 'add':
					$title = 'Добавить';
					break;
				case 'edit':
					$title = 'Изменить';
					break;
				case 'delete':
					$title = 'Удалить';
					break;
				default:
					$title = $v->title;
					break;
			}
			$v = new Magwai_Config_Control(array(
				'title' => $title,
				'controller' => Zend_Controller_Front::getInstance()->getRequest()->getControllerName(),
				'action' => 'ctl'.$action,
				'param' => $param,
				'default' => $default,
				'confirm' => $confirm
			));
		}
	}

	function get($k) {
		if (isset($this->_data[$k])) {
			$ret = $this->_data[$k];
			if (is_string($ret) && substr($ret, 0, 13) == 'php_function:') {
				$f = create_function('$control', substr($ret, 13));
				$control = Zend_Controller_Action_HelperBroker::getStaticHelper('control');
				$ret = $f($control);
				if ($ret) {
					$this->set($k, $ret);
					$this->_data[$k]->set($ret);
					$ret = $this->_data[$k];
				}
			}
		}
		else $ret = null;
		return $ret;
	}

	public function toArray() {
        $array = array();
        foreach ($this as $key => $value) {
            if ($value instanceof Magwai_Config_Control) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $this->$key;
            }
        }
        return $array;
    }

	function __get($k) {
		return $this->get($k);
	}

	function __set($k, $v = null) {
		/*if (is_array($v) && is_array($this->_data[$k])) {
			$this->_data[$k] = $this->array_merge($this->_data[$k], $v);
		}
		else $this->_data[$k] = $v;
		$this->_count = count($this->_data);*/
		$this->set(array($k => $v));
	}

	public function __clone() {
      $array = array();
      foreach ($this->_data as $key => $value) {
          if ($value instanceof Magwai_Config_Control) {
              $array[$key] = clone $value;
          } else {
              $array[$key] = $value;
          }
      }
      $this->_data = $array;
    }

	public function __isset($name) {
        return isset($this->_data[$name]);
    }

    public function __unset($name) {
		unset($this->_data[$name]);
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

	private function is_assoc($array) {
		if (!is_array($array)) return false;
		foreach (array_keys($array) as $k => $v) {
			if ($k !== $v) return true;
		}
  		return false;
	}

	function array_merge(array &$array1, array &$array2){
		$merged = $array1;
		foreach ( $array2 as $key => &$value )
		{
    		if (is_array($value) && isset ($merged[$key]) && is_array($merged [$key]))
    			$merged[$key] = $this->array_merge($merged[$key], $value);
			else
				$merged[$key] = $value;
		}
		return $merged;
	}
}


