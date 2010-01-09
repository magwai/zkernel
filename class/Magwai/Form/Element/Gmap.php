<?php

class Magwai_Form_Element_Gmap extends Zend_Form_Element_Text
{
	public $helper = 'formGmap';

	public function render(Zend_View_Interface $view = null)
    {
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
	gmap.init("'.$this->getName().'", '.($value ? '['.str_replace('|', ', ', $value).']' : 'null').', {'.
		($width ? 'width: '.$width.',' : '').
	    ($height ? 'height: '.$height.',' : '').
	    ($scrollwheel ? 'scrollwheel: '.$scrollwheel.',' : '').
		($maptypeid ? 'mapTypeId: "'.$maptypeid.'",' : '').
		($zoom ? 'zoom: '.$zoom.',' : '').
		($center ? 'center: ['.str_replace('|', ', ', $center).'],' : '').
	'});
};
$.include([
	"/lib/ctl/gmap/gmap.js",
	"http://maps.google.com/maps/api/js?sensor=false&callback=cb_gmap_'.$this->getName().'",
], function() {
    if (window.cb_gmap_'.$this->getName().'_loaded) window.cb_gmap_'.$this->getName().'();
});
';
		$js = str_replace(',}', '}', $js);

    	Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval($js);

    	$old = $this->getView()->getPluginLoader('helper');

    	$this->getView()->setPluginLoader(new Zend_Loader_PluginLoader(array(
			'Magwai_View_Helper' => 'Magwai/View/Helper'
		)), 'helper');

		$ret = parent::render($view);

		$this->getView()->setPluginLoader($old, 'helper');
		return $ret;
	}
}
