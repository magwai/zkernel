var ZanchorDialog = {
	init: function(ed) {
		var f = document.forms[0];
		f.controller.value = ed.windowManager.params.plugin_controller;
		f.action.value = ed.windowManager.params.plugin_action;
		f.title.value = ed.windowManager.params.plugin_title;
	},
	update : function() {
		var ed = tinyMCEPopup.editor, h, f = document.forms[0];
		var url = ed.windowManager.params.plugin_url;
		
		if (f.controller.value && f.action.value) {
			h = '<img class="mceZanchor mceNonEditable mceItemNoResize" alt="controller: ' + f.controller.value + '; action: ' + f.action.value + ';" title="' + f.title.value + '" src="' + url + '/img/trans.gif" />';
	
			var n = ed.selection.getNode();
			if (n.nodeName == 'IMG' && n.outerHTML.indexOf('class="mceZanchor') !== -1) {
				var p = n.parentNode;
				p.removeChild(n, true);
				ed.selection.select(p);
				ed.selection.collapse(true);
			}
			ed.execCommand("mceInsertContent", false, h);
		}
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.requireLangPack();
tinyMCEPopup.onInit.add(ZanchorDialog.init, ZanchorDialog);
