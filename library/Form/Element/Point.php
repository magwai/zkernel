<?php

class Zkernel_Form_Element_Point extends Zend_Form_Element_Text
{
	public $helper = 'formPoint';

	public function render(Zend_View_Interface $view = null)
    {
    	$color = $this->getAttrib('color');
    	$value = $this->getValue();
    	$js =
'$.include("/zkernel/ctl/point/point.js", function() {
	point.init("'.$this->getName().'", '.($value ? '['.str_replace('|', ', ', $value).']' : 'null').', {
    	url: "'.$this->getAttrib('url').'",'.
    	($color ? 'color: "'.$color.'",' : '').
    '});
});
';
    	$js = str_replace(',}', '}', $js);
    	Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval($js);

    	$old = $this->getView()->getPluginLoader('helper');

    	$this->getView()->setPluginLoader(new Zend_Loader_PluginLoader(array(
			'Zkernel_View_Helper' => 'Zkernel/View/Helper'
		)), 'helper');

		$ret = parent::render($view);

		$this->getView()->setPluginLoader($old, 'helper');

    	return $ret;
	}
}
