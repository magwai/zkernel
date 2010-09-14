;(function($){
$.jgrid.extend({
	updateRowNum : function(){
		this.each(function(){
			$(this).find("td.jqgrid-rownum").each(function(i){
				var s = $(this).find('span');
				s.length
					? s.html(i + 1)
					: $(this).html(i + 1);
			});
		});
	},
	updateNodeChildren : function(r) {
		var ch = $("#list").jqGrid("getNodeChildren", r);
		$(ch).insertAfter(r);
	}
});

$.extend($.fn.fmatter, {
	zimage: function(cellval, opts) {
		opts['url'] = '/upload/catalogvase_pic';
		return opts.url
			?	'<a href="' + opts.url + '/' + cellval + '" class="c_fancy">' +
				'<img style="height:50px;max-width:100px;" src="' + opts.url + '/' + cellval + '" alt="" />' +
				'</a>'
			: cellval;
	}
});

})(jQuery);
