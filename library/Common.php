<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

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

	static function getOuterIds($param = array()) {
		$param['where'] = @$param['where'];
		$param['field'] = @$param['field'] ? $param['field'] : 'parentid';
		$param['model'] = @$param['model'];
		$param['id'] = @$param['id'];
		$ret = array();
		$id = $param['model']->fetchOne($param['field'], '`id` = "'.$param['id'].'"'.($param['where'] ? ' AND ('.$param['where'].')' : ''));
		if ($id) {
			$ret[] = $id;
			$param['id'] = $id;
			$ret_1 = self::getOuterIds($param);
			$ret = array_merge($ret, $ret_1);
		}
		return $ret;
	}

	static function strtolower($str) {
		return iconv('windows-1251', 'utf-8', strtolower(strtr(
			iconv('utf-8', 'windows-1251', $str),
			iconv('utf-8', 'windows-1251', 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ'),
			iconv('utf-8', 'windows-1251', 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя')
		)));
	}
}
