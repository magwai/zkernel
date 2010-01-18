var point = {};

point.init = function(id, value, opt) {
	var o = $('#point_' + id);
	var url = opt.url;
	var color = opt.color ? opt.color : '#ff0000';
	var p = $('<div title="Удалить отметку" id="point_' + id + '_p" style="overflow:hidden;width:6px;height:6px;position:absolute;display:none;cursor:pointer;background-color:' + color + ';" />').click(function() {
		point.set(id, -1, -1);
	});
	var i = $('<img id="point_' + id + '_i" alt="" style="cursor:crosshair;" />').click(function(e) {
		var f = $(this).offset();
		point.set(id, e.clientX - f.left, e.clientY - f.top);
		return false;
	}).load(function() {
		if (value) point.set(id, value[0], value[1]);
	}).attr('src', url);
	o.prepend(p).append(i);
}

point.set = function(id, x, y) {
	var d = $('#' + id);
	var p = $('#point_' + id + '_p');
	if (x == -1 || y == -1) {
		d.val('');
		p.hide();
	}
	else {
		p.show();
		x = document.body.scrollLeft + x;
		y = document.body.scrollTop + y;
		var xx = x - p.get(0).offsetWidth / 2;
		var yy = y - p.get(0).offsetHeight / 2;
		p.css({
			marginLeft: xx,
			marginTop: yy
		});
		d.val(x + "|" + y);
	}
}
