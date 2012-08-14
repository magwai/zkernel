;(function($) {
    $.rate_wrapper = {
        el: null,
        init: function(options, el) {
            this.settings = $.extend({}, {
                on_select: function(ui, score) {
                },
                uid: 0,
                star_count: 5,
                start_z: 100,
				readonly: false,
				cookie_use: true,
                cookie_name: 'mysite_product_score',
                cookie_domain: '',
                cookie_path: '/',
                cookie_duration: 365
                }, options
            );
            //set the current element
            this.el = el;
            var uid = this.settings.uid;
            if (this.settings.readonly) {
				el.addClass('rate-disabled');
				el.find('.rate-current-score').css('z-index', this.settings.start_z + this.settings.star_count + 1);
			}

            //use the score in the cookies when we have them;
            this.set_score(el, uid);

            stars = el.find('a');
            stars.each(function(i, star) {
				$(star).bind('click', function(ev) {
					if ($.rate_wrapper.settings.readonly) return false;
                    score = +$(star).html();
                    ev.preventDefault();
                    $.rate_wrapper.settings.on_select(el, score);
                    if ($.rate_wrapper.settings.cookie_use) $.rate_wrapper.set_cookie_score(uid, score);
                    $.rate_wrapper.set_score(el, uid, score);
                });
            });

            // Hide the current score when we have a mousever
            // and show em again on leave.
            stars.each(function(i, star) {
                if (!$.rate_wrapper.settings.readonly) $(star).hover(function() {
						el.find('.rate-current-score').hide();
                    },
                    function () {
                        el.find('.rate-current-score').show();
                    }
                );
            });
        },
        set_score: function (el, uid, score) {

            cur_score = el.find('.rate-current-score');
            cur_score_val = cur_score.html();

			if ($.rate_wrapper.settings.cookie_use) {
				cookie_score_val = this.get_rating(uid);

				if(cookie_score_val !== 0) {
					cur_score_val = cookie_score_val;
				}
			}

            if(score > 0 ) {
                cur_score_val = score/$.rate_wrapper.settings.star_count * 100;
            }
            cur_score.css('display', 'block').css('width', String(cur_score_val)+'%');
        },
        /** retrieve the cookie value **/
        get_cookie: function (c_name) {
            if (document.cookie.length>0) {
                c_start=document.cookie.indexOf(c_name + "=");
                if (c_start!=-1) {
                    c_start = c_start + c_name.length+1;
                    c_end = document.cookie.indexOf(";", c_start);
                if (c_end==-1) c_end=document.cookie.length;
                    return unescape(document.cookie.substring(c_start,c_end));
                }
            }
            return "";
        },
        /** Retrives the current rating inside the cookie **/
        get_rating: function(uid) {
            uid = String(uid);

            cookie = this.get_cookie($.rate_wrapper.settings.cookie_name);

            if( '' === cookie) {
                return 0;
            }

            cookie = eval("("+cookie+")")
            if (uid in cookie) {

                return cookie[uid]/$.rate_wrapper.settings.star_count * 100;
            }
            return 0;
        },
        set_cookie_score: function(uid, score) {
            var duration =new Date();
            duration.setDate(duration.getDate()+ $.rate_wrapper.settings.cookie_duration);

            cookie_name     = $.rate_wrapper.settings.cookie_name;
            cookie_domain   = $.rate_wrapper.settings.cookie_domain;
            cookie_path     = $.rate_wrapper.settings.cookie_path;
            cookie_duration = duration.toUTCString()

            cookie_val =  this.get_cookie(cookie_name);
            o = new Object;
            o[String(uid)] = score;

            if ('' === cookie_val) {
                cookie_val = o;
            } else {
                cookie_val = eval("("+cookie_val+")");
                cookie_val = $.extend({}, cookie_val, o);
            }

            cookie_val = escape(JSON.stringify(cookie_val));
            cookie = cookie_name+'='+cookie_val+';expires='+cookie_duration+';path='+cookie_path;
            if('' !== cookie_domain) {
                cookie += ';domain='+cookie_domain
            }
            document.cookie = cookie;
        }
    }
    $.fn.rate = function(options) {
        return $.rate_wrapper.init(options, $(this));
    }
})(jQuery);