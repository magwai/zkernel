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
		if ($this->getView()->control()->config->wysiwyg == 'mce') {
			unset($a['helper']);
			$o = array(
				'script_url' => "/zkernel/ctl/mce/tiny_mce.js",
				'theme' => "advanced",
				'language' => $this->lang,
				'content_css' => "/img/style.css",
				'remove_script_host' => true,
				'relative_urls' => false,
				'add_form_submit_trigger' => false,
				'plugins' => "safari,inlinepopups,table,advimage,advlink,media,print,contextmenu,paste,fullscreen,xhtmlxtras,imagemanager,filemanager,pagebreak,zanchor,noneditable,tabfocus,style",
				//'plugins' => "table,advimage,advlink,media,contextmenu,paste,xhtmlxtras,imagemanager,filemanager,zanchor,style",
				'extended_valid_elements' => "iframe[name|src|framespacing|border|frameborder|scrolling|title|height|width],object[declare|classid|codebase|data|type|codetype|archive|standby|height|width|usemap|name|tabindex|align|border|hspace|vspace],script[type|src]",
				'theme_advanced_blockformats' => "blockquote,h1,h2,h3,h4,h5,h6",
				'theme_advanced_toolbar_location' => "top",
				'theme_advanced_toolbar_align' => "left",
				'theme_advanced_buttons1' => "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,sub,sup",
				'theme_advanced_buttons2' => "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,charmap,hr,|,image,file,media,|,forecolor,backcolor",
				'theme_advanced_buttons3' => "tablecontrols,|,styleprops,|,removeformat,|,print,|,newdocument,fullscreen,|,code,|,pagebreak".(APPLICATION_ENV == 'production' ? '' : ',|,zanchor')
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
		}
		else if ($this->getView()->control()->config->wysiwyg == 'ck') {
			$o = array(
				'customConfig' => '',
				'skin' => 'v2',
				'baseHref' => '/',
				'contentsCss' => '/img/style.css',
				'language' => 'ru',
				'resize_enabled' => false,
				'toolbarCanCollapse' => false,
				'toolbar' => array(
					array('Bold','Italic','Underline','Strike'),
					array('JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'),
					array('Format','Font','FontSize'),
					array('Subscript','Superscript'),
					'/',
					array('Cut','Copy','Paste','PasteText','PasteFromWord'),
					array('NumberedList','BulletedList'),
					array('Outdent','Indent'),
					array('Blockquote'),
					array('Undo','Redo'),
					array('Link','Unlink','Anchor','SpecialChar','HorizontalRule'),
					array('Image','Flash'),
					array('TextColor','BGColor'),
					'/',
					array('Table'),
					array('RemoveFormat'),
					array('Print'),
					array('NewPage','Maximize'),
					array('Source'),
					array('PageBreak')
				),
				'filebrowserBrowseUrl' => '/zkernel/ctl/ck/ckfinder/ckfinder.html',
				'filebrowserImageBrowseUrl' => '/zkernel/ctl/ck/ckfinder/ckfinder.html?Type=Images',
				'filebrowserFlashBrowseUrl' => '/zkernel/ctl/ck/ckfinder/ckfinder.html?Type=Flash',
				'filebrowserUploadUrl' => '/zkernel/ctl/ck/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
				'filebrowserImageUploadUrl' => '/zkernel/ctl/ck/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images',
				'filebrowserFlashUploadUrl' => '/zkernel/ctl/ck/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash'
			);
			if ($a) $o = array_merge($o, $a);
			$js =
'$.include([
	"/zkernel/ctl/ck/ckeditor.js",
	"/zkernel/ctl/ck/adapters/jquery.js"
], function() {
	var opt = '.Zend_Json::encode($o).';
	var o = $("textarea[name='.$this->getName().']");
	opt.width = (o[0].offsetWidth / o.parent()[0].offsetWidth * 100) + "%";
	opt.height = o[0].offsetHeight + "px";
	o.val(o.val().replace("<!-- pagebreak -->", "<div style=\"page-break-after: always;\">\n	<span style=\"display: none;\">&nbsp;</span></div>"));
    o.addClass("ui-state-disabled")
		.mousedown(function() { return false; })
    	.focus(function() { return false; })
    	.keydown(function() { return false; })
    	.ckeditor(function() {}, opt);
});
';
		}
    	$this->getView()->inlineScript('script', $js);
		return parent::render($view);
	}

	public function getValue() {
		$value = parent::getValue();
		$value = str_ireplace(array(
			'<p style="margin-top: 0px; margin-right: 0px; margin-bottom: 1em; margin-left: 0px;">',
			"<p>&nbsp;</p>\n<hr />\n<p>&nbsp;</p>",
			'<hr />',
			'<div style="page-break-after: always;">
	<span style="display: none;">&nbsp;</span></div>'
		), array(
			'<p>',
			'<hr />',
			'<!-- pagebreak -->',
			'<!-- pagebreak -->'
		), $value);
		return $value;
	}
}
