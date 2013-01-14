<?php

class Zkernel_Db_Model_ZappSimpleBand extends Zkernel_Db_ZappTable {	
	protected $_name = 'simpleband';

	function fetchList() {
		return $this->fetchAllPaged(array('`active` = "1"'));
	}
	
	function fetchCard($stitle = '') {
		return $this->fetchRow(array('`stitle` = ?' => $stitle),'orderid');
	}

	function fetchIndexList($count = 3) {
		return $this->fetchAll(null, 'orderid', $count);
	}
}