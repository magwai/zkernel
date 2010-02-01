<?php

class Zkernel_Db_Model_Page extends Zkernel_Db_Table
{
	protected $_name = 'page';

	function fetchCard($id) {
		$ret = $this->fetchRow(array('`stitle` = ?' => $id));
		return $ret = $ret ? $ret : $this->fetchRow('`stitle` = "error"');
	}
}
