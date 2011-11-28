(function(jQuery) {
	jQuery.fn.extend({
		tablegenerator: function(options) {
			_t = jQuery(this);

			opt = jQuery.extend({
				col: null,
				row: null,
				id: 'tablegenerator'
			}, options ? options : {});

			_result = $('#' + opt.id);
			_t.html(
				'<div class="tg-frame">' +
					'<table class="tg-table"><tr class="tg-row tg-col-holder"></tr><tbody class="tg-row-holder"></tbody></table>' +
				'</div>' + (_result.length ? '' : '<input type="hidden" id="' + opt.id + '" />')
			);
			if (!_result.length) _result = $('#' + opt.id);

			var data = JSON.decode(_result.val());

			if (data && data.col) opt.col = opt.col ? $.extend(data.col, opt.col) : data.col;
			else if (!opt.col) opt.col = {};

			if (data && data.row) opt.row = opt.row ? $.extend(data.row, opt.row) : data.row;
			else if (!opt.row) opt.row = {};

			_t.undelegate('.tg-add-row', 'click').delegate('.tg-add-row', 'click', _t.add_row);
			_t.undelegate('.tg-add-col', 'click').delegate('.tg-add-col', 'click', _t.add_col);
			_t.undelegate('.tg-del-col', 'click').delegate('.tg-del-col', 'click', _t.del_col);
			_t.undelegate('.tg-del-row', 'click').delegate('.tg-del-row', 'click', _t.del_row);
			_t.undelegate('.tg-input', 'keyup').delegate('.tg-input', 'keyup', _t.save);
			_t.undelegate('.tg-input', 'change').delegate('.tg-input', 'change', _t.save);
			_t.undelegate('.tg-del-row', 'hover').delegate('.tg-cell', 'hover', function(ev) {
				var tr = $(this).parents('.tg-row');
				var tr_ind = tr.parents('.tg-table').find('.tg-row').index(tr);
				var td_ind = tr.find('.tg-cell').index($(this));
				var td_top = _t.find('.tg-row:first').find('.tg-cell').eq(td_ind).find('.tg-wrap');
				var td_right = tr.find('.tg-cell:last').find('.tg-wrap');

				var cur_col = opt.col[td_ind];
				var next_col = opt.col[td_ind + 1];

				if (cur_col) {
					if (ev.type == 'mouseenter') {
						if (!next_col || !next_col.preserve) td_top.prepend('<div class="tg-button tg-add-col"><a title="Добавить столбец" href="#" onclick="return false">+</a></div>');
						if (!cur_col.preserve) td_top.prepend('<div class="tg-button tg-del-col"><a title="Удалить столбец" href="#" onclick="return false">-</a></div>');

						td_right.prepend('<div class="tg-button tg-add-row"><a title="Добавить строку" href="#" onclick="return false">+</a></div>');
						if (tr_ind != 0) td_right.prepend('<div class="tg-button tg-del-row"><a title="Удалить строку" href="#" onclick="return false">-</a></div>');
					}
					if (ev.type == 'mouseleave') {
						if (!next_col || !next_col.preserve) td_top.find('.tg-add-col').remove();
						if (!cur_col.preserve) td_top.find('.tg-del-col').remove();

						td_right.find('.tg-add-row').remove();
						if (tr_ind != 0) td_right.find('.tg-del-row').remove();
					}
				}
			});
			_t.build();
		},
		build: function() {
			var header = _t.find('.tg-col-holder').html('');
			for (var i = 0; i < opt.col.length; i++) {
				header.append(opt.col[i].preserve
					? _t.cell_header_preserve_html(opt.col[i].title)
					: _t.cell_header_html(opt.col[i].title)
				);
			}

			var body = _t.find('.tg-row-holder').html('');
			for (var i = 0; i < opt.row.length; i++) {
				var tds = $('<tr class="tg-row"></tr>');

				for (var i1 = 0; i1 < opt.col.length; i1++) {
					var val = opt.row[i].data[i1];
					tds.append(_t.cell_html(typeof val == 'undefined' ? '' : val, opt.col[i1].type));
					if (opt.col[i1].type == 'file') {
						var name = 'tg-uploadify-' + i + '-' + i1;
						tds.find('.tg-input[type=file]:last').attr({
							'name': name,
							'id': name
						});
						tds.find('.tg-input[type=hidden]:last').attr({
							'name': name
						});
					}
				}

				body.append(tds);
			}

			body.find('.tg-input[type=file]').each(function() {
				var t = $(this);
				zuf.init({
					'buttonImg': '/zkernel/ctl/tablegenerator/browse.png',
					'fileDataName': t.attr('name'),
					'folder': opt.file.folder,
					'scriptData': {
						'old': t.val(),
						'sid': opt.file.sid
					},
					'width': '33',
					'height': '20'
				});
				var h = t.nextAll('.tg-input[type=hidden]');
				var v = h.val();
				if (v) {
					zuf.add(t.attr('name'), v, opt.file.url);
					_t.zuf_del_reinit(t);
				}
				t.bind('select', function() {
					t.uploadifyUpload();
				}).bind('complete', function() {
					var v = h.val().replace('u|', '');
					h.val(v);
					if (v) {
						_t.save();
						window.setTimeout(function() {
							zuf.add(t.attr('name'), v, opt.file.url);
							_t.zuf_del_reinit(t);
						}, 500);
					}
				});
			});

			_t.find('.tg-cell').width((100 / opt.col.length) + '%');
		},
		zuf_del_reinit: function(o) {
			o.nextAll('.uploadifyQueue').find('.cancel a').attr('href', 'javascript:zuf.remove("' + o.attr('name') + '", "' + o.nextAll('.tg-input[type=hidden]').val() + '", true);_t.save();');
		},
		add_row: function() {
			var next_row_id = _t.get_row_next_id();

			var tr = $(this).parents('.tg-row');
			var tr_ind = tr.parents('.tg-table').find('.tg-row').index(tr) - 1;

			var row = [];
			for (var i = 0; i < opt.col.length; i++) row.push('');

			var o = {
				id: 'row' + next_row_id,
				data: row
			};

			if (tr_ind >= opt.row.length) opt.row.push(o);
			else opt.row.splice(tr_ind + 1, 0, o);

			_t.build();
			_t.save();
		},
		add_col: function() {
			var next_col_id = _t.get_col_next_id();

			var td = $(this).parents('.tg-cell');
			var tr = td.parents('.tg-row');
			var td_ind = tr.find('.tg-cell').index(td);

			var o = {
				id: 'col' + next_col_id,
				title: 'Столбец ' + next_col_id
			};

			if (td_ind == -1 || td_ind >= opt.col.length) opt.col.push(o);
			else opt.col.splice(td_ind + 1, 0, o);

			_t.build();
			_t.save();
		},
		del_col: function() {
			var td = $(this).parents('.tg-cell');
			var tr = td.parents('.tg-row');
			var td_ind = tr.find('.tg-cell').index(td);

			opt.col.splice(td_ind, 1);

			_t.build();
			_t.save();
		},
		del_row: function() {
			var tr = $(this).parents('.tg-row');
			var tr_ind = tr.parents('.tg-table').find('.tg-row').index(tr) - 1;

			opt.row.splice(tr_ind, 1);

			_t.build();
		},
		cell_header_html: function(title) {
			var o = $('<th class="tg-cell"><div class="tg-wrap"><input class="tg-input" /></div></th>');
			o.find('.tg-input').val(title);
			return o;
		},
		cell_header_preserve_html: function(title) {
			var o = $('<th class="tg-cell"><div class="tg-wrap"><div class="tg-label">' + title + '</div></th>');
			return o;
		},
		cell_html: function(value, type) {
			var o = $('<td class="tg-cell"><div class="tg-wrap"><input class="tg-input"' + (type == 'file' ? ' type="file"' : '') + ' />' + (type == 'file' ? '<input class="tg-input" type="hidden" />' : '') + '</div></div></td>');
			o.find('.tg-input:last').val(value);
			return o;
		},
		get_col_next_id: function() {
			var next_id = 0;
			for (var i = 0; i < opt.col.length; i++) {
				if (opt.col[i].id.slice(0, 3) == 'col') {
					var num = Number(opt.col[i].id.slice(3));
					if (num > next_id) next_id = num;
				}
			}
			return next_id + 1;
		},
		get_row_next_id: function() {
			var next_id = 0;
			for (var i = 0; i < opt.row.length; i++) {
				if (opt.row[i].id.slice(0, 3) == 'row') {
					var num = Number(opt.row[i].id.slice(3));
					if (num > next_id) next_id = num;
				}
			}
			return next_id + 1;
		},
		save: function() {
			var trs = _t.find('.tg-row');

			var tds_hdr = trs.eq(0).find('.tg-cell');
			for (var i = 0; i < opt.col.length; i++) {
				var obj = tds_hdr.eq(i);
				var inp = obj.find('.tg-input');
				opt.col[i].title = inp.length ? inp.val() : obj.find('.tg-label').text();
			}

			for (var i = 0; i < opt.row.length; i++) {
				for (var i1 = 0; i1 < opt.col.length; i1++) {
					opt.row[i].data[i1] = trs.eq(i + 1).find('.tg-cell').eq(i1).find('.tg-input:last').val();
				}
			}

			_result.val(JSON.encode({
				col: opt.col,
				row: opt.row
			}));
		}

	});
})(jQuery);