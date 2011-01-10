<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Clickheat extends Zend_View_Helper_Abstract  {
	function clickheat($opt = array()) {
		$opt['count'] = @(int)$opt['count'];
		$opt['group'] = isset($opt['group']) ? $opt['group'] : 'url';
		if ($opt['group'] == 'title') $group = '(document.title == "" ? "-none-" : encodeURIComponent(document.title))';
		else if ($opt['group'] == 'url') $group = 'encodeURIComponent(window.location.pathname+window.location.search)';
		else $group = '"'.$group.'"';
		$this->view->headScript()->offsetSetFile(166, '/zkernel/ctl/clickheat/js/clickheat.js');
		$this->view->inlineScript()->offsetSetScript(166, 'clickHeatSite = "default";clickHeatGroup = '.$group.';clickHeatServer = "/zkernel/ctl/clickheat/click.php";'.($opt['count'] ? 'clickHeatQuota = '.$opt['count'].';' : '').'initClickHeat();');
	}
}