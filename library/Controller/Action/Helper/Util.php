<?php

class Zkernel_Controller_Action_Helper_Util extends Zend_Controller_Action_Helper_Abstract  {
	function array_merge_recursive_distinct(array &$array1, array &$array2)
	{
		$merged = $array1;
		foreach ( $array2 as $key => &$value )
		{
    		if (is_array($value) && isset ($merged[$key]) && is_array($merged [$key]))
    			$merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
			else
				$merged[$key] = $value;
		}
		return $merged;
	}

	function urlAssemble($controller = '', $action = '', $param = array()) {
    	$p = '';
		if ($param) foreach ($param as $k => $v) $p .= ($p ? '&' : '').$k.'='.$v;
    	return	$controller.
    			($action ? '/'.$action : '').
    			($p && ($controller || $action) ? '?' : '').$p;
    }

	public function direct()
    {
        return $this;
    }

	function getDateBack($period, $parezh = 'imen', $parts = array('day', 'hour', 'minute'), $round = array()) {
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
		if ($n_t && in_array('month', $parts) && (!in_array('month', $round) || $n_t > 3 && $res)) $res .= $n_t.' '.$this->getPluralform($n_t, $month).' ';
		if ($n_d && in_array('day', $parts) && (!in_array('day', $round) || $n_d > 3)) $res .= $n_d.' '.$this->getPluralform($n_d, $day).' ';
		if ($n_h && in_array('hour', $parts) && (!in_array('hour', $round) || $n_h > 3 && $res)) $res .= $n_h.' '.$this->getPluralform($n_h, $hour).' ';
		if ($n_m && in_array('minute', $parts) && (!in_array('minute', $round) || $n_m > 3 && $res)) $res .= $n_m.' '.$this->getPluralform($n_m, $minute).' ';
		if ($n_s && in_array('second', $parts) && (!in_array('second', $round) || $n_s > 3 && $res)) $res .= $n_s.' '.$this->getPluralform($n_s, $second).' ';

		return trim($res);
	}

	function getPluralform($value, $suf = array()) {
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

	function getOuterIds($param = array()) {
		$param['where'] = @$param['where'];
		$param['field'] = @$param['field'] ? $param['field'] : 'parentid';
		$param['model'] = @$param['model'];
		$param['id'] = @$param['id'];
		$ret = array();
		$id = $param['model']->fetchOne($param['field'], '`id` = "'.$param['id'].'"'.($param['where'] ? ' AND ('.$param['where'].')' : ''));
		if ($id) {
			$ret[] = $id;
			$param['id'] = $id;
			$ret_1 = $this->getOuterIds($param);
			$ret = array_merge($ret, $ret_1);
		}
		return $ret;
	}

	function getById($param = array()) {
		$param['field'] = @$param['field'];
		$param['model'] = @$param['model'];
		$param['key'] = @$param['key'] ? $param['key'] : 'id';
		$param['id'] = @$param['id'];
		return $param['model']->fetchOne($param['field'], array('`'.$param['key'].'` = ?' => $param['id']));
	}

	function translit($str) {
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

	function stitle($str, $length = 56000) {
		$str = strtolower($this->translit($str));
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

	function getDocblock($class, $type = 'class') {
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
    		$inner = $this->getDocblock($pk->name);
    		if ($inner) $ret = array_merge($inner, $ret);
    	}
		return $ret;
	}
}