<?php

class Zkernel_Form extends Zend_Form
{
	function init() {
		$this->addPrefixPath(
			'Zkernel_Form_',
			'Zkernel/Form'
		);
	}
}
