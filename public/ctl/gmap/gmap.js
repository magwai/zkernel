/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

var gmap = {
		type: 'point'
};

gmap.init = function(id, value, opt) {
	var o = $('#gmap_' + id);
	if (!opt.mapTypeId) opt.mapTypeId = 'ROADMAP';
	if (!opt.zoom) opt.zoom = 15;
	if (!opt.center) opt.center = [48.712688, 44.513394];
	if (!opt.type) gmap.type = opt.type = 'point';
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
	if (opt.search && opt.type == 'point') {
		o.after('<div class="gmap-search"><input placeholder="Поиск по адресу" type="text" value="" class="ui-state-default ui-corner-all c_input" /></div>');
		gmap.geocoder = new google.maps.Geocoder();
		o.next('.gmap-search').find('input').keyup(function() {
			window.clearTimeout(gmap.search_timer);
			var t = $(this);
			gmap.search_timer = window.setTimeout(function() {
				gmap.geocoder.geocode({'address': t.val()}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						var z = gmap[id].getZoom();
						if (z < 14) gmap[id].setZoom(14);
						gmap[id].setCenter(results[0].geometry.location);
						google.maps.event.trigger(gmap[id], 'click', {
							latLng: results[0].geometry.location
						});
					}
				});
			}, 400);
		});
	}
	if (value) {
            if(opt.type == 'point'){
                var ll = new google.maps.LatLng(value[0], value[1]);
		google.maps.event.trigger(gmap[id], 'click', {
			latLng: ll
		});
		gmap[id].setCenter(ll);

            }else{
		var pp = [];
		for (var i = 0; i < value.length; i+=2) {
			pp.push(new google.maps.LatLng(value[i], value[i + 1]));
			google.maps.event.trigger(gmap[id], 'click', {
				latLng: pp[pp.length - 1]
			});
		}
		gmap[id].setCenter(pp[0]);
            }
	}
};

gmap.value_add = function(id, v) {
	var old = $('#' + id).val();
	if(gmap.type == 'point') old = '';
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
