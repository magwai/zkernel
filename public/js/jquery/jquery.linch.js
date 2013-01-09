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
				'color': [{
					title: 'default',
				    color: '#f7f4dd',
				    selected: true
				}],
				'color_show': false,
				'color_current': 0,
				'background_delete': '#ff0000',
				'background_modify': '#0000ff',
				'css': {
					'display': 'block',
					'position': 'absolute',
					'z-index': 2
				},
				'css_wrapper': {
					//'-moz-user-select': 'none',
					'-o-user-select': 'none',
					'-khtml-user-select': 'none',
					'user-select': 'none',
					'position': 'relative',
					'z-index': 1
				},
				'css_close': {
					//'-moz-user-select': 'none',
					'-o-user-select': 'none',
					'-khtml-user-select': 'none',
					'user-select': 'none',
					'cursor': 'pointer'
				},
				'css_object': {
					//'-moz-user-select': 'none',
					'-o-user-select': 'none',
					'-khtml-user-select': 'none',
					'user-select': 'none',
					'position': 'absolute',
					'z-index': 3
				},
				'css_input': {
					'display': 'block',
					'width': '100%',
					'height': '100%',
					'border': '0',
					'background': '0',
					'margin': '0px',
					'padding': '0px',
					'overflow': 'auto',
					'resize': 'none',
					'font-family': 'arial',
					'font-size': '12px',
					'line-height': '16px',
					'color': '#000000',
					'outline': 'none'
				},
				'css_inner': {
					//'-moz-user-select': 'none',
					'-o-user-select': 'none',
					'-khtml-user-select': 'none',
					'user-select': 'none',
					'cursor': 'default'
				},
				'css_color': {
					'-moz-user-select': 'none',
					'-o-user-select': 'none',
					'-khtml-user-select': 'none',
					'user-select': 'none',
					'position': 'absolute',
					'z-index': 2,
					'right': '5px',
					'top': '5px',
					'display': 'none'
				},
				'css_color_el': {
					'-moz-user-select': 'none',
					'-o-user-select': 'none',
					'-khtml-user-select': 'none',
					'user-select': 'none',
					'outline': 'none',
					'width': '20px',
					'height': '20px',
					'margin-bottom': '5px',
					'display': 'block',
					'border': '1px solid #000'
				},
				'cx': 100,
				'cy': 37,
				'padding_inner': 10,
				'padding': 5,
				'spike_radius': 10,
				'cb_change': function() {}
			}, options ? options : {});
			var t_height = t.height();
			var t_width = t.width();
			wrap = t
				.css(jQuery.extend(opt.css, {
					'left': '0px',
					'top': '0px'
				}))
				.wrap('<div class="linch_wrapper" style="width:' + (t_width - opt.padding * 2) + 'px;height:' + (t_height - opt.padding * 2) + 'px;"></div>')
				.parent('.linch_wrapper')
					.css(jQuery.extend(opt.css_wrapper, {
						'padding': opt.padding + 'px'
					}));
			wrap.append('<div class="paper_wrap" style="width:' + t_width + 'px;height:' + t_height + 'px;"></div>');
			paper_wrap = wrap.find('.paper_wrap').css({
				'z-index': parseInt(t.css('z-index')),
				'position': 'absolute',
				'left': '0px',
				'top': '0px'
			});
			paper = Raphael(paper_wrap[0], t_width, t_height);
			if (opt.event_add) paper_wrap.bind(opt.event_add, t.linch_add);

			wrap.append('<div class="linch_color"></div>');
			color = wrap.find('.linch_color').css(opt.css_color);
			color_current = opt.color_current;
			if (opt.color.length) {
				var cnt = 0;
				for (k = 0; k < opt.color.length; k++) {
					var ex;
					if (opt.mode == 'edit') ex = true;
					else {
						ex = false;
						for (k1 = 0; k1 < opt.data.length; k1++) {
							if (typeof opt.data[k1].color == 'undefined') opt.data[k1].color = color_current;
							if (opt.data[k1].color == k) {
								ex = true;
								break;
							}
						}
					}
					color.append('<a class="linch_color_el" href="#"></a>');
					color.find('.linch_color_el:last')
						.data({
							'index': k,
							'color': opt.color[k].color
						})
						.css(jQuery.extend(opt.css_color_el, {
							'background': opt.color[k].color,
							'opacity': '.5',
							'display': (ex ? 'block' : 'none')
						}))
						.attr('title', opt.color[k].title)
						.click(function() {
							var ind = jQuery(this).data('index');
							if (opt.mode == 'edit') t.linch_color_select(ind);
							else {
								if (jQuery(this).data('selected')) t.linch_color_deselect(ind);
								else t.linch_color_select(ind);
								t.linch_render();
							}
							return false;
						});
					if (opt.mode == 'edit' && k == 0) t.linch_color_select(k);
					if (opt.mode != 'edit' && opt.color[k].selected) t.linch_color_select(k);
					if (ex) cnt++;
				}
				if (cnt < 2) opt.color_show = false;
			}

			if (opt.render_initial) t.linch_render(opt.data);
			else data = opt.data;
		},
		linch_remove: function() {
			if (typeof wrap != 'undefined' && wrap) {
				wrap.after(t);
				wrap.remove();
				wrap = null;
				t.css('position', 'relative');
			}
		},
		linch_color_select: function(ind) {
			var o = color.find('.linch_color_el').eq(ind);
			if (opt.mode == 'edit') color.find('.linch_color_el').not(o).each(function() {
				t.linch_color_deselect(jQuery(this).data('index'));
			});
			o.css('opacity', '1').data('selected', true);
			color_current = ind;
		},
		linch_color_deselect: function(ind) {
			var o = color.find('.linch_color_el').eq(ind);
			o.css('opacity', '.5').data('selected', false);
		},
		linch_add: function(x, y) {
			if (opt.mode != 'edit') return false;
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
			data = typeof data != 'array' ? [] : data;
			data.push({
				'text': '',
				'x': x,
				'y': y,
				'cx': opt.cx,
				'cy': opt.cy,
				'z': t.linch_zindex_max() + 1,
				'color': color_current,
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
			color.hide();
		},
		linch_render: function(dt) {
			if (typeof dt != 'undefined') data = dt;
			t.linch_clear();
			var zmax = t.linch_zindex_max();
			for (k = 0; k < data.length; k++) {
				if (!data[k]) continue;
				if (typeof data[k].color == 'undefined') data[k].color = color_current;
				if (opt.mode != 'edit' && !color.find('.linch_color_el').eq(data[k].color).data('selected')) continue;
				wrap.append('<div class="linch_object">' + (opt.mode == 'edit' ? '<div class="ui-icon ui-icon-close linch_object_close"></div>' : '') + '<div class="linch_object_inner"><textarea class="linch_object_text"></textarea></div></div>');
				var o = wrap.find('.linch_object:last').data('index', k).css(jQuery.extend(opt.css_object, {
					'left': data[k].x,
					'top': data[k].y,
					'width': data[k].cx,
					'height': data[k].cy,
					'z-index': zmax + data[k].z,
					'background': opt.color[data[k].color].color
				}));
				var tc = o.find('.linch_object_close');
				var to = o.find('.linch_object_inner');
				var tt = o.find('.linch_object_text');
				if (opt.mode == 'edit') {
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
					tc.css(jQuery.extend(opt.css_close, {
						'position': 'absolute',
						'right': '-2px',
						'top': '-2px'
					})).click(function() {
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
				to.css(jQuery.extend(opt.css_inner, {
					'padding': opt.padding_inner + 'px'
				}));
				tt.css(jQuery.extend(opt.css_input, {
					'height': (data[k].cy - opt.padding_inner * 2) + 'px'
				})).val(data[k].text);
				t.linch_render_spikes(k);
			}
			if (opt.color_show) color.show();
		},
		linch_zindex_max: function() {
			var z = parseInt(t.css('z-index'));
			for (k1 = 0; k1 < data.length; k1++) {
				if (!data[k1]) continue;
				if (data[k1].z > z) z = data[k1].z;
			}
			return z;
		},
		linch_render_spikes: function(i) {
			ind = i;
			var d = data[ind];
			var ds = d.spikes;
			if (typeof d.group != 'undefined' && d.group) {
				try { d.group.remove(); } catch (e) {}
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
				if (opt.mode == 'edit') {
					var circ = paper.circle(x, y, 10);
					circ.attr({fill: opt.background_delete, stroke: 'none', opacity: 0});
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
						data[o].spikes[i].y = y - tt.data('y0');;
					}, function() {
						this.ox = this.attr('cx');
						this.oy = this.attr('cy');
					}, function() {});
					var node_c = jQuery(circ.node).data({
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
							fill: opt.background_modify
						});
						return false;
					}, function() {
						jQuery(this).data('spike').attr({
							fill: opt.color[d.color].color
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
			spike.attr({fill: opt.color[color].color, stroke: 'none', 'zIndex': z});
			var node = jQuery(spike.node).data({
				'object': o,
				'ind': i
			});
			if (opt.mode == 'edit') node.hover(function() {
				jQuery(this).attr({
					fill: opt.background_delete
				});
				return false;
			}, function() {
				jQuery(this).attr({
					fill: opt.color[color].color
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
				var s = [];
				var td = data[k];
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
					text: td.text,
					color: td.color,
					spikes: s
				});
			}
			return ret;
		}
	});
})(jQuery);