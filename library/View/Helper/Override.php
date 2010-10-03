<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Override extends Zend_View_Helper_Abstract  {
	protected $_inited = false;

	function _init(){}

	function _overrideModule(&$r, $options) {
		if (@$options['module_nofall']) return;
		foreach ($options['module_field'] as $el) {
			preg_match_all('/\<\!\-\-\ module\:\ \{controller\:\ \"([^\"]*)\"\,\ action\:\ \"([^\"]*)\"\,\ title\:\ \"([^\"]*)\"\}\ \-\-\>/si', $r->$el, $res);
			if (@$res[0]) {
				foreach ($res[0] as $k => $v) {
					$c = $this->view->render($res[1][$k].'/'.$res[2][$k].'.phtml');
					$r->{$el.'_valid'} = str_ireplace(array('<p>'.$v.'</p>', '<div>'.$v.'</div>', $v), array($c), $r->$el);
				}
			}
		}
	}

	function overridePage(&$r, $options = array()) {
		$options['module_field'] = isset($options['module_field']) ? $options['module_field'] : array();
		$this->_overrideModule($r, array_merge(
			$options['module_field'],
			array('module_field' => array('message'))
		));
		$m = $r->message_valid ? $r->message_valid : $r->message;
		$sp = preg_split('/\<\!\-\-\ pagebreak\ \-\-\>/si', $m);
		if (count($sp) < 2) $sp = preg_split('/\<hr(\ )\/\>/si', $m);
		if (count($sp) > 1) {
			$r->description_valid = preg_replace(array('/\<p\>$/i', '/\<p\>(\&nbsp\;|)\<\/p\>$/i'), array('', ''), $sp[0]);
			array_shift($sp);
			$r->message_valid = preg_replace(array('/^\<\/p\>/i', '/^\<p\>(\&nbsp\;|)\<\/p\>/i'), array('', ''), trim(implode('<hr />', $sp)));
		}
		else if (!$r->message_valid) $r->message_valid = $r->message;
	}

	public function overrideSingle($data, $type = null, $options = null) {
		$r = $data instanceof Zkernel_View_Data ? $data : new Zkernel_View_Data($data);
		$reg = Zend_Registry::isRegistered('Zkernel_Multilang') ? Zend_Registry::get('Zkernel_Multilang') : '';
		if ($reg) {
			foreach ($r as $k => $v) if (preg_match('/^ml\_([^\_]+)\_'.$reg->id.'$/i', $k, $f)) {
				if ($v === null && !@$options['multilang_nofall']) {
					if (@$r->{'ml_'.$f[1].'_'.$reg->_default->id} !== null) $r->{$f[1]} = $r->{'ml_'.$f[1].'_'.$reg->_default->id};
				}
				else $r->{$f[1]} = $v;
				/*$r->{$f[1]} = $v === null && !@$options['multilang_nofall']
					? @$r->{'ml_'.$f[1].'_'.$reg->_default->id}
					: $v;*/
			}

		}
		if (isset($r->title)) $r->title_valid = htmlspecialchars($r->title);
		if (isset($r->date)) $r->date_valid = Zkernel_Common::getDate($r->date);
		if ($type !== null && method_exists($this, 'override'.ucfirst($type))) $this->{'override'.ucfirst($type)}($r, $options);
		return $r;
	}

	public function override($data = null, $type = null, $options = null) {
		if(!$this->_inited){$this->_init(); $this->_inited = true;}
		if ($data === null) return $this;
		$nd = array();
		if (!$data) return $nd;
		foreach ($data as $el) {
			$nd[] = $this->overrideSingle($el, $type, $options);
		}
		return $nd;
	}
}