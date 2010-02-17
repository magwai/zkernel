/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

var gmap = {};

gmap.init = function(id, value, opt) {
	var o = $('#gmap_' + id);
	if (!opt.mapTypeId) opt.mapTypeId = 'ROADMAP';
	if (!opt.zoom) opt.zoom = 15;
	if (!opt.center) opt.center = [48.712688, 44.513394];
	opt.mapTypeId = google.maps.MapTypeId[opt.mapTypeId];
	opt.center = new google.maps.LatLng(opt.center[0], opt.center[1]);
	if (opt.width) o.width(opt.width);
	if (opt.height) o.height(opt.height);
	if (!opt.scrollwheel) opt.scrollwheel = false;
	opt.draggableCursor = 'crosshair';
	opt.navigationControlOptions = {
		style: google.maps.NavigationControlStyle.SMALL
	};
	gmap[id] = new google.maps.Map(o.get(0), opt);
	google.maps.event.addListener(gmap[id], 'click', function(e) {
		var latlng = e.latLng;
		if (!gmap[id].marker) {
			gmap[id].marker = new google.maps.Marker({
				flat: false,
				map: gmap[id],
				draggable: true,
				position: latlng
			});
			google.maps.event.addListener(gmap[id].marker, 'click', function(e) {
				this.setVisible(false);
				$('#' + id).val('');
			});
		}
		gmap[id].marker.setVisible(true);
		gmap[id].marker.setPosition(latlng);
		$('#' + id).val(
			latlng.lat().toFixed(6) +
			'|' +
			latlng.lng().toFixed(6)
		);
	});
	if (value) google.maps.event.trigger(gmap[id], 'click', {
		latLng: opt.center
	});
};
