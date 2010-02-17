/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

zuf = {
	uploads: {}
};

zuf.init = function(o) {
	var n = o.fileDataName;
	var hh = $('input[name=' + n + '][type=file]');
	var h = $('input[name=' + n + '][type=hidden]');
	var e = h.prevAll('em');
	var oo = {
		'uploader': '/zkernel/ctl/uploadify/uploadify.swf',
		'cancelImg': '/zkernel/ctl/uploadify/cancel.png',
		'script': '/fu/',
		'onComplete': function(e, queueID, fileObj, response, data) {
			if (response && response.slice(0, 2) == "u|") {
				var v = response.slice(2);
				h.val(response);
				var sd = o.scriptData;
				sd.old = v;
				hh.uploadifySettings('scriptData', sd);
				zuf.uploads[n] = true;
				var done = true;
				for (k in zuf.uploads) {
					if (zuf.uploads[k] == false) {
						done = false;
						break;
					}
				}
				if (done) hh.trigger('complete');
			}
			else hh.trigger('error', {queueID: queueID, fileObj: fileObj, errorObj: {type: 'Security', info: response}});
		},
		'onSelectOnce': function(e, d) {
			zuf.uploads[n] = false;
		},
		'onError': function(e, queueID, fileObj, errorObj) { hh.trigger('error', {queueID: queueID, fileObj: fileObj, errorObj: errorObj}); },
		'onSelect': function() { if (h.hasClass("zuf_deleted")) hh.prevAll("em").find(">a").click(); }
	};

	hh.uploadify($.extend(oo, o));

	e.find('>a').click(function() {
		if (h.hasClass('zuf_deleted')) {
			e.find('span').css({
				'text-decoration': 'none',
				'opacity': 1
			});
			$(this).find('img').css({
				'opacity': 1
			}).attr('title', '');
			e.find('span>a').unbind('click').attr('title', 'Удалить');
			h.removeClass('zuf_deleted');
			h.val(h.attr('vv')).attr('vv', '');
		}
		else {
			e.find('span').css({
				'text-decoration': 'line-through',
				'opacity': .3
			});
			$(this).find('img').css({
				'opacity': .3
			}).attr('title', 'Не удалять');
			e.find('span>a').attr('title', 'Не удалять').unbind('click').click(function() {
				e.find('>a').click();
				return false;
			});
			h.addClass('zuf_deleted');
			h.attr('vv', h.val()).val('d|' + h.val());
			$('input[name=' + n + '][type=file]').uploadifyClearQueue();
		}
		return false;
	});
};

zuf.set = function(n, title, url, required) {
	var h = $('input[name=' + n + '][type=hidden]');
	var e = h.prevAll('em');
	e.find('span>a').attr('href', url).html(title);
	e.show();
	h.val(h.val().slice(2));
	if (!required) e.find('>a').show();
	if (h.hasClass('zuf_deleted')) e.find('>a').click();
};

zuf.remove = function(n) {
	var h = $('input[name=' + n + '][type=hidden]');
	h.val('').prevAll('em').hide();
};
