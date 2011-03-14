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
	if (!opt.type) opt.type = 'point';
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
			gmap[id].marker = [];
		}
		var m = opt.type == 'route' || !gmap[id].marker.length ? new google.maps.Marker({
			flat: false,
			map: gmap[id],
			draggable: true,
			position: latlng,
			title: opt.type == 'route'
				? String(gmap[id].marker.length + 1)
				: ''
		}) : gmap[id].marker[0];

		google.maps.event.addListener(m, 'click', function() {
			this.setVisible(false);
			var ll1 = this.getPosition();
			gmap.value_delete(id,
				ll1.lat().toFixed(6) +
				'|' +
				ll1.lng().toFixed(6)
			);
		});

		if (opt.type == 'route' || !gmap[id].marker.length) gmap[id].marker.push(m);

		m.setVisible(true);
		m.setPosition(latlng);
		gmap.value_add(id,
			latlng.lat().toFixed(6) +
			'|' +
			latlng.lng().toFixed(6)
		);
	});
	if (value) {
		var ll = [];
		for (var i = 0; i < value.length; i+=2) {
			ll.push(new google.maps.LatLng(value[i], value[i + 1]));
			google.maps.event.trigger(gmap[id], 'click', {
				latLng: ll[ll.length - 1]
			});
		}
		gmap[id].setCenter(ll[0]);
	}
};

gmap.value_add = function(id, v) {
	var old = $('#' + id).val();
	old = old.length == 0 ? [] : old.split(' ');
	for (var i = 0; i < old.length; i++) {
		if (old[i] == v) return;
	}
	old.push(v);
	$('#' + id).val(old.join(' '));
};

gmap.value_delete = function(id, v) {
	var old = $('#' + id).val();
	old = old.length == 0 ? [] : old.split(' ');
	for (var i = 0; i < old.length; i++) {
		if (old[i] == v) {
			old.splice(i, 1);
			break;
		}
	}
	$('#' + id).val(old.join(' '));
};
