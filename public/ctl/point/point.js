/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

var point = {};

point.init = function(id, value, opt) {
	var o = $('#point_' + id);
	var url = opt.url;
	var color = opt.color ? opt.color : '#ff0000';
	var type = opt.type ? opt.type : 'point';

	var i = $('<img id="point_' + id + '_i" alt="" style="cursor:crosshair;" />').click(function(e) {
		var st = typeof window.pageYOffset == 'undefined'
			? (typeof document.documentElement.scrollTop == 'undefined'
				? document.scrollTop
				: document.documentElement.scrollTop
			)
			: window.pageYOffset;
		var sl = typeof window.pageXOffset == 'undefined'
			? (typeof document.documentElement.scrollLeft == 'undefined'
				? document.scrollLeft
				: document.documentElement.scrollLeft
			)
			: window.pageYOffset;

		var f = $(this).offset();
		var x = e.clientX - f.left + sl;
		var y = e.clientY - f.top + st;

		if (type == 'point') point.set_point(id, x, y);
		else if (type == 'rect') point.set_rect(id, x, y);
		else if (type == 'poly') point.set_poly(id, x, y);
		return false;
	}).load(function() {
		if (value) {
			if (type == 'point') point.set_point(id, value);
			else if (type == 'rect') point.set_rect(id, value);
			else if (type == 'poly') point.set_poly(id, value);
		}
	}).attr('src', url);
	o.append(i);

	if (type == 'point') o.prepend($('<div title="Удалить отметку" id="point_' + id + '_p" style="overflow:hidden;width:6px;height:6px;position:absolute;display:none;cursor:pointer;background-color:' + color + ';" />').click(function() {
		point.set_point(id, -1, -1);
	}));
	else if (type == 'rect') o.prepend($('<div title="Удалить область" id="point_' + id + '_p" style="overflow:hidden;position:absolute;display:none;cursor:pointer;background-color:' + color + ';border:1px solid ' + color + ';opacity:.5;filter:alpha(opacity=50);" />').click(function() {
		point.set_rect(id, -1, -1);
	}).data('x', -1).data('y', -1));
	else if (type == 'poly')
		o.prepend($('<div title="Закончить фигуру" id="point_' + id + '_p" style="overflow:hidden;width:6px;height:6px;position:absolute;display:none;cursor:pointer;background-color:' + color + ';" color_original="' + color + '" />').click(function() {
			point.set_poly_finish(id);
		})).prepend($('<img title="Удалить область" id="point_' + id + '_pa" style="position:absolute;display:none;cursor:pointer;opacity:.5;filter:alpha(opacity=50);" />').click(function() {
			point.set_poly(id, -1, -1);
		}));
};

point.set_point = function(id, x, y) {
	var d = $('#' + id);
	var p = $('#point_' + id + '_p');
	if (typeof x == 'Array') {
		y = x[1];
		x = x[0];
	}
	if (x == -1 || y == -1) {
		d.val('');
		p.hide();
	}
	else {
		p.show();
		x = document.body.scrollLeft + x;
		y = document.body.scrollTop + y;
		var po = p.get(0);
		var xx = x - po.offsetWidth / 2;
		var yy = y - po.offsetHeight / 2;
		p.css({
			marginLeft: xx,
			marginTop: yy
		});
		d.val(x + "|" + y);
	}
};

point.set_rect = function(id, x, y) {
	var d = $('#' + id);
	var p = $('#point_' + id + '_p');
	if (typeof x == 'object') {
		if (x.length == 4) {
			p.data('x', x[0]).data('y', x[1]);
			y = x[3];
			x = x[2];
		}
		else {
			x = -1;
			y = -1;
		}
	}
	if (x == -1 || y == -1) {
		d.val('');
		p.hide().data('x', -1).data('y', -1);
	}
	else {
		var ox = p.data('x');
		var oy = p.data('y');
		x = document.body.scrollLeft + x;
		y = document.body.scrollTop + y;
		p.show();
		if (ox == -1 || oy == -1) {
			p.width(6).height(6);
			var xx = x - p.get(0).offsetWidth / 2;
			var yy = y - p.get(0).offsetHeight / 2;
			p.css({
				marginLeft: xx,
				marginTop: yy
			});
			p.data('x', x).data('y', y);
		}
		else {
			var xx = Math.min(x, ox);
			var yy = Math.min(y, oy);
			var mx = Math.max(x, ox);
			var my = Math.max(y, oy);
			p.css({
				marginLeft: xx,
				marginTop: yy
			}).width(mx - xx).height(my - yy).data('x', -1).data('y', -1);
			d.val(xx + "|" + yy + "|" + mx + "|" + my);
		}
	}
};

point.set_poly = function(id, x, y) {
	var d = $('#' + id);
	var p = $('#point_' + id + '_p');
	var pa = $('#point_' + id + '_pa');
	if (typeof x == 'object') {
		if (x.length > 2) {
			for (k in x) {
				if (typeof x[k] != 'object') {
					x = -1;
					y = -1;
					break;
				}
				point.set_poly(id, x[k][0], x[k][1]);
			}
			point.set_poly_finish(id);
		}
		else {
			x = -1;
			y = -1;
		}
	}
	else {
		if (x == -1 || y == -1) {
			d.val('');
			p.hide();
			pa.hide();
			var o = $('#point_' + id);
			o.find('.point_' + id).remove();
		}
		else {
			var o = $('#point_' + id);
			var nvis = p.css('display') == 'none';
			p.show();
			x = document.body.scrollLeft + x;
			y = document.body.scrollTop + y;
			var xx = x - p.get(0).offsetWidth / 2;
			var yy = y - p.get(0).offsetHeight / 2;
			if (nvis) {
				p.css({
					marginLeft: xx,
					marginTop: yy
				});
				pa.hide();
			}
			else o.prepend($('<div title="Удалить отметку" class="point_' + id + '" style="overflow:hidden;width:6px;height:6px;position:absolute;cursor:pointer;background-color:' + p.attr('color_original') + ';margin-left:' + xx + 'px;margin-top:' + yy + 'px;" />').click(function() {
				$(this).remove();
			}));
		}
	}
};

point.set_poly_finish = function(id) {
	var p = $('#point_' + id + '_p');
	if (p.css('display') == 'block') {
		var o = $('#point_' + id);
		var pts = o.find('.point_' + id);
		if (pts.length > 1) {
			var d = $('#' + id);
			var pa = $('#point_' + id + '_pa');
			
			var w = p.get(0).offsetWidth / 2;
			var h = p.get(0).offsetHeight / 2;
			var v = (parseInt(p.css('margin-left')) + w) + '|' + (parseInt(p.css('margin-top')) + h);
			pts.each(function() {
				var t = $(this);
				v += ';' + (parseInt(t.css('margin-left')) + w) + '|' + (parseInt(t.css('margin-top')) + h);
			});
			d.val(v);
			p.hide();
			pts.remove();
			pa.attr('src', '/z/point/coord/' + v + '/color/' + p.attr('color_original').replace(/\#/gi, '-')).show();
		}
		else point.set_poly(id, -1, -1);
	}
};

