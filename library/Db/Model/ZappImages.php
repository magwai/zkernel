<?php

class Zkernel_Db_Model_ZappImages extends Zkernel_Db_ZappTable {
	protected $_name = 'images';
	protected $_type;

	function fetchList($id, $count = null, $offset = null, $type = null) {
		$type = ($type === null) ? $this->_type : $type;
		return $this->fetchAll(array('`parentid` = ?' => $id, 
								 	 '`type` = ?' => $type), 'orderid', $count, $offset);
	}

	function deleteList($id, $type = null) {
		$type = ($type === null) ? $this->_type : $type;
		return $this->delete(array('`parentid` = ?' => $id,'`type` = ?' => $type));
	}
	
	function fetchCountImg($where = array(), $type = null){
		$type = ($type === null) ? $this->_type : $type;
		return $this->fetchCount(array_merge($where,array('`type` = ?' => $type)));
	}
	
	function insertImg($data = array(), $type = null) {
		$type = ($type === null) ? $this->_type : $type;
		return $this->insert(array_merge($data,array('type' => $type)));
	}
}