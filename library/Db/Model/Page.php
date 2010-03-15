<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Model_Page extends Zkernel_Db_Table {
	protected $_name = 'page';
	protected $_multilang_field = array(
		'title',
		'message'
	);

	function fetchCard($id) {
		$ret = $this->fetchRow(array('`stitle` = ?' => $id));
		return $ret = $ret ? $ret : $this->fetchRow('`stitle` = "error"');
	}
}
