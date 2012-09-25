(function(jQuery) {
	jQuery.fn.extend({
		linch: function(options) {
			t = jQuery(this);
			opt = jQuery.extend({
				'render_initial': true,
				'event_add': 'dblclick',
				'event_spike_add': 'dblclick',
				'mode': 'edit',
				'data': [],
				'toolbar': [
					'text',
					'line',
					'curve'
				],
				'color_text': ['#f7f4dd', '#ffe5e5', '#eeffec', '#ecf4ff'],
				'color_line': ['#ff6262', '#116d0f', '#2f3cbc'],
				'color_delete': '#ff0000',
				'color_modify': '#0000ff',
				'cx': 100,
				'cy': 37,
				'padding_inner': 10,
				'padding': 5,
				'spike_radius': 10,
				'cb_change': function() {},
				'current': null
			}, options ? options : {});
			var t_height = t.height();
			var t_width = t.width();
			wrap = t
				.addClass('linch_frame')
				.css({
					'left': '0px',
					'top': '0px'
				})
				.wrap('<div class="linch_wrapper" style="width:' + (t_width - opt.padding * 2) + 'px;height:' + (t_height - opt.padding * 2) + 'px;"></div>')
				.parent('.linch_wrapper')
				.css({
					'padding': opt.padding + 'px'
				});
			wrap.append('<div class="paper_wrap" style="width:' + t_width + 'px;height:' + t_height + 'px;"></div>');
			paper_wrap = wrap.find('.paper_wrap').css({
				'z-index': parseInt(t.css('z-index')),
				'position': 'absolute',
				'left': '0px',
				'top': '0px'
			});
			paper = Raphael(paper_wrap[0], t_width, t_height);
			if (opt.event_add) paper_wrap.bind(opt.event_add, t.linch_add);

			if (opt.mode == 'edit' && opt.toolbar && opt.toolbar.length) {
				wrap.append('<div class="linch_toolbar"></div>');
				toolbar = wrap.find('.linch_toolbar');

				var color_line = '';
				if (opt.color_line) {
					color_line += '<div class="linch_toolbar_color_popup">';
					for (k in opt.color_line) color_line += '<div class="linch_toolbar_color_el" style="background-color:' + opt.color_line[k] + ';"></div>';
					color_line += '</div>';
				}

				var color_text = '';
				if (opt.color_text) {
					color_text += '<div class="linch_toolbar_color_popup">';
					for (k in opt.color_text) color_text += '<div class="linch_toolbar_color_el" style="background-color:' + opt.color_text[k] + ';"></div>';
					color_text += '</div>';
				}

				for (k = 0; k < opt.toolbar.length; k++) {
					if (opt.toolbar[k] == 'text') {
						toolbar.append('<div class="linch_toolbar_el linch_toolbar_el_text">' +
							'<div title="Комментарий" class="linch_toolbar_button"></div>' +
							'<div class="linch_hidden">' +
								'<div title="Выбрать цвет" class="linch_toolbar_color">' +
									'<div class="linch_toolbar_color_button"></div>' +
									color_text +
								'</div>' +
							'</div>' +
						'</div>');
					}
					else if (opt.toolbar[k] == 'line') {
						toolbar.append(
							'<div class="linch_toolbar_el linch_toolbar_el_line">' +
								'<div title="Прямая линия" class="linch_toolbar_button"></div>' +
								'<div class="linch_hidden">' +
									'<div title="Выбрать цвет" class="linch_toolbar_color">' +
										'<div class="linch_toolbar_color_button"></div>' +
										color_line +
									'</div>' +
									'<div title="Задать ширину линии" class="linch_toolbar_width"><input maxlength="1" type="text" value="5" /></div>' +
									'<div title="Добавить стрелку" class="linch_toolbar_arrow"><input type="checkbox" /></div>' +
								'</div>' +
							'</div>'
						);
					}
					else if (opt.toolbar[k] == 'curve') {
						toolbar.append(
							'<div class="linch_toolbar_el linch_toolbar_el_curve">' +
								'<div title="Кривая" class="linch_toolbar_button"></div>' +
								'<div class="linch_hidden">' +
									'<div title="Выбрать цвет" class="linch_toolbar_color">' +
										'<div class="linch_toolbar_color_button"></div>' +
										color_line +
									'</div>' +
									'<div title="Задать ширину линии" class="linch_toolbar_width"><input maxlength="1" type="text" value="5" /></div>' +
									'<div title="Добавить стрелку" class="linch_toolbar_arrow"><input type="checkbox" /></div>' +
								'</div>' +
							'</div>'
						);
					}
				}
				$('.linch_toolbar_color_el').click(function() {
					$(this).parents('.linch_toolbar_color').find('.linch_toolbar_color_button').css({
						'background-color': $(this).css('background-color')
					});
					return false;
				});
				$('.linch_toolbar_color').hover(function() {
					$(this).find('.linch_toolbar_color_popup').show();
				}, function() {
					$(this).find('.linch_toolbar_color_popup').hide();
				}).each(function() {
					$(this).find('.linch_toolbar_color_el:first').click();
					$(this).find('.linch_toolbar_color_popup').hide();
				});
				$('.linch_toolbar_width input').change(function() {
					t.linch_correct_width($(this));
				}).keyup(function() {
					t.linch_correct_width($(this));
				}).bind('paste', function() {
					t.linch_correct_width($(this));
				}).mousedown(function() {
					$(this).focus().select();
					return false;
				});

				toolbar.find('.linch_toolbar_button').click(function() {
					var c = $(this).parent()[0].className.replace('linch_toolbar_el ', '');
					wrap.removeClass();
					wrap.addClass('linch_wrapper ' + c);
					toolbar.find('.linch_toolbar_el').removeClass('linch_toolbar_active');
					$(this).parent().addClass('linch_toolbar_active');
					t.linch_action_deinit();
					if (wrap.hasClass('linch_toolbar_el_line')) t.linch_line_init();
					else if (wrap.hasClass('linch_toolbar_el_curve')) t.linch_curve_init();
					t.linch_render(opt.data);
					return false;
				});
				toolbar.find('.linch_toolbar_button:first').click();
			}
			if (typeof opt.data[0] != 'object') opt.data = [];
			if (opt.render_initial) t.linch_render(opt.data);
			else data = opt.data;
		},
		linch_correct_width: function(t) {
			var val = Number(t.val());
			if (val < 1 || isNaN(val) || val.length == 0) val = 1;
			if (val > 9) val = 9;
			t.val(val);
		},
		linch_action_deinit: function() {
			paper_wrap.unbind('mousedown');
			paper_wrap.unbind('mousemove');
			paper_wrap.unbind('mouseup');
			paper_wrap.unbind('mouseleave');
		},
		linch_correct_coord: function(x, y) {
			var w = t.width();
			var h = t.height();
			if (x < opt.padding) x = opt.padding;
			if (x > (w - opt.padding)) x = w - opt.padding;
			if (y < opt.padding) y = opt.padding;
			if (y > (h - opt.padding)) y = h - opt.padding;
			return [x, y];
		},
		linch_line_init: function() {
			paper_wrap.bind('mousedown', function(e) {
				var x = null;
				var y = null;
				if (typeof x == 'object') {
					var o = wrap.offset();
					y = e.pageY - o.top;
					x = e.pageX - o.left;
				}
				var xy = t.linch_correct_coord(x, y);
				opt.ox = xy[0];
				opt.oy = xy[1];
				opt.current = null;
				opt.was_downed = true;
				return false;
			});
			paper_wrap.bind('mousemove', function(e) {
				var x = null;
				var y = null;
				if (typeof x == 'object') {
					var o = wrap.offset();
					y = e.pageY - o.top;
					x = e.pageX - o.left;
				}
				var xy = t.linch_correct_coord(x, y);
				x = xy[0];
				y = xy[1];
				if (opt.dragging) {
					opt.ox = x;
					opt.oy = y;
					opt.current = null;
					opt.was_downed = false;
				}
				else {
					if (opt.current != null) {
						//if (Math.abs(x - data[opt.current].cx) > 5 || Math.abs(y - data[opt.current].cy) > 5) {
							data[opt.current].cx = x;
							data[opt.current].cy = y;
							opt.cb_change();
							t.linch_render(data);
							opt.drawing = true;
						//}
					}
					else if (opt.was_downed && opt.ox && opt.oy && (Math.abs(opt.ox - x) > 3 || Math.abs(opt.oy - y) > 3)) {
						data = typeof data != 'array' && typeof data != 'object' ? [] : data;
						data.push({
							'type': 'line',
							'x': opt.ox,
							'y': opt.oy,
							'cx': x,
							'cy': y,
							'z': t.linch_zindex_max() + 1,
							'width': $('.linch_toolbar .linch_toolbar_el_line .linch_toolbar_width input').val(),
							'finished': 0,
							'color': $('.linch_toolbar .linch_toolbar_el_line .linch_toolbar_color_button').css('background-color'),
							'arrow': $('.linch_toolbar .linch_toolbar_el_line .linch_toolbar_arrow input')[0].checked ? 1 : 0
						});
						opt.ox = null;
						opt.oy = null;
						opt.current = data.length - 1;
					}
				}
			});
			paper_wrap.bind('mouseup', function() {
				if (opt.current) {
					data[opt.current].finished = 1;
					t.linch_render(data);
				}
				opt.ox = null;
				opt.oy = null;
				opt.current = null;
				opt.was_downed = false;
				opt.drawing = false;
			});
			paper_wrap.bind('mouseleave', function() {
				paper_wrap.mouseup();
			});
		},
		linch_curve_init: function() {
			paper_wrap.bind('mousedown', function(e) {
				var x = null;
				var y = null;
				if (typeof x == 'object') {
					var o = wrap.offset();
					y = e.pageY - o.top;
					x = e.pageX - o.left;
				}
				var xy = t.linch_correct_coord(x, y);
				opt.ox = xy[0];
				opt.oy = xy[1];
				opt.current = null;
				opt.was_downed = true;
			});
			paper_wrap.bind('mousemove', function(e) {
				var x = null;
				var y = null;
				if (typeof x == 'object') {
					var o = wrap.offset();
					y = e.pageY - o.top;
					x = e.pageX - o.left;
				}
				var xy = t.linch_correct_coord(x, y);
				x = xy[0];
				y = xy[1];
				if (opt.dragging) {
					opt.ox = x;
					opt.oy = y;
					opt.current = null;
					opt.was_downed = false;
				}
				else {
					if (opt.current != null) {
						if (Math.abs(x - data[opt.current].coord[data[opt.current].coord.length - 1][0]) > 5 || Math.abs(y - data[opt.current].coord[data[opt.current].coord.length - 1][1]) > 5) {
							data[opt.current].coord.push([x, y]);
							opt.cb_change();
							t.linch_render(data);
						}
					}
					else if (opt.was_downed && opt.ox && opt.oy && (Math.abs(opt.ox - x) > 3 || Math.abs(opt.oy - y) > 3)) {
						data = typeof data != 'array' && typeof data != 'object' ? [] : data;
						data.push({
							'type': 'curve',
							'coord': [[opt.ox, opt.oy]],
							'z': t.linch_zindex_max() + 1,
							'width': $('.linch_toolbar .linch_toolbar_el_curve .linch_toolbar_width input').val(),
							'finished': 0,
							'color': $('.linch_toolbar .linch_toolbar_el_curve .linch_toolbar_color_button').css('background-color'),
							'arrow': $('.linch_toolbar .linch_toolbar_el_curve .linch_toolbar_arrow input')[0].checked ? 1 : 0
						});
						opt.ox = null;
						opt.oy = null;
						opt.current = data.length - 1;
						opt.drawing = true;
					}
				}
			});
			paper_wrap.bind('mouseup', function() {
				if (opt.current != null) {
					data[opt.current].finished = 1;
					t.linch_render(data);
				}
				opt.ox = null;
				opt.oy = null;
				opt.current = null;
				opt.was_downed = false;
				opt.drawing = false;
			});
			paper_wrap.bind('mouseleave', function() {
				paper_wrap.mouseup();
			});
		},
		linch_remove: function() {
			if (typeof wrap != 'undefined' && wrap) {
				wrap.after(t);
				wrap.remove();
				wrap = null;
				t.css('position', 'relative');
			}
		},
		linch_add: function(x, y) {
			if (opt.mode != 'edit' || !wrap.hasClass('linch_toolbar_el_text')) return false;
			var w = t.width();
			var h = t.height();
			if (typeof x == 'object') {
				var o = wrap.offset();
				y = x.pageY - o.top;
				x = x.pageX - o.left;
			}
			if (!x) x = 0;
			if (x > (w - opt.cx - opt.padding)) x = w - opt.cx - opt.padding;
			if (!y) y = 0;
			if (y > (h - opt.cy - opt.padding)) y = h - opt.cy - opt.padding;
			data = typeof data != 'array' && typeof data != 'object' ? [] : data;
			data.push({
				'type': 'text',
				'text': '',
				'x': x,
				'y': y,
				'cx': opt.cx,
				'cy': opt.cy,
				'z': t.linch_zindex_max() + 1,
				'color': $('.linch_toolbar .linch_toolbar_el_text .linch_toolbar_color_button').css('background-color'),
				'spikes': []
			});
			opt.cb_change();
			t.linch_render(data);
			return false;
		},
		linch_delete: function(ind) {
			data[ind] = null;
			opt.cb_change();
			t.linch_render(data);
		},
		linch_clear: function() {
			wrap.find('.linch_object').remove();
			paper.clear();
		},
		linch_render: function(dt) {
			if (typeof dt != 'undefined') data = dt;
			t.linch_clear();
			var zmax = t.linch_zindex_max();
			for (k = 0; k < data.length; k++) {
				if (!data[k]) continue;
				//if (opt.mode != 'edit' && !color.find('.linch_color_el').eq(data[k].color).data('selected')) continue;
				if (data[k]['type'] == 'curve') {
					t.linch_render_curve(k);
				}
				else if (data[k]['type'] == 'line') {
					t.linch_render_line(k);
				}
				else {
					wrap.append('<div class="linch_object">' + (opt.mode == 'edit' && wrap.hasClass('linch_toolbar_el_text') ? '<div class="ui-icon ui-icon-close linch_object_close"></div>' : '') + '<div class="linch_object_inner"><textarea class="linch_object_text"></textarea></div></div>');
					var o = wrap.find('.linch_object:last').data('index', k).css({
						'left': data[k].x,
						'top': data[k].y,
						'width': data[k].cx,
						'height': data[k].cy,
						'z-index': zmax + data[k].z,
						'background': data[k].color
					});
					var tc = o.find('.linch_object_close');
					var to = o.find('.linch_object_inner');
					var tt = o.find('.linch_object_text')
					if (opt.mode == 'edit' && wrap.hasClass('linch_toolbar_el_text')) {
						o.mousedown(function() {
							var i1 = jQuery(this).data('index');
							if (!data[i1]) return;
							data[i1].z = t.linch_zindex_max() + 1;
							jQuery(this).css('z-index', data[i1].z);
						}).draggable({
							'containment': 'parent',
							'drag': function(event, ui) {
								var i1 = jQuery(this).data('index');
								data[i1].x = ui.position.left;
								data[i1].y = ui.position.top;
								t.linch_render_spikes(i1);
							}
						}).resizable({
							'minWidth': opt.cx,
							'minHeight': opt.cy,
							'containment': 'parent',
							'resize': function(event, ui) {
								var i1 = jQuery(this).data('index');
								var ok = true;
								for (k11 = 0; k11 < data[i1].spikes.length; k11++) {
									if (!data[i1].spikes[k11]) continue;
									var align1 = data[i1].spikes[k11].align;
									var o1 = data[i1].spikes[k11].o;
									if (((align1 == 'top' || align1 == 'bottom') && (o1 + opt.spike_radius) > ui.size.width)
										||
										((align1 == 'left' || align1 == 'right') && (o1 + opt.spike_radius) > ui.size.height)
									) ok = false;
								}
								var t1 = jQuery(this);
								var bs = t1.data('bs');
								if (!bs) bs = {
									width: ui.originalSize.width,
									height: ui.originalSize.height
								};
								if (ok) {
									var to = jQuery(this).find('.linch_object_text');
									to.height((ui.size.height - opt.padding_inner * 2) + 'px');
									bs.width = data[i1].cx = ui.size.width;
									bs.height = data[i1].cy = ui.size.height;
									t.linch_render_spikes(i1);
									t1.data('bs', bs);
								}
								else {
									ui.size.width = bs.width;
									ui.size.height = bs.height;
								}
							}
						});
						tc.css({
							'position': 'absolute',
							'right': '-2px',
							'top': '-2px'
						}).click(function() {
							var o = jQuery(this).parent('.linch_object');
							t.linch_delete(o.data('index'));
							return false;
						});
						to.bind(opt.event_spike_add, function(e) {
							var o = jQuery(this).parent('.linch_object');
							var ind = o.data('index');
							var d = data[ind];
							var w = wrap.offset();
							var x = e.pageX - w.left - d.x;
							var sr = opt.spike_radius;
							if (x < sr) x = sr;
							if (x > (d.cx - sr)) x = d.cx - sr;
							var y = e.pageY - w.top - d.y;
							if (y < opt.sr) y = sr;
							if (y > (d.cy - sr)) y = d.cy - sr;
							var offset = '';
							var align = '';
							var ox = 0;
							var oy = 0;
							if (y <= opt.padding_inner) {
								align = 'top';
								offset = x;
								ox = 0;
								oy = -40;
							}
							else if (y >= (d.cy - opt.padding_inner)) {
								align = 'bottom';
								offset = x;
								ox = 0;
								oy = 40;
							}
							else if (x <= opt.padding_inner) {
								align = 'left';
								offset = y;
								ox = -40;
								oy = 0;
							}
							else if (x >= (d.cx - opt.padding_inner)) {
								align = 'right';
								offset = y;
								ox = 40;
								oy = 0;
							}
							if (align.length) t.linch_spike_add(ind, align, offset, ox, oy);
							return false;
						});
						tt.change(function() {
							var o = jQuery(this).parents('.linch_object:first');
							data[o.data('index')].text = jQuery(this).val();
							opt.cb_change();
						});
					}
					else {
						tt.attr('readonly', 'readonly');
					}
					to.css({
						'padding': opt.padding_inner + 'px'
					});
					tt.css({
						'height': (data[k].cy - opt.padding_inner * 2) + 'px'
					}).val(data[k].text);
					t.linch_render_spikes(k);
				}
			}
		},
		linch_zindex_max: function() {
			var z = parseInt(t.css('z-index'));
			for (k1 = 0; k1 < data.length; k1++) {
				if (!data[k1]) continue;
				if (data[k1].z > z) z = data[k1].z;
			}
			return z;
		},
		linch_render_line: function(i) {
			var d = data[i];
			if (typeof d.group != 'undefined' && d.group) {
				try {d.group.remove();} catch (e) {}
				d.group = null;
			}
			d.group = paper.set();
			var x = Math.floor(d.x);
			var y = Math.floor(d.y);
			var cx = Math.floor(d.cx);
			var cy = Math.floor(d.cy);
			var spike = t.linch_line_draw(i, x, y, cx, cy, d.z, d.width, d.color, d.arrow);
			if (opt.mode == 'edit' && (wrap.hasClass('linch_toolbar_el_line') || wrap.hasClass('linch_toolbar_el_curve')) && d.finished != 0) {
				var circ = paper.circle(x, y, 10);
				circ.attr({fill: opt.color_delete, stroke: 'none', 'zIndex': d.z + 1, opacity: 0});
				circ.drag(function(dx, dy) {
					opt.dragging = true;
					var tt = jQuery(this.node);
					var tt1 = jQuery(tt.data('circ'));
					var ind = tt.data('ind');
					var xy = t.linch_correct_coord(this.ox + dx, this.oy + dy);
					var xn = xy[0];
					var yn = xy[1];
					this.attr({cx: xn, cy: yn});
					var spike = tt.data('spike');
					spike.remove();
					spike = t.linch_line_draw(
						ind,
						xn,
						yn,
						tt.data('cx'),
						tt.data('cy'),
						tt.data('z'),
						tt.data('width'),
						tt.data('color'),
						tt.data('arrow')
					);
					tt.data('spike', spike);
					tt1.data('spike', spike);
					tt.data('group').push(spike);
					data[ind].x = xn ;
					data[ind].y = yn;
					opt.cb_change();
				}, function() {
					this.ox = this.attr('cx');
					this.oy = this.attr('cy');
				}, function() {
					opt.dragging = false;
					var tt = jQuery(this.node);
					var tt1 = jQuery(tt.data('circ'));
					var ind = tt.data('ind');
					tt.data({
						'x': data[ind].x,
						'y': data[ind].y,
						'cx': data[ind].cx,
						'cy': data[ind].cy
					});
					tt1.data({
						'x': data[ind].x,
						'y': data[ind].y,
						'cx': data[ind].cx,
						'cy': data[ind].cy
					});
				});
				jQuery(circ.node).data({
					'arrow': d.arrow,
					'group': d.group,
					'spike': spike,
					'ind': i,
					'x': x,
					'y': y,
					'cx': cx,
					'cy': cy,
					'z': d.z,
					'width': d.width,
					'color': d.color
				}).hover(function() {
					if (opt.dragging || opt.drawing) return false;
					jQuery(this).data('spike').attr({
						stroke: opt.color_modify,
						fill: opt.color_modify
					});
					return false;
				}, function() {
					if (opt.dragging || opt.drawing) return false;
					var tt = jQuery(this);
					tt.data('spike').attr({
						stroke: tt.data('color'),
						fill: tt.data('color')
					});
					return false;
				});

				var circ1 = paper.circle(cx, cy, 10);
				circ1.attr({fill: opt.color_delete, stroke: 'none', 'zIndex': d.z + 1, opacity: 0});
				circ1.drag(function(dx, dy) {
					opt.dragging = true;
					var tt = jQuery(this.node);
					var tt1 = jQuery(tt.data('circ'));
					var ind = tt.data('ind');
					var xy = t.linch_correct_coord(this.ox + dx, this.oy + dy);
					var xn = xy[0];
					var yn = xy[1];
					this.attr({cx: xn, cy: yn});
					var spike = tt.data('spike');
					spike.remove();
					spike = t.linch_line_draw(
						ind,
						tt.data('x'),
						tt.data('y'),
						xn,
						yn,
						tt.data('z'),
						tt.data('width'),
						tt.data('color'),
						tt.data('arrow')
					);
					tt.data('spike', spike);
					tt1.data('spike', spike);
					tt.data('group').push(spike);
					data[ind].cx = xn;
					data[ind].cy = yn;
					opt.cb_change();
				}, function() {
					this.ox = this.attr('cx');
					this.oy = this.attr('cy');
				}, function() {
					opt.dragging = false;
					var tt = jQuery(this.node);
					var tt1 = jQuery(tt.data('circ'));
					var ind = tt.data('ind');
					tt.data({
						'x': data[ind].x,
						'y': data[ind].y,
						'cx': data[ind].cx,
						'cy': data[ind].cy
					});
					tt1.data({
						'x': data[ind].x,
						'y': data[ind].y,
						'cx': data[ind].cx,
						'cy': data[ind].cy
					});
				});
				jQuery(circ1.node).data({
					'arrow': d.arrow,
					'group': d.group,
					'spike': spike,
					'ind': i,
					'x': x,
					'y': y,
					'cx': cx,
					'cy': cy,
					'z': d.z,
					'width': d.width,
					'color': d.color
				}).hover(function() {
					if (opt.dragging || opt.drawing) return false;
					jQuery(this).data('spike').attr({
						stroke: opt.color_modify,
						fill: opt.color_modify
					});
					return false;
				}, function() {
					if (opt.dragging || opt.drawing) return false;
					var tt = jQuery(this);
					tt.data('spike').attr({
						stroke: tt.data('color'),
						fill: tt.data('color')
					});
					return false;
				});

				jQuery(circ1.node).data({
					'circ': circ.node
				});
				jQuery(circ.node).data({
					'circ': circ1.node
				});

				d.group.push(circ);
				d.group.push(circ1);
			}
			d.group.push(spike);
		},
		linch_render_curve: function(i) {
			var d = data[i];
			if (typeof d.group != 'undefined' && d.group) {
				try {d.group.remove();} catch (e) {}
				d.group = null;
			}
			d.group = paper.set();
			var p = 'M';
			var x = 0;
			var y = 0;
			var cx = 0;
			var cy = 0;
			for (var k = 0; k < d.coord.length; k++) {
				if (k == d.coord.length - 1) {
					cx = d.coord[k][0];
					cy = d.coord[k][1];
				}
				else if (k == d.coord.length - 2) {
					x = d.coord[k][0];
					y = d.coord[k][1];
				}
				p += (k ? 'L' : '') + d.coord[k][0] + ' ' + d.coord[k][1];
			}
			var spike = paper.path(p);
			spike.attr({fill: 'none', stroke: d.color, 'zIndex': d.z, 'stroke-width': d.width});
			d.group.push(spike);

			if (d.arrow) {
				var angle = Math.atan2(x - cx, cy - y);
				angle = (angle / (2 * Math.PI)) * 360;
				var size = d.width * 2;
				arrow = paper.path('M' + cx + ' ' + cy + ' L' + (cx - size - size) + ' ' + (cy - size + (d.width * 0.6)) + ' L' + (cx - size - size) + ' ' + (cy + size - (d.width * 0.6)) + ' L' + cx + ' ' + cy).rotate((90 + angle), cx, cy);
				arrow.attr({fill: d.color, stroke: 'none', 'zIndex': d.z});
				var xx = -(7 * d.width / 5) * Math.sin((2 * Math.PI) / 360 * angle);
				var yy = 7 * d.width / 5 * Math.cos((2 * Math.PI) / 360 * angle);
				arrow.translate(xx, yy);
				d.group.push(arrow);
			}

			if (opt.mode == 'edit' && (wrap.hasClass('linch_toolbar_el_line') || wrap.hasClass('linch_toolbar_el_curve')) && d.finished != 0) d.group.hover(function() {
				if (opt.drawing || opt.dragging) return false;
				d.group[0].attr({
					stroke: opt.color_delete
				});
				if (d.group[1]) d.group[1].attr({
					fill: opt.color_delete
				});
				return false;
			}, function() {
				if (opt.drawing || opt.dragging) return false;
				d.group[0].attr({
					stroke: d.color
				});
				if (d.group[1]) d.group[1].attr({
					fill: d.color
				});
				return false;
			}).dblclick(function() {
				t.linch_curve_delete(i);
				return false;
			});
			d.group.drag(function(dx, dy) {
				opt.dragging = true;
			}, function() {}, function() {
				opt.dragging = false;
			});


		},
		linch_line_draw: function(i, x, y, cx, cy, z, width, color, arrow) {
			var gr = paper.set();
			var spike = paper.path('M' + x + ' ' + y + 'L' + cx + ' ' + cy);
			spike.attr({fill: 'none', stroke: color, 'zIndex': z, 'stroke-width': width});
			gr.push(spike);

			if (arrow) {
				var angle = Math.atan2(x - cx, cy - y);
				angle = (angle / (2 * Math.PI)) * 360;
				var size = width * 2;
				arrow = paper.path('M' + cx + ' ' + cy + ' L' + (cx - size - size) + ' ' + (cy - size + (width * 0.6)) + ' L' + (cx - size - size) + ' ' + (cy + size - (width * 0.6)) + ' L' + cx + ' ' + cy).rotate((90 + angle), cx, cy);
				arrow.attr({fill: color, stroke: 'none', 'zIndex': z});
				var xx = -(7 * width / 5) * Math.sin((2 * Math.PI) / 360 * angle);
				var yy = 7 * width / 5 * Math.cos((2 * Math.PI) / 360 * angle);
				arrow.translate(xx, yy);
				gr.push(arrow);
			}

			if (opt.mode == 'edit' && (wrap.hasClass('linch_toolbar_el_line') || wrap.hasClass('linch_toolbar_el_curve'))) gr.hover(function() {
				if (opt.dragging || opt.drawing) return false;
				for (k in gr) $(gr[k].node).attr({
					stroke: opt.color_delete,
					fill: opt.color_delete
				});
				return false;
			}, function() {
				if (opt.dragging || opt.drawing) return false;
				for (k in gr) $(gr[k].node).attr({
					stroke: color,
					fill: color
				});
				return false;
			}).dblclick(function() {
				t.linch_line_delete(i);
				return false;
			});
			gr.drag(function(dx, dy) {
				opt.dragging = true;
			}, function() {}, function() {
				opt.dragging = false;
			});

			return gr;
		},
		linch_line_delete: function(ind) {
			data[ind] = null;
			opt.cb_change();
			t.linch_render(data);
		},
		linch_curve_delete: function(ind) {
			data[ind] = null;
			opt.cb_change();
			t.linch_render(data);
		},
		linch_render_spikes: function(i) {
			ind = i;
			var d = data[ind];
			var ds = d.spikes;
			if (typeof d.group != 'undefined' && d.group) {
				try {d.group.remove();} catch (e) {}
				d.group = null;
			}
			d.group = paper.set();
			for (k1 = 0; k1 < ds.length; k1++) {
				if (!ds[k1]) continue;
				var x0, y0, x01, y01, x02, y02;
				if (ds[k1].align == 'top') {
					x0 = parseInt(d.x) + parseInt(ds[k1].o);
					x01 = x0 - opt.spike_radius;
					x02 = x0 + opt.spike_radius;
					y0 = y01 = y02 = parseInt(d.y);
				}
				else if (ds[k1].align == 'bottom') {
					x0 = d.x + parseInt(ds[k1].o);
					x01 = x0 - opt.spike_radius;
					x02 = x0 + opt.spike_radius;
					y0 = y01 = y02 = parseInt(d.y) + parseInt(d.cy);
				}
				else if (ds[k1].align == 'left') {
					x0 = x01 = x02 = parseInt(d.x);
					y0 = parseInt(d.y) + parseInt(ds[k1].o);
					y01 = y0 - opt.spike_radius;
					y02 = y0 + opt.spike_radius;
				}
				else if (ds[k1].align == 'right') {
					x0 = x01 = x02 = parseInt(d.x) + parseInt(d.cx);
					y0 = parseInt(d.y) + parseInt(ds[k1].o);
					y01 = y0 - opt.spike_radius;
					y02 = y0 + opt.spike_radius;
				}
				var x = x0 + parseInt(ds[k1].x);
				var y = y0 + parseInt(ds[k1].y);
				var spike = t.linch_spike_draw(ind, k1, x01, y01, x02, y02, x, y, d.z, d.color);
				if (opt.mode == 'edit' && wrap.hasClass('linch_toolbar_el_text')) {
					var circ = paper.circle(x, y, 10);
					circ.attr({fill: opt.color_delete, stroke: 'none', opacity: 0});
					circ.drag(function(dx, dy) {
						var tt = jQuery(this.node);
						var x = this.ox + dx;
						var y = this.oy + dy;
						this.attr({cx: x, cy: y});
						var spike = tt.data('spike');
						var st = jQuery(spike.node);
						var o = st.data('object')
						var i = st.data('ind')
						spike.remove();
						spike = t.linch_spike_draw(
							o,
							i,
							tt.data('x01'),
							tt.data('y01'),
							tt.data('x02'),
							tt.data('y02'),
							x,
							y,
							tt.data('z'),
							tt.data('color')
						);
						tt.data('spike', spike);
						tt.data('group').push(spike);
						data[o].spikes[i].x = x - tt.data('x0');
						data[o].spikes[i].y = y - tt.data('y0');
						opt.cb_change();
					}, function() {
						this.ox = this.attr('cx');
						this.oy = this.attr('cy');
					}, function() {});
					jQuery(circ.node).data({
						'group': d.group,
						'spike': spike,
						'x0': x0,
						'y0': y0,
						'x01': x01,
						'y01': y01,
						'x02': x02,
						'y02': y02,
						'z': d.z,
						'color': d.color
					}).hover(function() {
						jQuery(this).data('spike').attr({
							fill: opt.color_modify
						});
						return false;
					}, function() {
						var tt = jQuery(this);
						tt.data('spike').attr({
							fill: tt.data('color')
						});
						return false;
					});
					d.group.push(circ);
				}
				d.group.push(spike);
			}
		},
		linch_spike_draw: function(o, i, x01, y01, x02, y02, x, y, z, color) {
			var spike = paper.path(	'M' + x01 + ' ' + y01 +
					'L' + x02 + ' ' + y02 +
					'L' + x + ' ' + y);
			spike.attr({fill: color, stroke: 'none', 'zIndex': z});
			spike.attr('class', 'linch_object_spike');
			var node = jQuery(spike.node).data({
				'object': o,
				'ind': i
			});
			if (opt.mode == 'edit' && wrap.hasClass('linch_toolbar_el_text')) node.hover(function() {
				jQuery(this).attr({
					fill: opt.color_delete
				});
				return false;
			}, function() {
				jQuery(this).attr({
					fill: color
				});
				return false;
			}).dblclick(function() {
				var tt = jQuery(this);
				t.linch_spike_delete(tt.data('object'), tt.data('ind'));
				return false;
			});
			return spike;
		},
		linch_spike_delete: function(o, i) {
			data[o].spikes[i] = null;
			opt.cb_change();
			t.linch_render_spikes(o);
		},
		linch_spike_add: function(o, align, offset, ox, oy) {
			data[o].spikes.push({
				align: align,
				o: offset,
			    x: ox,
			    y: oy
			});
			opt.cb_change();
			t.linch_render_spikes(o);
		},
		linch_get: function() {
			var ret = [];
			var minz = 999999;
			for (k = 0; k < data.length; k++) {
				if (!data[k]) continue;
				if (data[k].z < minz) minz = data[k].z;
			}
			minz = minz <= 1 ? 0 : (minz - 1);
			for (k = 0; k < data.length; k++) {
				if (!data[k]) continue;
				var td = data[k];
				if (td['type'] == 'curve') {
					ret.push({
						type: 'curve',
						coord: td.coord,
						z: td.z - minz,
						width: td.width,
						color: td.color,
						arrow: td.arrow
					});
				}
				else if (td['type'] == 'line') {
					ret.push({
						type: 'line',
						x: td.x,
						y: td.y,
						cx: td.cx,
						cy: td.cy,
						z: td.z - minz,
						width: td.width,
						color: td.color,
						arrow: td.arrow
					});
				}
				else {
					var s = [];
					for (k1 = 0; k1 < data[k].spikes.length; k1++) {
						if (!data[k].spikes[k1]) continue;
						var ts = data[k].spikes[k1];
						s.push({
							align: ts.align,
							o: ts.o,
							x: ts.x,
							y: ts.y
						});
					}
					ret.push({
						x: td.x,
						y: td.y,
						z: td.z - minz,
						cx: td.cx,
						cy: td.cy,
						color: td.color,
						text: td.text,
						spikes: s
					});
				}
			}
			return ret;
		}
	});
})(jQuery);