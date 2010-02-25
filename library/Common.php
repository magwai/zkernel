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

	static function hex2rgb ($hex , $asString = false) {
		if ( 0 === strpos ( $hex , '#' )) {
		$hex = substr ( $hex , 1 );
		} else if ( 0 === strpos ( $hex , '&H' )) {
		$hex = substr ( $hex , 2 );
		}
		$cutpoint = ceil ( strlen ( $hex ) / 2 )- 1 ;
		$rgb = explode ( ':' , wordwrap ( $hex , $cutpoint , ':' , $cutpoint ), 3 );
		$rgb [ 0 ] = ( isset ( $rgb [ 0 ]) ? hexdec ( $rgb [ 0 ]) : 0 );
		$rgb [ 1 ] = ( isset ( $rgb [ 1 ]) ? hexdec ( $rgb [ 1 ]) : 0 );
		$rgb [ 2 ] = ( isset ( $rgb [ 2 ]) ? hexdec ( $rgb [ 2 ]) : 0 );
		return ( $asString ? "{$rgb[0]} {$rgb[1]} {$rgb[2]}" : $rgb );
	}
}
