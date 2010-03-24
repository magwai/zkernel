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
			$r->description_valid = $sp[0];
			array_shift($sp);
			$r->message_valid = preg_replace('/^\<\/p\>/i', '', trim(implode('<hr />', $sp)));
		}
	}

	public function overrideSingle($data, $type = null, $options = null) {
		$r = $data instanceof Zkernel_View_Data ? $data : new Zkernel_View_Data($data);
		$reg = Zend_Registry::isRegistered('Zkernel_Multilang') ? Zend_Registry::get('Zkernel_Multilang') : '';
		if ($reg) {
			foreach ($r as $k => $v) if (preg_match('/^ml\_([^\_]+)\_'.$reg->id.'$/i', $k, $f)) {
				$r->{$f[1]} = $v === null && !@$options['multilang_nofall']
					? $r->{'ml_'.$f[1].'_'.$reg->_default->id}
					: $v;
			}
		}
		if (isset($r->title)) $r->title_valid = htmlspecialchars($r->title);
		if (isset($r->date)) $r->date_valid = Zkernel_Common::getDate($r->date);
		if ($type !== null && method_exists($this, 'override'.ucfirst($type))) $this->{'override'.ucfirst($type)}($r);
		return $r;
	}

	public function override($data = null, $type = null, $options = null) {
		if ($data === null) return $this;
		$nd = array();
		if (!$data) return $nd;
		foreach ($data as $el) {
			$nd[] = $this->overrideSingle($el, $type, $options);
		}
		return $nd;
	}
}