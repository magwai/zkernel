<?php

class Zkernel_View_Helper_Override extends Zend_View_Helper_Abstract  {
	public function overrideSingle($data, $type = null) {
		$r = new Zkernel_View_Data($data);
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