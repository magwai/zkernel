(function(jQuery) {
	jQuery.fn.extend({
		blink: function(options) {
			var _t = jQuery(this);

			if (_t.data('blink_blinking')) return;

			var opt = jQuery.extend({
				'css': {},
				'duration': 1000,
				'delay_in': 1,
				'delay_out': 1,
				'count': 'infinite',
				'first': true,
				'callback_in_before': null,
				'callback_in_after': null,
				'callback_out_before': null,
				'callback_out_after': null
			}, options ? options : {});

			var css_old = {};
			for (k in opt.css) css_old[k] = _t.css(k);
			_t.data('css_old', css_old);

			var count = opt.count;
			if (count) _t.data('blink_count', count);
			else count = _t.data('blink_count');

			if (count) _t.data('blink_blinking', true);
			else return;

			if (opt.callback_in_before) opt.callback_in_before(_t);
			_t.animate(opt.css, opt.duration, function() {
				if (opt.callback_in_after) opt.callback_in_after(_t);
				window.setTimeout(function() {
					if (opt.callback_out_before) opt.callback_out_before(_t);
					_t.animate(css_old, opt.duration, function() {
						if (opt.callback_out_after) opt.callback_out_after(_t);
						if (count != 'infinite') _t.data('blink_count', count - 1);
						opt.count = 0;
						_t.data('blink_blinking', false);
						window.setTimeout(function() {
							opt.first = false;
							_t.blink(opt);
						}, opt.delay_out);
					});
				}, opt.delay_in);
			});
		},
		blink_stop: function() {
			var _t = jQuery(this);
			_t.data('blink_count', 0);
			var css = _t.data('css_old');
			if (css && typeof css == 'object') _t.css(css);
		}
	});
})(jQuery);