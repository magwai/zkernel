if (typeof jQuery == "undefined") {
    var scriptObj = document.createElement("script");
    scriptObj.src = "/zkernel/js/jquery/jquery.js";
    scriptObj.type = "text/javascript";
    var head=document.getElementsByTagName("head")[0];
    head.insertBefore(scriptObj,head.firstChild);
}

var ZkernelLoad = window.onload;
window.onload = function(){
    if (ZkernelLoad) {
        ZkernelLoad();
    }
    //jQuery.noConflict();
    ZkernelCollapsed();
};

function ZkernelCollapsed() {
    if (window.zdebug_collapsed) {
		ZkernelPanel();
	    jQuery("#Zkernel_toggler").html("&#187;");
	    return jQuery("#Zkernel_debug").css("left", "-"+parseInt(jQuery("#Zkernel_debug").outerWidth()-jQuery("#Zkernel_toggler").outerWidth()+1)+"px");
    }
}

function ZkernelPanel(name) {
    jQuery(".Zkernel_panel").each(function(i){
        if(jQuery(this).css("display") == "block") {
            jQuery(this).slideUp();
        } else {
            if (jQuery(this).attr("id") == name)
                jQuery(this).slideDown();
            else
                jQuery(this).slideUp();
        }
    });
}

function ZkernelSlideBar() {
    if (jQuery("#Zkernel_debug").position().left > 0) {
        document.cookie = "ZkernelCollapsed=1;expires=;path=/";
        ZkernelPanel();
        jQuery("#Zkernel_toggler").html("&#187;");
        return jQuery("#Zkernel_debug").animate({left:"-"+parseInt(jQuery("#Zkernel_debug").outerWidth()-jQuery("#Zkernel_toggler").outerWidth()+1)+"px"}, "normal", "swing");
    } else {
        document.cookie = "ZkernelCollapsed=0;expires=;path=/";
        jQuery("#Zkernel_toggler").html("&#171;");
        return jQuery("#Zkernel_debug").animate({left:"5px"}, "normal", "swing");
    }
}

function ZkernelToggleElement(name, whenHidden, whenVisible){
    if(jQuery(name).css("display")=="none"){
            jQuery(whenVisible).show();
            jQuery(whenHidden).hide();
        } else {
            jQuery(whenVisible).hide();
            jQuery(whenHidden).show();
        }
        jQuery(name).slideToggle();
}