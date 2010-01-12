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
})(jQuery);