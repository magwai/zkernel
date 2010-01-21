<?php

class Zkernel_Form extends Zend_Form
{
	function init() {
		$this->addPrefixPath(
			'Zkernel_Form_',
			'Zkernel/Form'
		);
	}

	function getErrors() {
		$d = array(
			'isEmpty' => 'обязательно для заполнения',
			'regexNotMatch' => 'недопустимые символы'
		);
		$e = parent::getErrors();
		if ($e) {
			foreach ($e as &$el) {
				if ($el) {
					foreach ($el as &$el_1) if (isset($d[$el_1])) $el_1 = $d[$el_1];
				}
			}
		}
		return $e;
	}
}
