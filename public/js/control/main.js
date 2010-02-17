/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

c = {
	sel_block: false,
	drag: [],
	timer_l: null,
	timer_u: null,
	uploads: {}
}; 

c.init = function(bd) {
	bd = typeof bd == 'undefined' || !bd ? $('body') : bd;
	document.title = "";
	c.loading = false;
	c.lang = {
		no_connect: 'Сервер не отвечает',
		no_lang: 'Ошибка загрузки языка',
		try_again: 'Повторить попытку?'
	};
	c.cfg = {};
	c.tpl = {};
	c.timer = null;
	c.overlay = null;
	c.controller = '';
	c.action = '';
	c.param = '';
	$.ajax({
		url: '/control/lang/',
		dataType: 'xml',
		error: function(e) {
			if (confirm(c.lang['no_connect'] + '\n' + c.lang['try_again'])) c.init();
		},
		success: function(xml) {
			if ($(xml).find('d').find('*').length) {
				$(xml).find('d').find('*').each(function() {
					c.lang[$(this).get(0).tagName] = $(this).text();
				});
				$.ajax({
					url: '/control/config/',
					dataType: 'xml',
					error: function(e) {
						c.info(c.lang['no_config']);
					},
					success: function(xml) {
						if ($(xml).find('d').find('*').length) {
							$('#c_tl').hide();
							$('.c_tline .h').css('display', 'block');
							$(xml).find('d').find('*').each(function() {
								c.cfg[$(this).get(0).tagName] = $(this).text();
							});
							document.title = c.cfg['title'];
							c.tpl['c_auth'] = $('#c_login').html();
							$('#c_mlink').click(function() {
								var o = $('#c_menu_frame');
								$('#c_menu_frame:visible').length
									? o.slideUp('fast')
									: o.slideDown('fast');
								return false;
							});
							$('body').click(function() {
								$('#c_menu_frame').hide();
							});
							$(window).unbind("resize").resize(function() {
								var l = $("#list");
								if (l.length && l.setGridHeight && l.setGridWidth) l.setGridHeight(c.table_height()).setGridWidth(c.table_width());
							});
							$(window).bind('include_start', function(e, d) {
								c.loading_start(true, 400);
							}).bind('include_finish', function(e, d) {
								c.loading_finish();
							});
							c.load_auth(function() {
								c.load_menu(function() {
									c.go(c.cfg['controller'], c.cfg['action'], c.cfg['param']);
								});
							});
						}
					}
				});
			}
			else if (confirm(c.lang['no_lang'] + '\n' + c.lang['try_again'])) c.init();
		}
	});
}
c.loading_start = function(lock, delay) {
	if (c.loading) return;
	window.clearTimeout(c.timer_l);
	if (delay) {
		c.timer_l = window.setTimeout('c.loading_start(' + Number(lock) + ')', delay);
		return;
	}
	$('#c_loader').show();
	if (typeof lock != 'undefined' && lock) c.overlay_show();
	c.loading = true;
}
c.loading_finish = function() {
	window.clearTimeout(c.timer_l);
	c.loading = false;
	$('#c_loader').hide();
	c.overlay_hide();
	
}
c.load_menu = function(success) {
	$.ajax({
		url: '/control/menu/?' + c.rnd(),
		dataType: 'xml',
		success: function(xml) {
			$('#c_menu').empty().hide();
			if ($(xml).find('d').children('e').length) {
				c.build_menu($(xml).find('d').children('e'), $('#c_menu'));
				$('.c_tline .mf').show();
			}
			else $('.c_tline .mf').hide();
			$('#c_menu').fadeIn('fast');
			if (typeof success != 'undefined') success();
		},
		error: function() {
			c.info(c.lang['no_menu']);
			$('.c_tline .mf').hide();
		}
	});
}
c.build_menu = function(data, obj) {
	data.each(function() {
		var t = $(this).attr('t');
		if (t.length) {
			var controller = $(this).attr('c');
			var action = $(this).attr('a');
			var param = $(this).attr('p');
			var inner = $(this).children('e');
			if (inner.length || controller) {
				var o = obj.append('<a />').find('a:last').html(t).attr('href', '#');
				if (inner.length) {
					var i_o = obj.append('<span />').find('span:last');
					c.build_menu(inner, i_o);
					o.click(function() {
						$(this).next('span:hidden:first').length
							? $(this).next('span:hidden:first').slideDown('fast')
							: $(this).next('span:visible:first').slideUp('fast');
						return false;
					});
				}
				else if (controller) o.click(function() {
					return c.go(controller, action, param);
				});
			}
		}
	});
}
c.go = function(controller, action, param) {
	param = typeof param == 'undefined' ? '' : param;
	action = typeof action == 'undefined' ? '' : action;
	noempty = typeof noempty == 'undefined' ? false : noempty;
	c.loading_start(true, 1000);
	/*if (!noempty) {
		$('#c_content').empty();
		$('#c_navpane').empty();
	}*/
	php.messages.defaultCallBack = function(msg, params) {
		c.info(msg);
	}
	php.error = function(xmlEr, typeEr, except) {
		c.info('e:' + c.lang['no_response'] + ': ' + controller + (action ? '/' + action : ''));
	};
	php.complete = function (request, status) {
		c.loading_finish();
		if (typeof callback != 'undefined') callback();
		$('.c_form input:first').focus();
		//jfu.init('c_form');
	};
	post =	'_controller=' + controller +
			(action.length ? '&_action=' + action : '') +
			(param.length ? '&' : '') + param;
	$.php('/control/router/', post);
	$('#c_menu_frame').hide();
	return false;
}
c.load_auth = function(success) {
	$.ajax({
		url: '/control/auth/?' + c.rnd(),
		dataType: 'xml',
		success: function(xml) {
			$('#c_login').empty();
			var login = $(xml).find('l').text();
			if (login.length && login != 'none') $('#c_login').append(login + ' &nbsp;&nbsp;<span><input type="button" value="Выйти" /></span>').find('input').click(c.logout);
			else {
				$('#c_login').append(c.tpl['c_auth']).find('form input[type="submit"]').click(function() {
					return c.login($('#c_login').find('form').serialize(), c.cfg['controller'], c.cfg['action'], c.cfg['param']);
				});
			}
			if (typeof success != 'undefined') success();
		},
		error: function() {
			c.info(c.lang['no_login']);
		}
	});
}
c.login = function(data, controller, action, param) {
	c.loading_start(false, 400);
	$('#c_login').fadeOut('fast');
	$.ajax({
		url: '/control/auth/?' + c.rnd(),
		type: 'post',
		dataType: 'xml',
		data: data,
		success: function(xml) {
			var login = $(xml).find('l').text();
			if (login.length && login != 'none') {
				c.load_auth();
				c.load_menu();
				c.go(controller, action, param);
			}
			else c.info(c.lang['no_login_err']);
		},
		error: function() {
			c.info(c.lang['no_loggin']);
		},
		complete: function() {
			c.loading_finish();
			$('#c_login').fadeIn('fast');
		}
	});
	return false;
}
c.logout = function() {
	c.loading_start(false, 400);
	$('#c_login').fadeOut('fast');
	$.ajax({
		url: '/control/auth/?' + c.rnd(),
		type: 'post',
		dataType: 'xml',
		data: {
			login: '',
			password: ''
		},
		success: function(xml) {
			c.load_auth();
			c.load_menu();
			c.cfg['controller'] = c.cfg['def_controller'];
			c.cfg['action'] = c.cfg['def_action'];
			c.cfg['param'] = c.cfg['def_param'];
			c.go(c.cfg['def_controller'], c.cfg['def_action'], c.cfg['def_param']);
		},
		error: function() {
			c.info(c.lang['no_logout']);
		},
		complete: function() {
			c.loading_finish();
			$('#c_login').fadeIn('fast');
		}
	});
}

c.info = function(str) {
	window.clearTimeout(c.timer);
	if (typeof str != 'undefined' && String(str).length > 0) {
		var t = str.slice(1, 2);
		if (t == ':') {
			t = str.slice(0, 1);
			str = str.slice(2);
		}
		else t = 'i';
		$('#c_info').html('<span class="ui-icon ui-icon-' + (t == 'i' ? 'info' : 'alert') + '"></span>' + str).removeClass('ui-state-highlight').removeClass('ui-state-error').addClass(t == 'i' ? 'ui-state-highlight' : 'ui-state-error').fadeIn('fast', function() {
			c.timer = window.setTimeout("$('#c_info').fadeOut('fast')", 4000);
			$('body').click(function() {
				window.clearTimeout(c.timer);
				$('body').unbind('click');
				$('#c_info').fadeOut('fast');
			});
		});
	}
}
c.overlay_show = function() {
	c.overlay = $('<div />').addClass('c_overlay').prependTo('body').show();
}
c.overlay_hide = function() {
	if (c.overlay) {
		c.overlay.hide().remove();
	}
}

c.do_action = function(obj, parent) {
	var cl = obj.cl;
	var controller = obj.controller;
	var action = obj.action;
	var param = obj.param;
	var field = obj.field ? obj.field : 'id';
	var conf = Number(obj.confirm);
	if (conf && !confirm(parent.value + '?')) return;
	var l = $('#list');
	if (l.length) {
		var id = l.getGridParam('selrow');
		if (cl == 't' && c.controller != controller && l.find('tr[id=' + id + '] .treeclick').length != 0) {
			c.info('Разрешено переходить только в концевые рубрики');
			return false;
		}
		else if (cl == 'f' && c.controller != controller) {
			var tdds = l.find('tr[id=' + id + ']>td');
			if (Number(tdds.eq(tdds.length - 4).text()) != 0) {
				c.info('Разрешено переходить только в рубрики верхнего уровня');
				return false;
			}
		}
		var ids = l.getGridParam('selarrrow');
		param += (param.length ? '&' : '') + '_' + field + '=' + (id ? id : 0);
		var p = '';
		if (ids) for (var i = 0; i < ids.length; i++) p += (p.length ? ',' : '') + ids[i];
		param += (param.length ? '&' : '') + '_' + field + 's=' + (p.length ? p : 0);
	}
	c.go(
		controller,
		action,
		param
	);
}
c.url_sign = function(url) {
	return url.indexOf('?') == -1 ? '?' : '&';
}
c.init_table = function() {
	var def = $('#c_list input[default="1"]:first');
	$('#c_list table tbody tr').click(function() {
		if (c.sel_block) {
			c.sel_block = false;
			return;
		}
		$('#c_list table tr').removeClass('c_current');
		if ($(this).hasClass('c_selected')) {
			$(this).removeClass('c_selected');
			var next = $(this).nextAll('tr.c_selected:first');
			if (next.length) next.addClass('c_current');
			else $(this).prevAll('tr.c_selected:first').addClass('c_current');
		}
		else $(this).addClass('c_current').addClass('c_selected');
		//return false;
	}).dblclick(def.length
		? function() {
			$(this).addClass('c_current').addClass('c_selected');
			def.click();
		}
		: function() {
			$('#c_list table tr').removeClass('c_current');
			var cnt_all = $('#c_list table tr').length;
			var cnt_sel = $('#c_list table tr.c_selected').length;
			if (cnt_all == cnt_sel) $('#c_list table tr').removeClass('c_selected').removeClass('c_current');
			else {
				$('#c_list table tr').addClass('c_selected');
				$(this).addClass('c_current');
			}
			return false;
		}
	).find('*').attr('unselectable', 'on');
	$('#c_filter form').submit(function() {
		var qr = $(this).find('input:first').val();
		if (qr) {
			qr = qr.replace(/(\&|\=)/gi, '');
			c.go(c.url + c.url_sign(c.url) + 'cpqr=' + escape(qr))
		}
		else {
			window.scroll(0, 0);
			c.info(c.lang['no_filter']);
		}
		return false;
	});
	if (typeof $.tableDnD != 'undefined') $('#c_list table.drag').tableDnD({
		onDragClass: 'c_drag',
		onDragStart: function(t, r) {
			c.drag = $(r).prev('tr:first').attr('id');
		},
		onDrop: function(t, r) {
			var d = $(r).prev('tr:first').attr('id');
			if (d != c.drag) {
				c.sel_block = true;
				//var ids = '';
				$('#c_list table tr').each(function(i) {
					$(this).find('td:first').html(i);
					//if ($(this).hasClass('c_selected') && $(this).attr('id') != r.id) ids += (';' + $(this).attr('id'));
				});
				c.go(c.url + c.url_sign(c.url) + 'action=move&dir=drag&id=' + d + '&ids=' + r.id/* + ids*/, "", true);
			}
			return false;
		}
	});
}
c.implode = function(glue, pieces) {
	return ((pieces instanceof Array) ? pieces.join (glue) : pieces);
}
c.submit = function(apply) {
	if (typeof tinyMCE != 'undefined') tinyMCE.triggerSave();
	c.loading_start(true);
	var i = $('#c_form .uploadifyQueueItem');
	if (i.length == 0) c.sumbit_full(apply);
	else {
		i.each(function() {
			var n = $(this).attr('id');
			n = n.slice(0, n.length - 6);
			var inp = $('#c_form input[name=' + n + '][type=file]');
			inp.unbind('complete').bind('complete', function() {
				c.sumbit_full(apply);
			});
			inp.unbind('error').bind('error', function(e, d) {
				var error = '';
				var response = d.errorObj.info;
				var ln = 0;
				response += '|';
				for (k = 0; k < response.length; k++) {
					if (response[k] == '|') {
						error += (error.length == 0 ? '' : '<br />') + d.fileObj.name + ': ' + response.slice(ln, k);
						ln = k + 1;
					}
				}
				c.info(error);
				window.scroll(0, 0);
				c.loading_finish();
			});
			inp.uploadifyUpload();
		});
	}
	return false;
}
c.sumbit_full = function(apply) {
	var form = $('#c_form');
	apply = typeof apply == 'undefined' ? 0 : apply;
	var post = form.serialize();
	post += (post.length ? '&' : '') + 'cposted=1&is_apply=' + escape(apply);
	c.go(c.controller, c.action, post);
	return false;
}
c.init_catalog = function(open) {
	$('.c_catalog div').unbind().click(function() {
		var was = $(this).hasClass('c_current_cat');
		$('.c_catalog div').removeClass('c_current_cat');
		if (!was) $(this).addClass('c_current_cat');
		return false;
	}).dblclick(function() {
		$(this).addClass('c_current_cat');
		return c.load_catalog($(this).attr('id'));
	});
	c.open_catalog(open);
}
c.load_catalog = function(id, callback) {
	if (c.loading) return false;
	var obj = $('.c_catalog div[id="' + id + '"]');
	var inner = obj.next('.c_inner:first');
	if (inner.length > 0) {
		inner.slideUp('fast', function() {
			$(this).prev('.c_branch:first').removeClass('c_branch_open');
			$(this).remove();
		});
	}
	else c.go(c.url + c.url_sign(c.url) + 'cd=' + id, '', true, callback);
	return false;
}
c.open_catalog = function(open) {
	if (open.length > 0) {
		var op = open[0];
		open.shift();
		if (op) {
			c.loading = false;
			c.load_catalog(op, function() {
				c.open_catalog(open);
			});
		}
	}
}
c.mce_save = function(inst) {
	$('#' + inst.id).val(inst.getContent());
}
c.mce_save_event = function(editor_id, elm, command) {
	if (command == 'mceRepaint') {
		var inst = tinyMCE.getInstanceById(editor_id);
		$('#' + editor_id).val(inst.getContent());
	}
}
c.table_filter = function(o) {
	var u = '';
	var o = $('[name^="fqr"]').each(function() {
		var v = escape($(this).val());
		u += (u.length == 0 ? '' : '|') + (v.length == 0 ? '*' : v);
	});
	c.go(c.url + (u.length == 0 ? '' : c.url_sign(c.url) + 'cpqr=' + u));
}
c.rnd = function() {
	var dt = new Date();
	return dt.getTime();
}
c.table_width = function() {
	return $(window).width() - 43;
}
c.table_height = function() {
	var th = 0;
	$('.c_text').each(function() {
		th += $(this).get(0).offsetHeight;
	});
	$('.c_button').each(function() {
		th += $(this).get(0).offsetHeight;
	});
	return $(window).height() - th - 105;
}
c.parse_url = function(c) {
	var p = c.indexOf('?');
	if (p == -1) p = '';
	else {
		var temp = p;
		p = c.slice(p + 1);
		c = c.slice(0, temp);
	}
	var a = c.indexOf('/');
	if (a == -1) a = '';
	else {
		var temp = a;
		a = c.slice(a + 1);
		c = c.slice(0, temp);
	}
	return {
		controller: c,
		action: a,
		param: p
	};
}

c.formatter = function(row, cm) {
	var d = {};
	var n = 0;
	for (var i = 0; i < cm.length; i++) {
		var nm = cm[i].name;
		if ( nm !== 'cb' && nm !== 'subgrid' && nm !== 'rn') {
			d[nm] = row[n];
			n++;
		}
	}
	return d;
}

c.build_navpane = function(d) {
	var r = '';
	for (k in d) r +=
		(r.length ? ' - ' : '') +
		(d[k].c
			? '<a href="#" c="' + d[k].c + '" a="' + (d[k].a ? d[k].a : '') + '" p="' + (d[k].p ? d[k].p : '') + '">' + d[k].t + '</a>'
			: d[k].t
		);
	$('#c_navpane').html(r).find('a').click(function() {
		c.go($(this).attr('c'), $(this).attr('a'), $(this).attr('p'));
		return false;
	});
}














