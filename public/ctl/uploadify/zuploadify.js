zuf = {};

zuf.init = function(n) {
	var h = $('input[name=' + n + '][type=hidden]');
	var e = h.prevAll('em');
	e.find('>a').click(function() {
		if (h.hasClass('zuf_deleted')) {
			e.find('span').css({
				'text-decoration': 'none',
				'opacity': 1
			});
			$(this).find('img').css({
				'opacity': 1
			}).attr('title', '');
			e.find('span>a').unbind('click').attr('title', 'Удалить');
			h.removeClass('zuf_deleted');
			h.val(h.attr('vv')).attr('vv', '');
		}
		else {
			e.find('span').css({
				'text-decoration': 'line-through',
				'opacity': .3
			});
			$(this).find('img').css({
				'opacity': .3
			}).attr('title', 'Не удалять');
			e.find('span>a').attr('title', 'Не удалять').unbind('click').click(function() {
				e.find('>a').click();
				return false;
			});
			h.addClass('zuf_deleted');
			h.attr('vv', h.val()).val('d|' + h.val());
			$('input[name=' + n + '][type=file]').uploadifyClearQueue();
		}
		return false;
	});
}
