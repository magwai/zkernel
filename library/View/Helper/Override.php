<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Override extends Zend_View_Helper_Abstract  {
	function overridePage(&$r) {
		$sp = preg_split('/\<hr(\ )\/\>/si', $r->message);
		if (count($sp) > 1) {
			$r->description = $sp[0];
			array_shift($sp);
			$r->message = preg_replace('/^\<\/p\>/i', '', trim(implode('<hr />', $sp)));
		}
	}

	public function overrideSingle($data, $type = null) {
		$r = $data instanceof Zkernel_View_Data ? $data : new Zkernel_View_Data($data);
		if (isset($r->title)) $r->title_valid = htmlspecialchars($r->title);
		if (isset($r->date)) $r->date_valid = Zend_Controller_Action_HelperBroker::getStaticHelper('util')->getDate($r->date);
		if ($type !== null) $this->{'override'.ucfirst($type)}($r);
		return $r;
	}

	public function override($data = null, $type = null) {
		if ($data === null) return $this;
		$nd = array();
		if (!$data) return $nd;
		foreach ($data as $el) {
			$nd[] = $this->overrideSingle($el, $type);
		}
		return $nd;
	}
}