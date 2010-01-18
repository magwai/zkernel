
jQuery.extend({


    createUploadIframe: function(id, uri)
	{
			//create frame
            var frameId = 'jUploadFrame' + id;

            if(window.ActiveXObject) {
                var io = document.createElement('<iframe id="' + frameId + '" name="' + frameId + '" />');
                if(typeof uri== 'boolean'){
                    io.src = 'javascript:false';
                }
                else if(typeof uri== 'string'){
                    io.src = uri;
                }
            }
            else {
                var io = document.createElement('iframe');
                io.id = frameId;
                io.name = frameId;
            }
            io.style.position = 'absolute';
            io.style.top = '-1000px';
            io.style.left = '-1000px';

            document.body.appendChild(io);

            return io
    },
    createUploadForm: function(id, fileElementId)
	{
		//create form
		var formId = 'jUploadForm' + id;
		var fileId = 'jUploadFile' + id;
		var form = $('<form  action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"><input type="hidden" name="UPLOAD_IDENTIFIER" value="' + fileElementId + '" /></form>');
		var oldElement = $('#' + fileElementId);
		var newElement = $(oldElement).clone();
		$(oldElement).attr('id', fileId);
		$(oldElement).before(newElement);
		$(oldElement).appendTo(form);
		//set attributes
		$(form).css('position', 'absolute');
		$(form).css('top', '-1200px');
		$(form).css('left', '-1200px');
		$(form).appendTo('body');
		return form;
    },

    ajaxFileUpload: function(s) {
        // TODO introduce global settings, allowing the client to modify them for all requests, not only timeout
        s = jQuery.extend({}, jQuery.ajaxSettings, s);
        var id = new Date().getTime()
		var form = jQuery.createUploadForm(id, s.fileElementId);
		var io = jQuery.createUploadIframe(id, s.secureuri);
		var frameId = 'jUploadFrame' + id;
		var formId = 'jUploadForm' + id;
        // Watch for a new set of requests
        if ( s.global && ! jQuery.active++ )
		{
			jQuery.event.trigger( "ajaxStart" );
		}
        var requestDone = false;
        // Create the request object
        var xml = {}
        if ( s.global )
            jQuery.event.trigger("ajaxSend", [xml, s]);
        // Wait for a response to come back
        var uploadCallback = function(isTimeout)
		{
			var io = document.getElementById(frameId);
            try
			{
				if(io.contentWindow)
				{
					 xml.responseText = io.contentWindow.document.body?io.contentWindow.document.body.innerHTML:null;
                	 xml.responseXML = io.contentWindow.document.XMLDocument?io.contentWindow.document.XMLDocument:io.contentWindow.document;

				}else if(io.contentDocument)
				{
					 xml.responseText = io.contentDocument.document.body?io.contentDocument.document.body.innerHTML:null;
                	xml.responseXML = io.contentDocument.document.XMLDocument?io.contentDocument.document.XMLDocument:io.contentDocument.document;
				}
            }catch(e)
			{
				jQuery.handleError(s, xml, null, e);
			}
            if ( xml || isTimeout == "timeout")
			{
                requestDone = true;
                var status;
                try {
                    status = isTimeout != "timeout" ? "success" : "error";
                    // Make sure that the request was successful or notmodified
                    if ( status != "error" )
					{
                        // process the data (runs the xml through httpData regardless of callback)
                        var data = jQuery.uploadHttpData( xml, s.dataType );
                        // If a local callback was specified, fire it and pass it the data
                        if ( s.success )
                            s.success( data, status );

                        // Fire the global callback
                        if( s.global )
                            jQuery.event.trigger( "ajaxSuccess", [xml, s] );
                    } else
                        jQuery.handleError(s, xml, status);
                } catch(e)
				{
                    status = "error";
                    jQuery.handleError(s, xml, status, e);
                }

                // The request was completed
                if( s.global )
                    jQuery.event.trigger( "ajaxComplete", [xml, s] );

                // Handle the global AJAX counter
                if ( s.global && ! --jQuery.active )
                    jQuery.event.trigger( "ajaxStop" );

                // Process result
                if ( s.complete )
                    s.complete(xml, status);

                jQuery(io).unbind()

                setTimeout(function()
									{	try
										{
											$(io).remove();
											$(form).remove();

										} catch(e)
										{
											jQuery.handleError(s, xml, null, e);
										}

									}, 100)

                xml = null

            }
        }
        // Timeout checker
        if ( s.timeout > 0 )
		{
            setTimeout(function(){
                // Check to see if the request is still happening
                if( !requestDone ) uploadCallback( "timeout" );
            }, s.timeout);
        }
        try
		{
           // var io = $('#' + frameId);
			var form = $('#' + formId);
			$(form).attr('action', s.url);
			$(form).attr('method', 'POST');
			$(form).attr('target', frameId);
            if(form.encoding)
			{
                form.encoding = 'multipart/form-data';
            }
            else
			{
                form.enctype = 'multipart/form-data';
            }
            $(form).submit();

        } catch(e)
		{
            jQuery.handleError(s, xml, null, e);
        }
        if(window.attachEvent){
            document.getElementById(frameId).attachEvent('onload', uploadCallback);
        }
        else{
            document.getElementById(frameId).addEventListener('load', uploadCallback, false);
        }
        return {abort: function () {}};

    },

    uploadHttpData: function( r, type ) {
        var data = !type;
        data = type == "xml" || data ? r.responseXML : r.responseText;
        // If the type is "script", eval it in global context
        if ( type == "script" )
            jQuery.globalEval( data );
        // Get the JavaScript object, if JSON is used.
        if ( type == "json" )
            eval( "data = " + data );
        // evaluate scripts within html
        if ( type == "html" )
            jQuery("<div>").html(data).evalScripts();
			//alert($('param', data).each(function(){alert($(this).attr('value'));}));
        return data;
    }
})

jfu = {
	cnt: 0,
	loading_size: [],
	loading: [],
	timer: [],
	failure_cnt: 0,
	period: 2000
};
jfu.upload = function(tid) {
	$('#' + tid).find('input[id$="_jfu"]').each(function() {
		jfu.cnt++;
		var id = $(this).attr('id');
		$.ajaxFileUpload ({
			url: '/x_process_jfu/',
			secureuri: false,
			fileElementId: id,
			dataType: 'json',
			success: function (data, status) {
				var inp = $('#' + id).next('input:first');
				inp.val(inp.attr('name') + '|' + data.data);
			},
			error: function (data, status, e) {
				alert(e);
			},
			complete: function() {
				jfu.cnt--;
				jfu.loading[id] = false;
				window.clearTimeout(jfu.timer[id]);
				$('#' + id).prevAll('span:first').html('').hide();
				//alert(id + '*');
			}
		});
		jfu.loading[id] = true;
		jfu.get_size(id);
	});
}
jfu.check = function(callback) {
	if (jfu.cnt == 0) callback();
	else {
		c = callback;
		window.setTimeout('jfu.check(c)', 100);
	}
}
jfu.get_size = function(id) {
	if (!jfu.loading_size[id]) {
  		$.ajax({
  			dataType : "json",
			url: '/x_get_fu_size/id.' + id + '/',
			complete: function() {
				jfu.loading_size[id] = false;
				if (jfu.failure_cnt > 5) jfu.period += 2000;
			},
			success: function(data) {
				if (data && data.bytes_uploaded) {
					$('#' + id).prevAll('span:first').html(
						jfu.get_size_text(data.bytes_uploaded) +
						(data.bytes_total
							? ' èç ' + jfu.get_size_text(data.bytes_total)
							: '')
					).show();
					jfu.failure_cnt = 0;
					jfu.period = 2000;
				}
				else jfu.failure_cnt++;
			},
			error: function() {
				jfu.failure_cnt++;
			}
		});
		jfu.loading_size[id] = true;
	}
	if (jfu.loading[id]) {
		window.clearTimeout(jfu.timer[id]);
		if (jfu.period < 11000) jfu.timer[id] = window.setTimeout('jfu.get_size("' + id + '")', jfu.period);
	}
}
jfu.get_size_text = function(s) {
	s = Number(s);
	var toEval = '';
	var type = ['á', 'Êá', 'Ìá', 'Ãá', 'Òá'];
	var nsize = s;
	var times = 0;
	while (nsize > 1024) {
		nsize = nsize / 1024;
		toEval += '/1024';
		times ++;
	}
	if (times > 0) eval('s = (s' + toEval + ');');
	return s.toFixed(2) + ' ' + type[times];
}