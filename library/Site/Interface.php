<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Magwai_Site_Interface
{
	protected $_model = null;
	protected $_data = array();

	public function __construct($model = 'Default_Model_Interface')
    {
    	if ($model instanceof Magwai_Db_Table_Abstract) {
            $this->_model = $model;
        }
        else $this->_model = new $model();
        $this->_data = $this->_model->fetchPairs();
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) return $this->_data[$name];
        else {
            throw new Exception('Invalid Interface property');
        }
    }

}
