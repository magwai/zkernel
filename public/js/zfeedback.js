if (typeof jQuery == "undefined") {
    var scriptObj = document.createElement("script");
    scriptObj.src = "/zkernel/js/jquery/jquery.js";
    scriptObj.type = "text/javascript";
    var head=document.getElementsByTagName("head")[0];
    head.insertBefore(scriptObj,head.firstChild);
}

var ZfeedbackLoad = window.onload;
window.onload = function(){
    if (ZfeedbackLoad) {
    	ZfeedbackLoad();
    }
    $('#Zkernel_Feedback').show();
    $('#Zkernel_Feedback_Btn').click(function() {
    	var w = $('#Zkernel_Feedback_Wnd');
    	if (w.length == 0) {
    		$('#Zkernel_Feedback').append(
    			'<div id="Zkernel_Feedback_Wnd"><form action="" method="post">' +
    				'<div><textarea tabindex="1" name="message"></textarea></div>' +
    				/* '<div><label for="Zkernel_Feedback_Ss"><input type="checkbox" checked="checked" name="screenshot" id="Zkernel_Feedback_Ss" /> прикрепить скриншот</label></div>' + */
    				'<div id="Zkernel_Feedback_Close"><input tabindex="3" type="button" value="Закрыть" /></div>' +
    				'<div id="Zkernel_Feedback_Last"><input tabindex="2" type="submit" value="Отправить" /></div>' +
    				'<input type="hidden" value="" name="browser" /><input type="hidden" value="" name="url" />' +
    			'</form></div>'
    		);
    		$('#Zkernel_Feedback_Close input').click(function() {
    			$('#Zkernel_Feedback_Wnd').hide();
            });
    		w = $('#Zkernel_Feedback_Wnd');
    		w.find('form').submit(function() {
    			$(this).find('input[name=browser]').val(
    				'Code name: ' + navigator.appCodeName + '\n' +
    				'App name: ' + navigator.appName + '\n' +
    				'User agent: ' + navigator.userAgent + '\n' +
    				'Vendor: ' + navigator.vendor + '\n' +
    				'Version: ' + navigator.appVersion + '\n' +
    				'Cookies: ' + (navigator.cookieEnabled ? 'Да' : 'Нет') + '\n' +
    				'Platform: ' + navigator.platform + '\n' +
    				'Screen: ' + window.screen.width + 'x' + window.screen.height + '\n' +
    				'Color: ' + window.screen.colorDepth + '\n'
    			);
    			$(this).find('input[name=url]').val(window.location);
    			$.ajax({
    				url: '/z/feedback',
    				type: 'post',
    				data: $(this).serialize(),
    				dataType: 'json',
    				success: function(d) {
    					$('#Zkernel_Feedback_Wnd').hide();
    					$('#Zkernel_Feedback_Wnd textarea').val('');
    					alert('Сообщение отправлено');
    				},
    				error: function() {
    					alert('Ошибка отправки сообщения');
    				},
    				complete: function() {
    					$('#Zkernel_Feedback_Last input').css('disabled', false);
    				}
    			});
    			$('#Zkernel_Feedback_Last input').css('disabled', true);
    			return false;
    		});
    	}
    	w.show();
    	$('#Zkernel_Feedback_Wnd textarea').focus();
    });
};
