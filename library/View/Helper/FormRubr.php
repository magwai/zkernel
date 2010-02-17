<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_FormRubr extends Zend_View_Helper_FormMultiCheckbox
{
    public function formRubr($name, $value = null, $attribs = null, $options = null)
    {
		$options = array();
		if ($value) {
			$model = $attribs['rubr']['model'];
			foreach ($value as $el) {
				$item = $model->fetchRow(array('`id` = ?' => $el));
				$r = $this->digg_rubr($model, $item->parentid);
				$options[$item->id] = (string)$item->title.($r ? ' <small>('.$r.')</small>' : '');
			}
		}
		unset($attribs['rubr']);
		$attribs['escape'] = false;
    	$xhtml = $this->formRadio($name, $value, $attribs, $options);
    	$xhtml = '<div class="c_rubr" id="rubr_'.$name.'"></div><hr /><div class="c_rubr_value" id="rubr_'.$name.'_value">'.$xhtml.'</div>';
        return $xhtml;
    }

    private function digg_rubr($model, $id) {
    	$ret = '';
    	$p = $model->fetchRow(array('`id` = ?' => $id));
    	if ($p) {
    		$i = $this->digg_rubr($model, $p->parentid);
    		$ret = $i.($i ? ' / ' : '').$p->title;
    	}
    	return $ret;
    }
}
