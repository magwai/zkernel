/*
 * by Petko D. Petkov; pdp (architect)
 * http://www.gnucitizen.org
 * http://www.gnucitizen.org/projects/jquery-include/
 */
jQuery.extend({
	/*
	 * included scripts
	 */
	includedScripts: {},
	/*
	 * include timer
	 */
	includeTimer: null,
	
	includeInited: false,

	/*
	 * include
	 */
	include: function (url, onload) {
		if (typeof url != "string" && url.length == 1) url = url[0];
		if (typeof url != "string") {
			var u = url.shift();
			$.include(u, function() {
				$.include(url, onload);
			});
			return;
		}
		var type = 'script';
		var i = url.indexOf('|');
		if (i != -1) {
			type = url.slice(i + 1);
			url = url.slice(0, i);
		}

		if (typeof jQuery.includedScripts[url] != 'undefined') {
			if (typeof onload == 'function') {
				onload.apply(jQuery(jQuery.includedScripts[url]), arguments);
			}
			return;
		}

		jQuery.isReady = false;

		if (jQuery.readyList == null) {
			jQuery.readyList = [];
		}

		var script = document.createElement(type);

		script.type = 'text/' + (type == 'script' ? 'javascript' : 'css');
		script.onload = function () {
			jQuery.includedScripts[url] = script;

			if (typeof onload == 'function') {
				onload.apply(jQuery(script), arguments);
			}
			var isReady = true;
			for (var s in jQuery.includedScripts) {
				if (jQuery.includedScripts[s] == false) {
					isReady = false;
					break;
				}
			}
			if (isReady) $(window).trigger('include_finish', {
				url: url
			});
		};
		script.onreadystatechange = function () {
			if (script.readyState == 'loaded' || script.readyState == 'complete') {
				jQuery.includedScripts[url] = script;

				if (typeof onload == 'function') {
					onload.apply(jQuery(script), arguments);
				}
				var isReady = true;
				for (var s in jQuery.includedScripts) {
					if (jQuery.includedScripts[s] == false) {
						isReady = false;
						break;
					}
				}
				if (isReady) $(window).trigger('include_finish', {
					url: url
				});
			}
		};
		if (type == 'script') script.src = url;
		else {
			script.rel = 'stylesheet';
			script.href = url;
		}

		if (type == 'script') {
			jQuery.includedScripts[url] = false;
			$(window).trigger('include_start', {
				url: url
			});
		}
		document.getElementsByTagName('head')[0].appendChild(script);
		

		if (!jQuery.includeTimer) {
			jQuery.includeTimer = window.setInterval(function () {
				jQuery.ready();
			}, 10);
		}
	}
});

/*
 * replacement of jQuery.ready
 */
jQuery.extend({
	/*
	 * hijack jQuery.ready
	 */
	_ready: jQuery.ready,

	/*
	 * jQuery.ready replacement
	 */
	ready: function () {
		if (!jQuery.includeInited) {
			$('script').each(function() {
				var s = $(this).attr('src');
				if (s) jQuery.includedScripts[s] = true;
			});
			jQuery.includeInited = true;
		}
		isReady = true;

		for (var s in jQuery.includedScripts) {
			if (jQuery.includedScripts[s] == false) {
				isReady = false;
				break;
			}
		}

		if (isReady) {
			window.clearInterval(jQuery.includeTimer);
			jQuery._ready.apply(jQuery, arguments);
		}
	}
});

