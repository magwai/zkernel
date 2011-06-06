<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Gmap extends Zend_Form_Element_Text {
	public $helper = 'formGmap';

	public function render(Zend_View_Interface $view = null) {
    	$type = $this->getAttrib('type');
    	$value = $this->getValue();
    	$height = $this->getAttrib('height');
		$width = $this->getAttrib('width');
		$scrollwheel = $this->getAttrib('scrollwheel');
		$center = $this->getAttrib('center');
		$zoom = $this->getAttrib('zoom');
		$maptypeid = $this->getAttrib('maptypeid');

    	$js =
'if (typeof window.cb_gmap_'.$this->getName().'_loaded == "undefined") window.cb_gmap_'.$this->getName().'_loaded = false;
window.cb_gmap_'.$this->getName().' = function(a1, a2) {
	window.cb_gmap_'.$this->getName().'_loaded = true;
	gmap.init("'.$this->getName().'", '.($value ? '["'.str_replace(array('|', ' '), array('","', '","'), $value).'"]' : 'null').', {'.
	'"type": "'.($type ? $type : 'point').'",'.
		($width ? '"width": '.$width.',' : '').
	    ($height ? '"height": '.$height.',' : '').
	    ($scrollwheel ? '"scrollwheel": '.$scrollwheel.',' : '').
		($maptypeid ? '"mapTypeId": "'.$maptypeid.'",' : '').
		($zoom ? '"zoom": '.$zoom.',' : '').
		($center ? '"center": ["'.str_replace('|', ', ', $center).'"],' : '').
	'});
};
$.include([
	"/zkernel/ctl/gmap/gmap.js",
	"http://maps.google.com/maps/api/js?sensor=false&callback=cb_gmap_'.$this->getName().'",
], function() {
    if (window.cb_gmap_'.$this->getName().'_loaded) window.cb_gmap_'.$this->getName().'();
});
';
		$js = str_replace(',}', '}', $js);
    	$this->getView()->inlineScript('script', $js);
    	return parent::render($view);
	}
}
