<?php

class Magwai_Form_Element_Mce extends Zend_Form_Element_Textarea
{
	public function render(Zend_View_Interface $view = null)
    {
    	$js =
'$.include([
	"/lib/js/jquery/jquery.tinymce.js",
	"/lib/ctl/edit_area/edit_area_full.js"
], function() {
    $("textarea[name='.$this->getName().']").tinymce({
		script_url: "/lib/ctl/mce/tiny_mce.js",
		theme: "advanced",
		language: "ru",
		content_css: "/img/style.css",
		remove_script_host: true,
		relative_urls: false,
		add_form_submit_trigger: false,
		plugins: "safari,inlinepopups,table,advimage,advlink,media,print,contextmenu,paste,fullscreen,xhtmlxtras,imagemanager,filemanager",
		theme_advanced_blockformats: "blockquote,h1,h2,h3,h4,h5,h6",
		theme_advanced_toolbar_location: "top",
		theme_advanced_toolbar_align: "left",
		theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,sub,sup",
		theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,charmap,|,image,file,media,|,forecolor,backcolor",
		theme_advanced_buttons3: "tablecontrols,|,removeformat,|,print,|,newdocument,fullscreen,|,code,|,hr"
	});
});
';
    	Zend_Controller_Action_HelperBroker::getStaticHelper('js')->addEval($js);
		return parent::render($view);
	}
}
