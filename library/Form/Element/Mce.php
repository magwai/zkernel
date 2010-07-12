<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Mce extends Zend_Form_Element_Textarea {
	public function render(Zend_View_Interface $view = null) {
    	$a = $this->getAttribs();
    	unset($a['helper']);
		$o = array(
    		'script_url' => "/zkernel/ctl/mce/tiny_mce.js",
			'theme' => "advanced",
			'language' => "ru",
			'content_css' => "/img/style.css",
			'remove_script_host' => true,
			'relative_urls' => false,
			'add_form_submit_trigger' => false,
			'plugins' => "safari,inlinepopups,table,advimage,advlink,media,print,contextmenu,paste,fullscreen,xhtmlxtras,imagemanager,filemanager,pagebreak,zanchor,noneditable",
			'theme_advanced_blockformats' => "blockquote,h1,h2,h3,h4,h5,h6",
			'theme_advanced_toolbar_location' => "top",
			'theme_advanced_toolbar_align' => "left",
			'theme_advanced_buttons1' => "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,sub,sup",
			'theme_advanced_buttons2' => "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,charmap,hr,|,image,file,media,|,forecolor,backcolor",
			'theme_advanced_buttons3' => "tablecontrols,|,removeformat,|,print,|,newdocument,fullscreen,|,code,|,pagebreak".(APPLICATION_ENV == 'production' ? '' : ',|,zanchor')
    	);
    	if ($a) $o = array_merge($o, $a);
    	$js =
'$.include([
	"/zkernel/js/jquery/jquery.tinymce.js"
], function() {
    $("textarea[name='.$this->getName().']")
    	.addClass("ui-state-disabled")
    	.mousedown(function() { return false; })
    	.focus(function() { return false; })
    	.keydown(function() { return false; })
    	.tinymce('.Zend_Json::encode($o).');
});
';
    	$this->getView()->inlineScript('script', $js);
		return parent::render($view);
	}

	public function getValue() {
		$value = parent::getValue();
		$value = str_ireplace(array(
			'<p style="margin-top: 0px; margin-right: 0px; margin-bottom: 1em; margin-left: 0px;">',
			"<p>&nbsp;</p>\n<hr />\n<p>&nbsp;</p>",
			'<hr />'
		), array(
			'<p>',
			'<hr />',
			'<!-- pagebreak -->'
		), $value);
		return $value;
	}
}
