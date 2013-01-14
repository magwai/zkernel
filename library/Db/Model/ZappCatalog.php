<?php

class Zkernel_Db_Model_ZappCatalog extends Zkernel_Db_ZappTable {
    protected $_name = 'catalog';
    protected $_multilang_field = array(
        'title',
    	'message'
        ); 
    
	function fetchCard($stitle,$where = array(),$order = 'orderid') {
		return $this->fetchRow(array_merge(array('`stitle` = ?' => $stitle),$where),$order);
	}

	function fetchList($parentid = 0,$where = array(),$order = 'orderid') {
		$s = $this->select();
		$this->_where($s,array_merge(array('`parentid` = ?' => $parentid),$where));
		$this->_order($s,$order);
		return $this->fetchAllPaged($s);
	}
	
	function fetchAllActual($_catalogitem_model = null, $id){
		//$ci =  new $_catalogitem_model();	
		
		$s = $this->select()->setIntegrityCheck(false)
				  ->from(array('c' => $this->info('name')))
				  //->join(array('ci' => $ci->info('name')),'ci.parentid = c.id',array())
				  //->where('ci.active = "1" AND c.parentid = '.$id)
				  ->where('c.parentid = '.$id)
				  ->order('c.orderid')
				  ->group('c.id');
		
		//print_r($s->assemble());
		return $this->fetchAll($s);
	}
		
}
