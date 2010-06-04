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

	function fetchMatch($url) {
		return $this->fetchRow('"'.$url.'" LIKE REPLACE(REPLACE(`url`, "*", "%"), "?", "_")', 'LENGTH(`url`) DESC');
	}
}
