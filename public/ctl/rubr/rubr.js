var rubr = {};

rubr.init = function(id, opt) {
	var o = $('#rubr_' + id);
	var d = opt.data;
	var s = rubr.build(d);
	o.append(s);
	o.find('input').click(function() {
		var v = $(this).val();
		var ov = $('#' + id + '-' + v);
		if (ov.length == 0 && $(this).attr('checked')) {
			var i = rubr.digg_tree($(this).parent('span').parent('label').parent('div').prev('a:first'));
			$('#rubr_' + id + '_value').append('<br /><label for="' + id + '-' + v + '"><input type="checkbox" name="' + id + '[]" id="' + id + '-' + v + '" value="' + v + '" checked="checked" />' + $(this).parent('span').parent('label').text().trim()  + (i.length ? ' <small>(' + i + ')</small>' : '') + '</label>');
			rubr.activate_value(id);
		}
		else {
			var ovv = ov.parent('label');
			ovv.prev('br:first').remove();
			ovv.remove();
		}
	});
	o.find('a').click(function() {
		var a = $(this);
		var v = a.next('div:first:visible');
		if (v.length) v.slideUp(400, function() {
			a.find('span').html('+');
		});
		else a.next('div:first').slideDown(400, function() {
			a.find('span').html('-');
		});
		return false;
	});
	rubr.activate_value(id);
};

rubr.activate_value = function(id) {
	var o = $('#rubr_' + id);
	$('.#rubr_' + id + '_value input:not(.active)').addClass('rubr_active').click(function() {
		var idd = $(this).attr('id');
		var i = idd.lastIndexOf('-');
		idd = idd.slice(i + 1);
		o.find('input[value="' + idd + '"]').click();
	});
}

rubr.digg_tree = function(o) {
	var s = '';
	if (o.length) {
		var i = rubr.digg_tree(o.parent('div').prev('a:first'));
		s = (i.length ? i + ' / ' : '') + o.text().slice(1);
	}
	return s;
}

rubr.build = function(d) {
	var s = '';
	if (d.length) {
		for (var k in d) {
			var i = d[k].i;
			i = typeof i != 'undefined' ? rubr.build(i) : '';
			s += (i.length
				? '<a href="#"><span>+</span>' + d[k].t + '</a><div>' + i + '</div>'
				: '<label><span><input type="checkbox" value="' + d[k].d + '"' + (d[k].c ? ' checked="checked"' : '') + ' /></span>' + d[k].t + '</label>'
			);
		}
	}
	return s;
};