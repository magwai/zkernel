<?php

class common extends k_common {
	static function autoload($class) {
		if (substr($class, 0, 6) == 'model_') {
			eval('class '.$class.' extends database_model { };');
		}
		else if (substr($class, 0, 7) == 'entity_') {
			eval('class '.$class.' extends entity { };');
		}
	}
}