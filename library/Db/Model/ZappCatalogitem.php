<?php

class Zkernel_Db_Model_ZappCatalogitem extends Zkernel_Db_ZappTable {
    protected $_name = 'catalogitem';
    protected $_multilang_field = array(
        'title',
        'message'
        );
    
        
	function fetchList($id, $order = null) {
		$where = ($id > 0)? array('`parentid` = ?' => $id):array();
		return $this->fetchAllPaged(array_merge($where,array('`active` = "1"')), $order);
	}
	
	//function fetchActiveCount($id) {
	//	return $this->fetchCount(array('`parentid` = ?' => $id, '`active` = 1'));
	//}
	
	function fetchCard($id) {
		return $this->fetchRow(array('`stitle` = ?' => $id, '`active` = "1"'));
	}
	
	//function fetchCardById($id) {
	//	return $this->fetchRow(array('`id` = ?' => $id, '`active` = 1'));
	//}	
	
	function fetchIndexList($count = 5) {
		return $this->fetchAllPaged(array('`show_index` = 1','`active` = 1'), 'orderid', $count);
	}
}
