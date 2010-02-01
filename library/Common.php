<?php

class Zkernel_Common {
	static function getInnerIds($param = array()) {
		$ret = array();
		$param['where'] = @$param['where'];
		$param['field'] = @$param['field'] ? $param['field'] : 'parentid';
		$param['model'] = @$param['model'];
		$param['id'] = @$param['id'];
		$result = $param['model']->fetchCol('id', '`'.$param['field'].'` = "'.$param['id'].'"'.($param['where'] ? ' AND ('.$param['where'].')' : ''));
		if ($result) {
			foreach ($result as $el) {
				$ret[] = $el;
				$param['id'] = $el;
				$ret += array_merge($ret, self::getInnerIds($param));
			}
		}
		return $ret;
	}
}
