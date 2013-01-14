<?php

class Zkernel_Db_ZappTable extends Zkernel_Db_Table {
    protected $_page = null;
    protected $_per_page;
    
	public function setPaginator($pageNumber = 1, $per_page = 10){
		$this->_page = $pageNumber;
		$this->_per_page = $per_page;
	}
	
	public function clearPaginator(){
	}
	
	public function getPaginator($select) {
		if($select === null) $select = $this->select();
  		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
  		$paginator->setCurrentPageNumber($this->_page);
 		$paginator->setItemCountPerPage($this->_per_page);
  		//$paginator->setPageRange(1);
  		return $paginator;
	}
	
	public function fetchAllPaged($where = null, $order = null, $count = null, $offset = null){
	    
		if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select();
            if ($where !== null) $this->_where($select, $where);
            if ($order !== null) $this->_order($select, $order);
            if ($count !== null || $offset !== null) $select->limit($count, $offset);
        } else {
            $select = $where;
        }
        
		//print_r($select->assemble());
		return ($this->_page)?$this->getPaginator($select):$this->fetchAll($select);		
	}
		
}