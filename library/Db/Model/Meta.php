<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Db_Model_Meta extends Zkernel_Db_Table {
	protected $_name = 'meta';
	protected $_multilang_field = array(
		'title',
		'keywords',
		'description'
	);

	function fetchOid($oid) {
		return $this->fetchRow(array('`oid` = ?' => (string)$oid));
	}

	function fetchMatch($url) {
		return $this->fetchRow('"'.$url.'" LIKE CONCAT("%", REPLACE(REPLACE(`url`, "*", "%"), "?", "_"), "%")', 'LENGTH(`url`) DESC');
	}

	public function fetchControlList($where, $order, $count, $offset) {
		return $this->fetchAll(
	    	$where,
	    	'LENGTH(`url`) > 0 DESC, LENGTH(`url`), `oid`',
	    	$count,
	    	$offset
	    );
    }
}
