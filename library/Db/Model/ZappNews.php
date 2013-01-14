<?php

class Zkernel_Db_Model_ZappNews extends Zkernel_Db_ZappTable {	
	protected $_name = 'news';

	function fetchList($year,$month) {
		$s = $this->select();
		$this->_where($s,array('`date` <= CURDATE()'));
		if($year) $this->_where($s,array('YEAR(`date`) = ?' => $year));
		if($month) $this->_where($s,array('MONTH(`date`) = ?' => $month));
		$this->_order($s,'date desc');
		//print_r($s->assemble()); 
		return $this->getPaginator($s);
	}
	
	function fetchCard($stitle = '') {
		return $this->fetchRow(array('`stitle` = ?' => $stitle,'`date` <= CURDATE()'),'date desc');
	}

	function fetchIndexList($count = 3) {
		return $this->fetchAll(array('`date` <= CURDATE()'), 'date desc', $count);
	}
}