<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Form_Element_Suggest extends Zend_Form_Element_Text {
	public function render(Zend_View_Interface $view = null) {
    	$model = $this->getAttrib('model');

    	$min_length = (int)$this->getAttrib('min_length');
    	$min_length = $min_length ? $min_length : 1;

    	$must_match = $this->getAttrib('must_match');
    	$add_param = $this->getAttrib('add_param');

    	$s = new Zend_Session_Namespace();

    	$s->form[$this->getName()] = array(
			'model' => $model
		);

    	$js =
'$.include(["/zkernel/js/jquery/ui/ui.position.js", "/zkernel/js/jquery/ui/ui.autocomplete.js"], function() {
	var i = $("input[name='.$this->getName().']");
	i.autocomplete({
		source: function(request, response) {
			var add = "";
			'.($add_param ?
			'var ap = '.Zend_Json::encode($add_param).';
			for (k in ap) {
				if (ap[k].slice(0, 3) == "jq:") {
					var o = $(ap[k].slice(3));
					if (o.length && o.val()) add += "/" + k + "/" + o.val();
				}
				else add += "/" + k + "/" + ap[k];
			}' : '').'
			$.ajax({
				url: "/z/suggest/name/'.$this->getName().'" + add,
				dataType: "json",
				data: request,
				success: function(data) {
					'.($must_match ? 'if (data.length == 0) i.val("");' : '').'
					response(data);
				}
			});
		}
		'.($min_length == 1 ? '' : ',minLength: '.$min_length).'
		'.($must_match ? ',select: function(event, ui) { i.data("matched", ui.item.value); }' : '').'
	}).data("matched", i.val())'.($must_match ? '.blur(function() { if (i.data("matched") != i.val()) i.val(""); })' : '').';
});
';
    	$js = str_replace(',}', '}', $js);
    	$this->getView()->inlineScript('script', $js);
    	return parent::render($view);
	}
}
