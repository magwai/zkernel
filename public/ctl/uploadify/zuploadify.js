/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

zuf = {
	uploads: {},
	results: {},
	inited: false
};

zuf.init = function(o) {
	var n = o.fileDataName;
	var hh = $('input[name=' + n + '][type=file]');
	var h = $('input[name=' + n + '][type=hidden]');
	var e = h.prevAll('em');
	var oo = {
		'multi': typeof o.multi == 'undefined' ? false : true,
		'uploader': '/zkernel/ctl/uploadify/uploadify.swf',
		'cancelImg': '/zkernel/ctl/uploadify/cancel.png',
		'script': '/z/fu',
		'onComplete': function(e, queueID, fileObj, response, data) {
			if (response && response.slice(0, 2) == "u|") {
				var v = response.slice(2);
				zuf.results[n] += (zuf.results[n].length ? '*' : '') + response;
				var sd = o.scriptData;
				if (sd.old != 'multi') sd.old = v;
				hh.uploadifySettings('scriptData', sd);
				zuf.uploads[n]--;
				if (zuf.uploads[n] == 0) h.val((h.val().length ? (h.val() + '*') : '') + zuf.results[n]);
				var done = true;
				for (k in zuf.uploads) {
					if (zuf.uploads[k] > 0) {
						done = false;
						break;
					}
				}
				if (done) hh.trigger('complete');
			}
			else hh.trigger('error', {queueID: queueID, fileObj: fileObj, errorObj: {type: 'Security', info: response}});
		},
		'onSelectOnce': function(e, d) {
			if (o.scriptData.old != 'multi') {
				var ob = h.prev('div.uploadifyQueue');
				if (ob.find('.uploadifyQueueLoaded').length > 0) {
					ob.find('.uploadifyQueueLoaded').remove();
					h.val('');
				}
			}
			zuf.uploads[n] = d.fileCount;
			zuf.results[n] = '';
			hh.trigger('select');
		},
		'onCancel': function(e, queueID, fileObj, data) {
			hh.trigger('cancel');
		},
		'onError': function(e, queueID, fileObj, errorObj) { hh.trigger('error', {queueID: queueID, fileObj: fileObj, errorObj: errorObj}); },
		'onSelect': function() { if (h.hasClass("zuf_deleted")) hh.prevAll("em").find(">a").click(); }
	};

	hh.uploadify($.extend(oo, o));

	zuf.inited = true;
	
	/*e.find('>a').click(function() {
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
	});*/
};

zuf.add = function(n, title, url, required) {
	if (!zuf.inited) {
		window.setTimeout(function() {
			zuf.add(n, title, url, required);
		}, 100);
		return;
	}
	var i = $('input[name=' + n + '][type=hidden]');
	var v = zuf.explode('*', i.val());
	var exist = false;
	for (k in v) if (v[k].slice(2) == title) {
		exist = true;
		v[k] = title;
		break;
	}
	if (!exist) {
		o = i.prev('div.uploadifyQueue');
		var url_valid = title.search(/^http\:\/\//gi) == -1
			? url + '/' + title
			: title;
		o.append('<div rel="' + title + '" class="uploadifyQueueItem uploadifyQueueLoaded">\
			' + (required ? '' : '<div class="cancel">\
				<a href="javascript:zuf.del(\'' + n + '\', \'' + title + '\')"><img src="/zkernel/ctl/uploadify/cancel.png" border="0" /></a>\
			</div>') + '\
			<span class="fileName"><a target="_blank" href="' + url_valid + '" title="Открыть">' + title + '</a></span><span class="percentage"></span>\
			<div class="uploadifyProgress">\
				<div class="uploadifyProgressBar" style="width:100%;"><!--Progress Bar--></div>\
			</div>\
		</div>');
	}
	i.val(zuf.implode('*', v));
	/*var h = $('input[name=' + n + '][type=hidden]');
	var e = h.prevAll('em');
	e.find('span>a').attr('href', url).html(title);
	e.show();
	h.val(h.val().slice(2));
	if (!required) e.find('>a').show();
	if (h.hasClass('zuf_deleted')) e.find('>a').click();*/
};

zuf.remove = function(n, k) {
	var i = $('input[name=' + n + '][type=hidden]');
	var o = i.prev('div.uploadifyQueue').find('div[rel="' + k + '"]');
	o.remove();
	var v = zuf.explode('*', i.val());
	var vv = o.find('.fileName').text();
	for (kk in v) if (v[kk] == 'd|' + vv) v[kk] = '';
	i.val(zuf.implode('*', v));
};

zuf.del = function(n, k) {
	var i = $('input[name=' + n + '][type=hidden]');
	var o = i.prev('div.uploadifyQueue').find('div[rel="' + k + '"]');
	var v = zuf.explode('*', i.val());
	var vv = o.find('.fileName').text();
	if (o.hasClass('zuf_deleted')) {
		for (kk in v) if (v[kk] == 'd|' + vv) v[kk] = vv;
		o.css({
			'text-decoration': 'none',
			'opacity': 1
		}).removeClass('zuf_deleted');
	}
	else {
		for (kk in v) if (v[kk] == vv) v[kk] = 'd|' + v[kk];
		o.css({
			'text-decoration': 'line-through',
			'opacity': .3
		}).addClass('zuf_deleted');
	}
	i.val(zuf.implode('*', v));
};

zuf.implode = function(g, arr) {
	var str = '';
	for (k in arr) str += arr[k].length ? ((str.length ? g : '') + arr[k]) : '';
	return str;
};

zuf.explode = function(d, str) {
	str += d;
	var dt = [];
	var ln = 0;
	for (var i = 0; i < str.length; i++) {
		if (str[i] == d) {
			dt.push(str.slice(ln, i));
			ln = i + 1;
		}
	}
	return dt;
};

