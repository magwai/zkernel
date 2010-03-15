<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Rubr extends Zend_Form_Element_Multi {
	public $helper = 'formRubr';

	public function render(Zend_View_Interface $view = null) {
    	$value = $this->getValue();
    	$data = $this->build_tree();

    	$js =
'$.include("/zkernel/ctl/rubr/rubr.js", function() {
    rubr.init("'.$this->getName().'", {
		data: '.Zend_Json::encode($data).'
    });
});
';
		$js = str_replace(',}', '}', $js);
		$this->getView()->inlineScript('script', $js);
    	return parent::render($view);
	}

	private function build_tree($pid = 0) {
		$data = array();
		$value = $this->getValue();
		$rubr = $this->getAttrib('rubr');
		$parentid = $rubr->parentid ? $rubr->parentid : 'parentid';
		$orderby = $rubr->orderby ? $rubr->orderby : 'orderid';
		$orderdir = $rubr->orderdir ? $rubr->orderdir : 'asc';
		$result = $rubr->model->fetchAll(array('`'.$parentid.'` = ?' => $pid), $orderby.' '.$orderdir);
		if ($result) {
			foreach ($result as $el) {
				$a = array(
					'd' => $el->id,
					't' => $el->title
				);
				$i = $this->build_tree($el->id);
				if ($i) $a['i'] = $i;
				if ($value && in_array($el->id, $value)) $a['c'] = 1;
				$data[] = $a;
			}
		}
		return $data;
	}
}
