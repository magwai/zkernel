<?php

class database_model extends k_database_model {
	public $zmodel = null;

	function __construct() {
		$class = 'Default_Model_'.ucfirst(substr(get_class($this), 6));
		if (class_exists($class)) {
			$this->zmodel = new $class;
			$this->name = $this->zmodel->info('name');
		}
		parent::__construct();
	}

	function __call($m, $p) {
		if (isset($this->zmodel)) {
			return call_user_func_array(array($this->zmodel, $m), $p);
		}
	}

	function entity($el, $entity = null) {
		$class = 'entity_'.($entity ? $entity : $this->name);
		return new $class($el);
	}

}