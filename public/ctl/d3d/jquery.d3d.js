(function(jQuery) {
	jQuery.fn.extend({
		d3d: function(options) {
			var _t = jQuery(this).show();
			var _thumb = [];
			var _preload = [];
			var _preload_left = 0;
			var _step = 1;
			var _angle = 0;
			var _old_x;
			var _old_y;
			var _rotating = false;
			var _sliding = false;
			var _timer_play = null;
			var _zoom = 0;
			var _zoom_step;
			var _ox;
			var _oy;
			var _odx = 0;
			var _ody = 0;
			var _crop_loader = null;
			var _moving = false;

			var _o_loader;
			var _o_loader_progress;
			var _o_loader_progress_bar;
			var _o_loader_percent;
			var _o_view;
			var _o_screen;
			var _o_crop;
			var _o_toolbar;
			var _o_toolbar_play;
			var _o_toolbar_zoomin;
			var _o_toolbar_zoomout;
			var _o_toolbar_zoom;
			var _o_toolbar_rotateleft;
			var _o_toolbar_rotateright;
			var _o_toolbar_rotate;
			var _o_toolbar_slider;
			var _o_toolbar_slider_drag;
			var _o_mover;
			var _o_mover_drag;

			var opt = jQuery.extend({
				wheel_mode: 'zoom',
				mover_ratio: 0.2,
				zoom_max: 10,
				play_interval: 100,
				play_angle: 10,
				rotate_speed: 0.5,
				rotate_wheel_speed: 5,
				crop_url: null,
				on_loading_start: function() {
					_o_loader.show();
				},
				on_loading: function(p) {
					_o_loader_progress_bar.width(_o_loader_progress.width() * Math.ceil(p) / 100);
					_o_loader_percent.html(Math.ceil(p) + '%');
				},
				on_loading_finish: function() {
					_o_loader.hide();
				}
			}, options ? options : {});

			if (opt.crop_url && opt.crop_url.length && opt.data && opt.data.length) {
				_t.html(
					'<div class="d3d-frame" unselectable="on"><img class="d3d-screen" /><img class="d3d-crop" /><div class="d3d-view" unselectable="on"></div><div class="d3d-mover" unselectable="on"><div class="d3d-mover-fade"></div><div class="d3d-mover-drag" unselectable="on"></div></div><div class="d3d-toolbar" unselectable="on"><div class="d3d-toolbar-fade"></div>' +
						'<div class="d3d-toolbar-pane">' +
							'<a class="d3d-toolbar-button d3d-toolbar-button-play" href="#"><span class="d3d-toolbar-button-icon"></span></a>' +
						'</div>' +
						'<div class="d3d-toolbar-pane">' +
							'<a class="d3d-toolbar-button d3d-toolbar-button-zoomout" href="#"><span class="d3d-toolbar-button-icon"></span></a>' +
							'<div class="d3d-toolbar-slider d3d-toolbar-slider-zoom" unselectable="on"><div class="d3d-toolbar-slider-track" unselectable="on"></div><div class="d3d-toolbar-slider-drag" unselectable="on"></div></div>' +
							'<a class="d3d-toolbar-button d3d-toolbar-button-zoomin" href="#"><span class="d3d-toolbar-button-icon"></span></a>' +
						'</div>' +
						'<div class="d3d-toolbar-pane">' +
							'<a class="d3d-toolbar-button d3d-toolbar-button-rotateleft" href="#"><span class="d3d-toolbar-button-icon"></span></a>' +
							'<div class="d3d-toolbar-slider d3d-toolbar-slider-rotate" unselectable="on"><div class="d3d-toolbar-slider-track" unselectable="on"></div><div class="d3d-toolbar-slider-drag" unselectable="on"></div></div>' +
							'<a class="d3d-toolbar-button d3d-toolbar-button-rotateright" href="#"><span class="d3d-toolbar-button-icon"></span></a>' +
						'</div>' +
					'</div><div class="d3d-loader"><div class="d3d-loader-progress"><div class="d3d-loader-progress-bar"></div></div><div class="d3d-loader-percent"></div></div></div>'
				);

				_o_loader = _t.find('.d3d-loader').hide();
				_o_loader_progress = _o_loader.find('.d3d-loader-progress');
				_o_loader_progress_bar = _o_loader.find('.d3d-loader-progress-bar').width(0);
				_o_loader_percent = _o_loader.find('.d3d-loader-percent').html('0%');
				_o_screen = _t.find('.d3d-screen').attr('src', null).css('dispaly', 'block').hide();
				_o_view = _t.find('.d3d-view');
				_o_crop = _t.find('.d3d-crop').attr('src', null).css('dispaly', 'block').hide();
				_o_toolbar = _t.find('.d3d-toolbar').hide();
				_o_toolbar_play = _o_toolbar.find('.d3d-toolbar-button-play');
				_o_toolbar_zoomin = _o_toolbar.find('.d3d-toolbar-button-zoomin');
				_o_toolbar_zoomout = _o_toolbar.find('.d3d-toolbar-button-zoomout');
				_o_toolbar_zoom = _o_toolbar.find('.d3d-toolbar-slider-zoom');
				_o_toolbar_rotateleft = _o_toolbar.find('.d3d-toolbar-button-rotateleft');
				_o_toolbar_rotateright = _o_toolbar.find('.d3d-toolbar-button-rotateright');
				_o_toolbar_rotate = _o_toolbar.find('.d3d-toolbar-slider-rotate');
				_o_toolbar_slider = _o_toolbar.find('.d3d-toolbar-slider');
				_o_toolbar_slider_drag = _o_toolbar.find('.d3d-toolbar-slider-drag');
				_o_mover = _t.find('.d3d-mover');
				_o_mover_drag = _o_mover.find('.d3d-mover-drag');

				_step = 360 / opt.data.length;
				_preload_left = opt.data.length;
				_zoom_step = (opt.width_full - opt.width_thumb) / (opt.zoom_max - 1);
				_ox = opt.width_thumb / 2;
				_oy = opt.height_thumb / 2;

				opt.on_loading_start();

				function _f_crop_url(d) {
					return	opt.crop_url +
							'?image=' + encodeURIComponent(opt.data[d.num]) +
							(d.width ? '&ow=' + d.width : '') +
							(d.height ? '&oh=' + d.height : '') +
							(d.x ? '&x=' + d.x : '') +
							(d.y ? '&y=' + d.y : '') +
							'&w=' + opt.width_thumb +
							'&h=' + opt.height_thumb +
							(d.resize ? '&resize=1' : '');
				}

				for (var i = 0; i < opt.data.length; i++) {
					var img = new Image();
					$(img).load(function() {
						_preload_left--;
						opt.on_loading((opt.data.length - _preload_left) / opt.data.length * 100);
						if (_preload_left == 0) {
							function _f_angle() {
								while (Math.abs(_angle) >= 360) {
									_angle -= 360 * (_angle ? 1 : -1);
								}
								if (_angle < 0) _angle = 360 + _angle;
							}
							function _f_rotate() {
								_o_screen.attr('src', _thumb[_f_get_num()]);
								_f_crop();
							}
							function _f_get_num() {
								return Math.floor(_angle / _step);
							}
							function _f_move() {
								var w = _o_screen.width();
								var h = _o_screen.height();

								var dx = (w - opt.width_thumb) / -2;
								var dy = (h - opt.height_thumb) / -2;

								var ax = w / opt.width_thumb;
								var ay = h / opt.height_thumb;

								var nx = dx - (_odx * ax);
								var ny = dy - (_ody * ay);

								if (nx > 0) nx = 0;
								if (ny > 0) ny = 0;
								if (nx < opt.width_thumb - w) nx = opt.width_thumb - w;
								if (ny < opt.height_thumb - h) ny = opt.height_thumb - h;

								var mx = parseInt(_o_mover_drag.css('left'));
								var my = parseInt(_o_mover_drag.css('top'));
								if (mx < 0) _o_mover_drag.css('left', 0);
								if (my < 0) _o_mover_drag.css('top', 0);
								var mmx = _o_mover.width() - _o_mover_drag.width() - 1;
								var mmy = _o_mover.height() - _o_mover_drag.height() - 1;
								if (mx > mmx) _o_mover_drag.css('left', mmx);
								if (my > mmy) _o_mover_drag.css('top', mmy);

								_o_screen.css({
									left: nx,
									top: ny
								});
							}
							function _f_zoom() {
								var nw = Math.floor(opt.width_thumb + _zoom_step * _zoom);
								var nh = Math.floor(opt.height_thumb + _zoom_step * _zoom);

								_o_screen.width(nw).height(nh);

								if (_zoom) {
									_o_mover.show();
									_f_mover_init();
								}
								else _o_mover.hide();

								_f_move();

								_f_crop();
							}
							function _f_slider_set(o, v) {
								var old = _f_slider_get(o);
								var drag = o.find('.d3d-toolbar-slider-drag');
								var steps = o.data('steps');
								var width = o.width();
								if (v < 0) v = 0;
								if (v > steps - 1) v = steps - 1;
								var step = Math.floor(width / (steps - 1));
								drag.css('left', v * Math.floor(width / (steps - 1)) - Math.floor(drag.width() / 2)).show();

								if (old != v) o.trigger('change');
							}
							function _f_slider_get(o) {
								var drag = o.find('.d3d-toolbar-slider-drag');
								var steps = o.data('steps') - 1;
								var width = o.width();
								var step = Math.floor(width / steps);
								var left = parseInt(drag.css('left')) + Math.floor(drag.width() / 2);
								var v = Math.round(left / step);
								if (v < 0) v = 0;
								if (v > steps) v = steps - 1;
								return v;
							}
							function _f_crop() {
								_o_crop.hide();
								if (_crop_loader) _crop_loader.onload = '';
								if (!opt.crop_url || _timer_play || _zoom == 0 || _rotating || _sliding || _moving) return;

								_crop_loader = new Image();
								var url = _f_crop_url({
									num: _f_get_num(),
									x: Math.abs(parseInt(_o_screen.css('left'))),
									y: Math.abs(parseInt(_o_screen.css('top'))),
									width: _o_screen.width(),
									height: _o_screen.height()
								});
								_crop_loader.onload = function() {
									_o_crop.attr('src', url);
									_o_crop.show();
								};
								_crop_loader.src = url;
							}
							function _f_mover_init() {
								var w = Math.floor(opt.width_thumb * opt.mover_ratio);
								var h = Math.floor(opt.height_thumb * opt.mover_ratio);
								var ax = opt.width_thumb / _o_screen.width();
								var ay = opt.height_thumb / _o_screen.height();
								var dx = Math.floor(w * ax);
								var dy = Math.floor(h * ay);
								_o_mover.width(w).height(h);
								_o_mover_drag.width(dx).height(dy).css({
									left: (_ox + _odx) * opt.mover_ratio - dx / 2,
									top: (_oy + _ody) * opt.mover_ratio - dy / 2
								});
							}

							opt.on_loading_finish();
							_o_toolbar.show();
							_o_screen.show();
							_f_rotate();

							_o_toolbar_zoom.data({
								steps: opt.zoom_max
							});
							_f_slider_set(_o_toolbar_zoom, _zoom);

							_o_toolbar_rotate.data({
								steps: _thumb.length
							});
							_f_slider_set(_o_toolbar_rotate, _f_get_num());

							_o_view.mousedown(function(e) {
								var o = $(this).offset();
								_old_x = e.clientX - o.left + $(window).scrollLeft();
								_rotating = true;
								return false;
							});

							_o_mover_drag.mousedown(function(e) {
								var o = $(this).offset();
								_old_x = e.clientX - o.left + $(window).scrollLeft();
								_old_y = e.clientY - o.top + $(window).scrollTop();
								_moving = true;
								return false;
							});

							_o_toolbar_slider_drag.mousedown(function(e) {
								var t = $(this);
								var slider = t.parents('.d3d-toolbar-slider:first');
								var o = slider.offset();
								slider.data({
									x: e.clientX - o.left + $(window).scrollLeft(),
									v: _f_slider_get(slider)
								});
								_sliding = slider;
								return false;
							});

							_o_toolbar_slider.change(function(e, d) {
								var t = $(this);
								if (t.hasClass('d3d-toolbar-slider-zoom')) {
									_zoom = _f_slider_get(t);
									_f_zoom();
								}
								if (t.hasClass('d3d-toolbar-slider-rotate')) {
									_angle = _f_slider_get(t) * _step;
									_f_angle();
									_f_rotate();
								}
							});

							$(document).mouseup(function(e) {
								var changing = _rotating || _sliding || _moving;
								_rotating = false;
								_sliding = false;
								_moving = false;
								if (changing) _f_crop();
							});
							$(document).mousemove(function(e) {
								if (_rotating) {
									if (_o_toolbar_play.hasClass('d3d-toolbar-button-stop')) {
										_o_toolbar_play.click();
									}
									var o = _o_view.offset();
									var nx = e.clientX - o.left + parseInt(_o_view.css('left')) + $(window).scrollLeft();
									var dx = nx - _old_x;
									_old_x = nx;
									_angle -= Math.floor(dx * opt.rotate_speed);
									_f_angle();
									_f_rotate();
									_f_slider_set(_o_toolbar_rotate, _f_get_num());
								}
								if (_sliding) {
									var t = _sliding.find('.d3d-toolbar-slider-drag');
									var o = _sliding.offset();
									var nx = e.clientX - o.left + $(window).scrollLeft();
									var dx = nx - _sliding.data('x');

									var steps = _sliding.data('steps') - 1;
									var width = _sliding.width();
									var step = Math.floor(width / steps);
									var add = Math.round(dx / step);
									_zoom = _f_slider_set(_sliding, _sliding.data('v') + add);
								}
								if (_moving) {
									var o = _o_mover_drag.offset();
									var nx = e.clientX - o.left + $(window).scrollLeft();
									var ny = e.clientY - o.top + $(window).scrollTop();
									var ddx = nx - _old_x;
									var ddy = ny - _old_y;
									var dx = parseInt(_o_mover_drag.css('left')) + ddx;
									var dy = parseInt(_o_mover_drag.css('top')) + ddy;
									if (dx < 0) dx = 0;
									if (dy < 0) dy = 0;
									if (dx > _o_mover.width() - _o_mover_drag.width() - 1) dx = _o_mover.width() - _o_mover_drag.width() - 1;
									if (dy > _o_mover.height() - _o_mover_drag.height() - 1) dy = _o_mover.height() - _o_mover_drag.height() - 1;
									_odx = Math.floor((dx - (_o_mover.width() - _o_mover_drag.width()) / 2) / opt.mover_ratio);
									_ody = Math.floor((dy - (_o_mover.height() - _o_mover_drag.height()) / 2) / opt.mover_ratio);

									_o_mover_drag.css({
										left: dx,
										top: dy
									});
									_f_move();
									_f_crop();
								}
							});
							_o_view.mousewheel(function(e, delta) {
								if (opt.wheel_mode == 'zoom') {
									if (delta == 0) return false;
									_zoom += (Number(delta) > 0 ? -1 : 1);
									if (_zoom < 0) _zoom = 0;
									if (_zoom > opt.zoom_max - 1) _zoom = opt.zoom_max - 1;
									_f_slider_set(_o_toolbar_zoom, _zoom);
								}
								else {
									_angle += Math.floor(delta * opt.rotate_wheel_speed);
									_f_angle();
									_f_rotate();
								}
								return false;
							});
							_o_toolbar_play.click(function() {
								var t = $(this);
								window.clearInterval(_timer_play);
								_timer_play = null;
								if (t.hasClass('d3d-toolbar-button-play')) {
									_timer_play = window.setInterval(function() {
										_angle += opt.play_angle;
										_f_angle();
										_f_rotate();
										_f_slider_set(_o_toolbar_rotate, _f_get_num());
									}, opt.play_interval);
									t.removeClass('d3d-toolbar-button-play').addClass('d3d-toolbar-button-stop');
								}
								else {
									t.removeClass('d3d-toolbar-button-stop').addClass('d3d-toolbar-button-play');
									_f_crop();
								}
								return false;
							});
							_o_toolbar_zoomin.click(function() {
								if (_zoom == opt.zoom_max - 1) return false;
								_zoom++;
								_f_slider_set(_o_toolbar_zoom, _zoom);
								return false;
							});
							_o_toolbar_zoomout.click(function() {
								if (_zoom == 0) return false;
								_zoom--;
								_f_slider_set(_o_toolbar_zoom, _zoom);
								return false;
							});
							_o_toolbar_rotateleft.click(function() {
								if (_o_toolbar_play.hasClass('d3d-toolbar-button-stop')) {
									_o_toolbar_play.click();
								}
								_angle -= _step;
								_f_angle();
								_f_rotate();
								_f_slider_set(_o_toolbar_rotate, _f_get_num());
								return false;
							});
							_o_toolbar_rotateright.click(function() {
								if (_o_toolbar_play.hasClass('d3d-toolbar-button-stop')) {
									_o_toolbar_play.click();
								}
								_angle += _step;
								_f_angle();
								_f_rotate();
								_f_slider_set(_o_toolbar_rotate, _f_get_num());
								return false;
							});
						}
					});
					_preload.push(img);
					var url = _f_crop_url({
						num: i,
						resize: 1
					});
					_thumb.push(url);
					img.src = url;
				}
			}
		}
	});
})(jQuery);