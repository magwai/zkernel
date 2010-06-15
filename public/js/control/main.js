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
	uploads: {},
	loading: false,
	lang: {
		no_connect: 'Сервер не отвечает',
		no_lang: 'Ошибка загрузки языка',
		try_again: 'Повторить попытку?'
	},
	cfg: {},
	tpl: {},
	timer: null,
	overlay: null
}; 

c.init = function(p) {
	c.cfg = $.extend(c.cfg, p);
	bd = $('body');
	document.title = "";
	php.messages.defaultCallBack = function(msg, params) {
		c.info(msg);
	};
	php.error = function(xmlEr, typeEr, except) {
		c.info('e:' + c.lang['no_response'] + ': ' + c.pre_controller + '/' + c.pre_action);
	};
	php.complete = function (request, status) {
		c.loading_finish();
		$('input[type=button],input[type=submit]').button();
		$('.c_form input:first').focus();
	};
	$.ajax({
		url: (c.cfg.lang ? '/' + c.cfg.lang : '') + '/control/lang',
		dataType: 'json',
		error: function(e) {
			if (confirm(c.lang.no_connect + '\n' + c.lang.try_again)) c.init();
		},
		success: function(data) {
			if (data) {
				c.lang = $.extend(c.lang, data);
				$.ajax({
					url: (c.cfg.lang ? '/' + c.cfg.lang : '') + '/control/config',
					dataType: 'json',
					error: function(e) {
						c.info(c.lang.no_config);
					},
					success: function(data) {
						if (data) {
							c.cfg = $.extend(c.cfg, data);
							$('#c_tl').hide();
							$('.c_tline .h').css('display', 'block');
							document.title = c.cfg.title;
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
									c.go(c.cfg.controller, c.cfg.action, c.cfg.param);
								});
							});
						}
					}
				});
			}
			else if (confirm(c.lang['no_lang'] + '\n' + c.lang['try_again'])) c.init();
		}
	});
};

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
};

c.loading_finish = function() {
	window.clearTimeout(c.timer_l);
	c.loading = false;
	$('#c_loader').hide();
	c.overlay_hide();
	
};

c.load_menu = function(success) {
	$.ajax({
		url: (c.cfg.lang ? '/' + c.cfg.lang : '') + '/control/menu/?' + c.rnd(),
		dataType: 'json',
		success: function(data) {
			$('#c_menu').empty().hide();
			if (data && data.length) {
				c.build_menu(data, $('#c_menu'));
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
};

c.build_menu = function(data, obj) {
	for (k in data) {
		var o = data[k];
		var t = o.t;
		if (t.length) {
			var controller = o.c;
			var action = o.a;
			var param = o.p;
			var inner = o.e;
			if (inner || controller) {
				var o = obj.append('<a />').find('a:last').html(t).attr({'href': c.url_assemble(controller, action)}).data({
					c: controller,
					a: action,
					p: param
				});
				if (inner) {
					var i_o = obj.append('<div />').find('div:last');
					c.build_menu(inner, i_o);
					o.click(function() {
						$(this).next('div:hidden:first').length
							? $(this).next('div:hidden:first').slideDown('fast')
							: $(this).next('div:visible:first').slideUp('fast');
						return false;
					});
				}
				else if (controller) o.click(function() {
					return c.go($(this).data('c'), $(this).data('a'), $(this).data('p'));
				});
			}
		}
	};
};

c.go = function(controller, action, param, post) {
	c.loading_start(true, 1000);
	c.pre_controller = controller;
	c.pre_action = action && action.length ? action : c.cfg.def_action;
	$.php((c.cfg.lang ? '/' + c.cfg.lang : '') + c.url_assemble(controller, action, param), post);
	$('#c_menu_frame').hide();
	return false;
};

c.url_assemble = function(controller, action, param) {
	return controller && controller.length
		? ('/' + controller + '/' + (action && action.length
			? action
			: c.cfg.def_action
		) + c.array2url(param))
		: '#';
};

c.array2url = function(a) {
	var u = '';
	if (a instanceof Object) for (k in a) u += '/' + k + '/' + a[k];
	return u;
};

c.load_auth = function(success) {
	$.ajax({
		url: (c.cfg.lang ? '/' + c.cfg.lang : '') + '/control/auth?' + c.rnd(),
		dataType: 'json',
		success: function(data) {
			$('#c_login').empty();
			var login = data.login;
			if (login.length && login != 'none') $('#c_login').append(login + ' &nbsp;&nbsp;<span><input class="c_l_button" type="button" value="Выйти" /></span>').find('input').click(c.logout);
			else {
				$('#c_login').append(c.tpl['c_auth']).find('form input[type="submit"]').click(function() {
					return c.login($('#c_login').find('form').serialize(), c.cfg.controller, c.cfg.action);
				});
			}
			if (typeof success != 'undefined') success();
		},
		error: function() {
			c.info(c.lang['no_login']);
		}
	});
};

c.login = function(data, controller, action) {
	c.loading_start(false, 400);
	$('#c_login').fadeOut('fast');
	$.ajax({
		url: (c.cfg.lang ? '/' + c.cfg.lang : '') + '/control/auth/?' + c.rnd(),
		type: 'post',
		dataType: 'json',
		data: data,
		success: function(data) {
			var login = data.login;
			if (login.length && login != 'none') {
				c.load_auth();
				c.load_menu();
				c.go(controller, action);
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
};

c.logout = function() {
	c.loading_start(false, 400);
	$('#c_login').fadeOut('fast');
	$.ajax({
		url: (c.cfg.lang ? '/' + c.cfg.lang : '') + '/control/auth/?' + c.rnd(),
		type: 'post',
		dataType: 'json',
		data: {
			login: '',
			password: ''
		},
		success: function(data) {
			c.load_auth();
			c.load_menu();
			c.cfg.controller = c.cfg.def_controller;
			c.cfg.action = c.cfg.def_action;
			c.go(c.cfg.controller);
		},
		error: function() {
			c.info(c.lang['no_logout']);
		},
		complete: function() {
			c.loading_finish();
			$('#c_login').fadeIn('fast');
		}
	});
};

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
};

c.overlay_show = function() {
	c.overlay = $('<div />').addClass('c_overlay').prependTo('body').show();
};

c.overlay_hide = function() {
	if (c.overlay) {
		c.overlay.hide().remove();
	}
};

c.do_action = function(obj, parent) {
	var cl = obj.cl;
	var controller = obj.controller;
	var action = obj.action;
	var param = obj.param;
	if (!param) param = {};
	var field = obj.field ? obj.field : 'id';
	var conf = Number(obj.confirm);
	if (conf && !confirm(parent.value + '?')) return;
	var l = $('#list');
	if (l.length) {
		var id = l.getGridParam('selrow');
		if (cl == 't' && c.cfg.controller != controller && l.find('tr[id=' + id + '] .treeclick').length != 0) {
			c.info('Разрешено переходить только в концевые рубрики');
			return false;
		}
		else if (cl == 'f' && c.cfg.controller != controller) {
			var tdds = l.find('tr[id=' + id + ']>td');
			if (Number(tdds.eq(tdds.length - 4).text()) != 0) {
				c.info('Разрешено переходить только в рубрики верхнего уровня');
				return false;
			}
		}
		var ids = l.getGridParam('selarrrow');
		if (!ids || ids.length == 0) ids = [0];
		param[field] = id ? id : 0;
		param[field + 's'] = c.implode(',', ids);
	}
	c.go(controller, action, param);
};

c.implode = function(glue, pieces) {
	return ((pieces instanceof Array) ? pieces.join (glue) : pieces);
};

c.submit = function(apply) {
	//if (typeof tinyMCE != 'undefined') tinyMCE.triggerSave();
	c.loading_start(true);
	var i = $('#c_form .uploadifyQueueItem').not('#c_form .uploadifyQueueLoaded');
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
};

c.sumbit_full = function(apply) {
	var form = $('#c_form');
	apply = typeof apply == 'undefined' ? 0 : apply;
	var post = form.serialize();
	post += (post.length ? '&' : '') + 'cposted=1&is_apply=' + escape(apply);
	c.go(c.cfg.controller, c.cfg.action, {}, post);
	return false;
};

c.mce_save = function(inst) {
	$('#' + inst.id).val(inst.getContent());
};

c.mce_save_event = function(editor_id, elm, command) {
	if (command == 'mceRepaint') {
		var inst = tinyMCE.getInstanceById(editor_id);
		$('#' + editor_id).val(inst.getContent());
	}
};

c.rnd = function() {
	var dt = new Date();
	return dt.getTime();
};

c.table_width = function() {
	var nw = $(window).width() - 43;
	return nw > 954 ? nw : 954;
};

c.table_height = function() {
	$('input[type=button],input[type=submit]').button();
	var th = 0;
	$('.c_text').each(function() {
		th += $(this).get(0).offsetHeight;
	});
	$('.c_button').each(function() {
		th += $(this).get(0).offsetHeight;
	});
	$('.ui-search-toolbar').each(function() {
		th += $(this).get(0).offsetHeight;
	});
	return $(window).height() - th - 105;
};

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
};

c.build_navpane = function(d) {
	var r = '';
	for (k in d) r +=
		(r.length ? ' - ' : '') +
		(d[k].c
			? '<a c="' + (d[k].c ? d[k].c : '') + '" a="' + (d[k].a ? d[k].a : '') + '" p="' + (d[k].p ? d[k].p : '') + '" href="' + c.url_assemble(d[k].c, d[k].a, d[k].p) + '">' + d[k].t + '</a>'
			: d[k].t
		);
	$('#c_navpane').html(r).find('a').click(function() {
		c.go($(this).attr('c'), $(this).attr('a'), $(this).attr('p'));
		return false;
	});
};














