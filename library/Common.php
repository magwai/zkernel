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

	static function url2array($url) {
		$arr = array();
    	$url = explode('&', $url);
    	if ($url) foreach ($url as $el) {
    		$p = explode('=', $el);
    		if (@$p[0]) $arr[$p[0]] = @$p[1];
    	}
    	return $arr;
    }

	static function getById($param = array()) {
		$param['field'] = @$param['field'];
		$param['model'] = @$param['model'];
		$param['key'] = @$param['key'] ? $param['key'] : 'id';
		$param['id'] = @$param['id'];
		return $param['model']->fetchOne($param['field'], array('`'.$param['key'].'` = ?' => $param['id']));
	}

	static function getDocblock($class, $type = 'class') {
		$ret = array();
		if ($type == 'method') $r = $class;
		else $r = new Zend_Reflection_Class($class);
		if ($r->getDocComment()) {
    		$d = $r->getDocblock();
			if ($d) $d = $d->getTags();
			if ($d) {
				foreach ($d as $el) $ret[$el->getName()] = $el->getDescription();
			}
    	}
    	$pk = method_exists($r, getParentClass)
    		? $r->getParentClass()
    		: false;
    	if ($pk) {
    		$inner = self::getDocblock($pk->name);
    		if ($inner) $ret = array_merge($inner, $ret);
    	}
		return $ret;
	}

	static function getDate($date, $template = 'd.m.Y') {
    	if (!$date || $date == '0000-00-00 00:00:00') return '';
		$res = '';
		if ($template == 'word' || $template == 'word_noyear') {
			$months = array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
			$dt = strtotime($date);
			$res = date('d', $dt)." ".$months[date('m', $dt) - 1].($template == 'word_noyear' ? '' : ' '.date('Y', $dt));
		}
		else $res = @date($template, @strtotime($date));
		return $res;
    }

	static function getDateBack($period, $parezh = 'imen', $parts = array('day', 'hour', 'minute'), $round = array()) {
		global $m;
		if ($period < 0) return '';
		$res = '';
		$year = array('год', 'года', 'лет');
		$month = array('месяц', 'месяца', 'месяцев');
		$day = array('день', 'дня', 'дней');
		$hour = array('час', 'часа', 'часов');
		$minute = array('минута', 'минуты', 'минут');
		$second = array('секунда', 'секунды', 'секунд');
		if ($parezh == 'vinit') {
			$minute[0] = 'минуту';
			$second[0] = 'секунду';
		}
		$n_y = in_array('year', $parts)
			? floor($period / 31536000)
			: 0;
		$n_t = in_array('month', $parts)
			? floor(($period - ($n_y ? $n_y * 31536000 : 0)) / 2592000)
			: 0;
		$n_d = floor(($period - (($n_y ? $n_y * 31536000 : 0) + ($n_t ? $n_t * 2592000 : 0))) / 86400);
		$n_h = floor(($period - ($n_y * 31536000 + $n_t * 2592000 + $n_d * 86400)) / 3600);
		$n_m = floor(($period - ($n_y * 31536000 + $n_t * 2592000 + $n_d * 86400 + $n_h * 3600)) / 60);
		$n_s = floor($period - ($n_y * 31536000 + $n_t * 2592000 + $n_d * 86400 + $n_h * 3600 + $n_m * 60));

		if ($n_y && in_array('year', $parts)) $res .= $n_y.' '.$m->f_get_pluralform($n_y, $year).' ';
		if ($n_t && in_array('month', $parts) && (!in_array('month', $round) || $n_t > 3 && $res)) $res .= $n_t.' '.self::getPluralform($n_t, $month).' ';
		if ($n_d && in_array('day', $parts) && (!in_array('day', $round) || $n_d > 3)) $res .= $n_d.' '.self::getPluralform($n_d, $day).' ';
		if ($n_h && in_array('hour', $parts) && (!in_array('hour', $round) || $n_h > 3 && $res)) $res .= $n_h.' '.self::getPluralform($n_h, $hour).' ';
		if ($n_m && in_array('minute', $parts) && (!in_array('minute', $round) || $n_m > 3 && $res)) $res .= $n_m.' '.self::getPluralform($n_m, $minute).' ';
		if ($n_s && in_array('second', $parts) && (!in_array('second', $round) || $n_s > 3 && $res)) $res .= $n_s.' '.self::getPluralform($n_s, $second).' ';

		return trim($res);
	}

	static function getPluralform($value, $suf = array()) {
		$last = (int)substr($value, strlen($value) - 1, 1);
		if ($value > 10 && $value < 20) $res = @$suf[2];
		else $res = $last == 1
			? @$suf[0]
			: ($last > 0 && $last < 5
				? @$suf[1]
				: @$suf[2]
			);
		return $res;
	}

	static function translit($str) {
		$tr = array(
			"Ґ"=>"G","Ё"=>"YO","Є"=>"E","Ї"=>"YI","І"=>"I",
			"і"=>"i","ґ"=>"g","ё"=>"yo","№"=>"#","є"=>"e",
			"ї"=>"yi","А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
			"Д"=>"D","Е"=>"E","Ж"=>"ZH","З"=>"Z","И"=>"I",
			"Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
			"О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
			"У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
			"Ш"=>"SH","Щ"=>"SCH","Ъ"=>"'","Ы"=>"YI","Ь"=>"",
			"Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh",
			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
			"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"'",
			"ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
		);
		return strtr($str, $tr);
	}

	static function stitle($str, $length = 56000) {
		$str = self::translit($str);
		$str = strtolower($str);
		$str = @preg_replace('/[^\w]/', '_', $str);
		while (strpos($str, '__') !== false) $str = str_replace('__', '_', $str);
		$str = trim($str, '_');
		if (strlen($str) > $length) {
			$p = explode('_', $str);
			$c = array();
			foreach ($p as $el) $c[] = strlen($el);
			while (strlen($str) > $length) {
				$i = array_search(max($c), $c);
				if ($i === false) break;
				unset($c[$i]);
				unset($p[$i]);
				$str = implode('_', $p);
			}
		}
		return $str;
	}

	static function stitleUnique($model, $stitle, $field = 'stitle', $where = array()) {
		$where = is_array($where) ? $where : array();
		$stitle = $stitle ? $stitle : '_';
    	$stitle_n = $stitle;
		$stitle_p = -1;
		do {
			$stitle_p++;
			$stitle_n = $stitle.($stitle_p == 0 ? '' : $stitle_p);
			$w = $where;
			$w['`'.$field.'` = ?'] = $stitle_n;
			$stitle_c = (int)$model->fetchCount($w);
		}
		while ($stitle_c > 0);
		return $stitle_n;
	}

	static function truncateText($text, $length = 100, $dots = false) {
		global $m;
		$text = $ftext = str_ireplace(array('&nbsp;'), array(' '), trim(strip_tags($text)));
		$pos_dot = $pos_com = $pos_sp = 0;
		for ($i = $length + 10; $i > $length - 11; $i--) {
			if (@($text[$i] == '.') && !$pos_dot) $pos_dot = $i;
			else if (@($text[$i] == ',' || $text[$i] == ';') && !$pos_com) $pos_com = $i;
			else if (@($text[$i] == ' ' || $text[$i] == '-' || $text[$i] == '_') && !$pos_sp) $pos_sp = $i;
		}
		if ($pos_dot) $pos = $pos_dot;
		else if ($pos_com) $pos = $pos_com;
		else if ($pos_sp) $pos = $pos_sp;
		else $pos = $length;
		$text = substr($text, 0, $pos);
		return $text.($dots ? ($text == $ftext ? '' : '&nbsp;...') : '');
	}
}
