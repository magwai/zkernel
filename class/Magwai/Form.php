<?php

class Magwai_Form extends Zend_Form
{
	function init() {
		$this->addPrefixPath(
			'Magwai_Form_',
			'Magwai/Form'
		);
	}
}
