(function() {
	tinymce.create('tinymce.plugins.ZanchorPlugin', {
		init : function(ed, url) {
			ed.onInit.add(function() {
				if (ed.settings.content_css !== false)
					ed.dom.loadCSS(url + "/css/zanchor.css");
			});

			// Register commands
			ed.addCommand('mceZanchor', function() {
				var c = "", a = "", t = "";
				if (ed.selection) {
					var n = ed.selection.getNode();
					if (n) {
						if (n.nodeName === 'IMG' && n.outerHTML.indexOf('class="mceZanchor') !== -1) {
							var r = zanchor_parse(n.outerHTML);
							c = r.controller;
							a = r.action;
							t = r.title;
						}
					}
				}
				ed.windowManager.open({
					file : url + '/zanchor.htm',
					width : 300 + parseInt(ed.getLang('zanchor.delta_width', 0)),
					height : 400 + parseInt(ed.getLang('zanchor.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url,
					plugin_controller: c,
					plugin_action: a,
					plugin_title: t
					
				});
			});
			ed.onClick.add(function(ed, e, cm) {
				e = e.target;
				if (e.nodeName === 'IMG' && e.outerHTML.indexOf('class="mceZanchor') !== -1) {
					ed.selection.select(e);
					window.setTimeout(function() {
						ed.controlManager.setDisabled('zanchor', false);
					}, 1);
				}
			});
			window.uuuuuu = url;

			// Register buttons
			ed.addButton('zanchor', {
				title : 'zanchor.zanchor_desc',
				cmd : 'mceZanchor'
			});

			ed.onBeforeSetContent.add(function(ed, o) {
				var url = window.uuuuuu;
				o.content = o.content.replace(/\<\!\-\-\ module\:\ \{controller\:\ \"([^\"]+)\"\,\ action\:\ \"([^\"]+)\"\,\ title\:\ \"([^\"]+)\"\}\ \-\-\>/gi, '<img class="mceZanchor mceNonEditable mceItemNoResize" alt="controller: $1; action: $2;" title="$3" src="' + url + '/img/trans.gif" />');
			});
	
			ed.onPostProcess.add(function(ed, o) {
				var m1 = /controller\:\ ([^\;]*)\;\ action\:\ ([^\;]*)\;/gim;
				var m2 = /\ title\=\"([^\"]+)\"/gim;
				if (o.get) o.content = o.content.replace(/<img[^>]+class\=\"mceZanchor[^>]+>/g, function(im) {
					var r = zanchor_parse(im);
					if (r.controller && r.action) im = '<!-- module: {controller: "' + r.controller + '", action: "' + r.action + '", title: "' + r.title + '"} -->';
					return im;
				});
			});
		},

		getInfo : function() {
			return {
				longname : 'Zanchor',
				author : 'Magwai',
				authorurl : 'http://magwai.ru',
				infourl : 'http://magwai.ru/contact',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('zanchor', tinymce.plugins.ZanchorPlugin);
})();

function zanchor_parse(im) {
	var m1 = /controller\:\ ([^\;]*)\;\ action\:\ ([^\;]*)\;/gi;
	var m2 = /\ title\=\"([^\"]+)\"/gi;
	var c = '', a = '', t = '';
	m1.compile(m1); 
	var m11 = m1.exec(im);
	if (m11 && typeof m11[1] != 'undefined' && typeof m11[2] != 'undefined') {
		m2.compile(m2);
		var m22 = m2.exec(im);
		c = m11[1];
		a = m11[2];
		t = m22 && typeof m22[1] == 'undefined' ? '' : m22[1];
	}
	return {controller: c, action: a, title: t};
}

function zanchor_rs(str) {
	return str.replace(/[\=\<\>\;\ \/\"\'\?\.\*\[\]\(\)\{\}\+\^\$\:]/g, function(a) {return '\\' + a;});
}