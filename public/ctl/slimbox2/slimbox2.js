/*!
	Slimbox v2.03 - The ultimate lightweight Lightbox clone for jQuery
	(c) 2007-2009 Christophe Beyls <http://www.digitalia.be>
	MIT-style license.
*/

(function($) {

	// Global variables, accessible to Slimbox only
	var win = $(window), options, images, activeImage = -1, activeURL, prevImage, nextImage, compatibleOverlay, middle, centerWidth, centerHeight, ie6 = !window.XMLHttpRequest,
		operaFix = window.opera && (document.compatMode == "CSS1Compat") && ($.browser.version >= 9.3), documentElement = document.documentElement,

	// Preload images
	preload = {}, preloadPrev = new Image(), preloadNext = new Image(),

	// DOM elements
	overlay, center, image, sizer, prevLink, nextLink, bottomContainer, bottom, caption, number;

	/*
		Initialization
	*/

	$(function() {
		// Append the Slimbox HTML code at the bottom of the document
		$("body").append(
			$([
				overlay = $('<div id="lbOverlay" />')[0],
				center = $('<div id="lbCenter" />')[0],
				bottomContainer = $('<div id="lbBottomContainer" />')[0]
			]).css("display", "none")
		);

		image = $('<div id="lbImage" />').appendTo(center).append(
			sizer = $('<div style="position: relative;" />').append([
				prevLink = $('<a id="lbPrevLink" href="#" />').click(previous)[0],
				nextLink = $('<a id="lbNextLink" href="#" />').click(next)[0]
			])[0]
		)[0];

		bottom = $('<div id="lbBottom" />').appendTo(bottomContainer).append([
			$('<a id="lbCloseLink" href="#" />').add(overlay).click(close)[0],
			caption = $('<div id="lbCaption" />')[0],
			number = $('<div id="lbNumber" />')[0],
			$('<div style="clear: both;" />')[0]
		])[0];
	});


	/*
		API
	*/
	$.slimbox_init = function(c, o) {
		c = typeof c == 'undefined' ? null : c;
		o = typeof o == 'undefined' ? {} : o;
		if (!o['linkMapper']) o['linkMapper'] = null;
		if (!o['linksFilter']) o['linksFilter'] = function(el) {
			return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
		};
		$("a[rel^='lightbox']", c).slimbox(o, o['linkMapper'], o['linksFilter']);
		$("a[rel^='videobox']", c).each (function () {
			var p = [];
			var r = $(this).attr('rel');
			var i = r.indexOf('[');
			if (i != -1) {
				r = r.slice(i + 1);
				r = r.replace(/\]/gi, '|');
				var ln = 0;
				for (var i = 0; i < r.length; i++) {
					if (r[i] == '|') {
						p.push(r.slice(ln, i));
						ln = i + 1;
					}
				}
				$(this).slimbox(o, function(el) {
					return ['/zkernel/img/_.gif', el.title];
				}, function(el) {
					$('#lbImage').width(p[2]).height(p[3]);
					$('object#lbObject').remove();
					var o = $('<div id="lbObject" />');
					$('#lbImage').prepend(o);
					new swfobject.embedSWF("/zkernel/swf/uppod.swf", 'lbObject', p[2], p[3], "9.0.0", false, {
						"st": p[1],
						"file": p[0]
					}, {
						wmode: "transparent",
						allowScriptAccess: "always"
					});
					window.setTimeout('$("#lbObject").css("visibility", "visible");', 1000);
					$('#lbCloseLink').add($('#lbOverlay')).unbind('click').click(function() {
						$('#lbObject').remove();
						close();
						return false;
					});
					return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
				}, c);
			}
		});
		$("a[rel^='flashbox']", c).each (function () {
			var p = [];
			var r = $(this).attr('rel');
			var i = r.indexOf('[');
			if (i != -1) {
				r = r.slice(i + 1);
				r = r.replace(/\]/gi, '|');
				var ln = 0;
				for (var i = 0; i < r.length; i++) {
					if (r[i] == '|') {
						p.push(r.slice(ln, i));
						ln = i + 1;
					}
				}
				$(this).slimbox(o, function(el) {
					return ['/zkernel/img/_.gif', el.title];
				}, function(el) {
					$('#lbImage').width(p[1]).height(p[2]);
					$('object#lbObject').remove();
					var o = $('<div id="lbObject" />');
					$('#lbImage').prepend(o);
					new swfobject.embedSWF(p[0], 'lbObject', p[1], p[2], "9.0.0", false, {}, {
						wmode: "transparent",
						allowScriptAccess: "always"
					});
					window.setTimeout('$("#lbObject").css("visibility", "visible");', 1000);
					$('#lbCloseLink').add($('#lbOverlay')).unbind('click').click(function() {
						$('#lbObject').remove();
						close();
						return false;
					});
					return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
				}, c);
			}
		});
	};
	// Open Slimbox with the specified parameters
	$.slimbox = function(_images, startImage, _options) {
		options = $.extend({
			loop: false,				// Allows to navigate between first and last images
			overlayOpacity: 0.8,			// 1 is opaque, 0 is completely transparent (change the color in the CSS file)
			overlayFadeDuration: 400,		// Duration of the overlay fade-in and fade-out animations (in milliseconds)
			resizeDuration: 400,			// Duration of each of the box resize animations (in milliseconds)
			resizeEasing: "swing",			// "swing" is jQuery's default easing
			initialWidth: 250,			// Initial width of the box (in pixels)
			initialHeight: 250,			// Initial height of the box (in pixels)
			imageFadeDuration: 400,			// Duration of the image fade-in animation (in milliseconds)
			captionAnimationDuration: 400,		// Duration of the caption animation (in milliseconds)
			counterText: "Картинка {x} из {y}",	// Translate or change as you wish, or set it to false to disable counter text for image groups
			closeKeys: [27, 88, 67],		// Array of keycodes to close Slimbox, default: Esc (27), 'x' (88), 'c' (67)
			previousKeys: [37, 80],			// Array of keycodes to navigate to the previous image, default: Left arrow (37), 'p' (80)
			nextKeys: [39, 78],			// Array of keycodes to navigate to the next image, default: Right arrow (39), 'n' (78)
			callbackShow: function() {
			}
		}, _options);

		// The function is called for a single image, with URL and Title as first two arguments
		if (typeof _images == "string") {
			_images = [[_images, startImage]];
			startImage = 0;
		}

		middle = win.scrollTop() + ((operaFix ? documentElement.clientHeight : win.height()) / 2);
		centerWidth = options.initialWidth;
		centerHeight = options.initialHeight;
		$(center).css({top: Math.max(0, middle - (centerHeight / 2)), width: centerWidth, height: centerHeight, marginLeft: -centerWidth/2}).show();
		compatibleOverlay = ie6 || (overlay.currentStyle && (overlay.currentStyle.position != "fixed"));
		if (compatibleOverlay) overlay.style.position = "absolute";
		$(overlay).css("opacity", options.overlayOpacity).fadeIn(options.overlayFadeDuration);
		position();
		setup(1);

		images = _images;
		options.loop = options.loop && (images.length > 1);
		return changeImage(startImage);
	};

	/*
		options:	Optional options object, see jQuery.slimbox()
		linkMapper:	Optional function taking a link DOM element and an index as arguments and returning an array containing 2 elements:
				the image URL and the image caption (may contain HTML)
		linksFilter:	Optional function taking a link DOM element and an index as arguments and returning true if the element is part of
				the image collection that will be shown on click, false if not. "this" refers to the element that was clicked.
				This function must always return true when the DOM element argument is "this".
	*/
	$.fn.slimbox = function(_options, linkMapper, linksFilter) {
		linkMapper = linkMapper || function(el) {
			return [el.href, el.title, el];
		};

		linksFilter = linksFilter || function() {
			return true;
		};

		var links = this;

		return links.unbind("click").click(function() {
			// Build the list of images that will be displayed
			var link = this, startIndex = 0, filteredLinks, i = 0, length;
			filteredLinks = $.grep(links, function(el, i) {
				return linksFilter.call(link, el, i);
			});

			// We cannot use jQuery.map() because it flattens the returned array
			for (length = filteredLinks.length; i < length; ++i) {
				if (filteredLinks[i] == link) startIndex = i;
				filteredLinks[i] = linkMapper(filteredLinks[i], i);
			}

			return $.slimbox(filteredLinks, startIndex, _options);
		});
	};


	/*
		Internal functions
	*/

	function position() {
		var l = win.scrollLeft(), w = operaFix ? documentElement.clientWidth : win.width();
		$([center, bottomContainer]).css("left", l + (w / 2));
		if (compatibleOverlay) $(overlay).css({left: l, top: win.scrollTop(), width: w, height: win.height()});
	}

	function setup(open) {
		$("object").add(ie6 ? "select" : "embed").each(function(index, el) {
			if (open) $.data(el, "slimbox", el.style.visibility);
			el.style.visibility = open ? "hidden" : /*$.data(el, "slimbox")*/'visible';
		});
		var fn = open ? "bind" : "unbind";
		win[fn]("scroll resize", position);
		$(document)[fn]("keydown", keyDown);
	}

	function keyDown(event) {
		var code = event.keyCode, fn = $.inArray;
		// Prevent default keyboard action (like navigating inside the page)
		return (fn(code, options.closeKeys) >= 0) ? close()
			: (fn(code, options.nextKeys) >= 0) ? next()
			: (fn(code, options.previousKeys) >= 0) ? previous()
			: false;
	}

	function previous() {
		return changeImage(prevImage);
	}

	function next() {
		return changeImage(nextImage);
	}

	function changeImage(imageIndex) {
		if (imageIndex >= 0) {
			activeImage = imageIndex;
			activeURL = images[activeImage][0];
			prevImage = (activeImage || (options.loop ? images.length : 0)) - 1;
			nextImage = ((activeImage + 1) % images.length) || (options.loop ? 0 : -1);

			stop();
			center.className = "lbLoading";

			preload = new Image();
			preload.onload = animateBox;
			preload.src = activeURL;
		}

		return false;
	}

	function animateBox() {
		center.className = "";
		$(image).css({backgroundImage: "url(" + activeURL + ")", visibility: "hidden", display: ""});
		$(sizer).width(preload.width);
		$([sizer, prevLink, nextLink]).height(preload.height);

		$(caption).html(images[activeImage][1] || "");
		$(number).html((((images.length > 1) && options.counterText) || "").replace(/{x}/, activeImage + 1).replace(/{y}/, images.length));

		if (prevImage >= 0) preloadPrev.src = images[prevImage][0];
		if (nextImage >= 0) preloadNext.src = images[nextImage][0];

		centerWidth = image.offsetWidth;
		centerHeight = image.offsetHeight;
		var top = Math.max(0, middle - (centerHeight / 2));
		if (center.offsetHeight != centerHeight) {
			$(center).animate({height: centerHeight, top: top}, options.resizeDuration, options.resizeEasing);
		}
		if (center.offsetWidth != centerWidth) {
			$(center).animate({width: centerWidth, marginLeft: -centerWidth/2}, options.resizeDuration, options.resizeEasing);
		}
		$(center).queue(function() {
			$(bottomContainer).css({width: centerWidth, top: top + centerHeight, marginLeft: -centerWidth/2, visibility: "hidden", display: ""});
			$(image).css({display: "none", visibility: "", opacity: ""}).fadeIn(options.imageFadeDuration, animateCaption);
		});
	}

	function animateCaption() {
		if (prevImage >= 0) $(prevLink).show();
		if (nextImage >= 0) $(nextLink).show();
		$(bottom).css("marginTop", -bottom.offsetHeight).animate({marginTop: 0}, options.captionAnimationDuration);
		options.callbackShow(activeURL);
		bottomContainer.style.visibility = "";
	}

	function stop() {
		preload.onload = null;
		preload.src = preloadPrev.src = preloadNext.src = activeURL;
		$([center, image, bottom]).stop(true);
		$([prevLink, nextLink, image, bottomContainer]).hide();
	}

	function close() {
		if (activeImage >= 0) {
			stop();
			activeImage = prevImage = nextImage = -1;
			$(center).hide();
			$(overlay).stop().fadeOut(options.overlayFadeDuration, setup);
		}

		return false;
	}

})(jQuery);
