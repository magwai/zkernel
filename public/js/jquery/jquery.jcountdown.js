/*
* jCountdown 1.3.3 jQuery Plugin
* Copyright 2011 Tom Ellis http://www.webmuse.co.uk | MIT Licensed (license.txt)
*/
(function($) {

$.fn.countdown = function( method /*, options*/ ) {

	var defaults = {
			date: new Date(),
			updateTime: 1E3,
			htmlTemplate: "%{d} <span class='cd-time'>days</span> %{h} <span class='cd-time'>hours</span> %{m} <span class='cd-time'>mins</span> %{s} <span class='cd-time'>sec</span>",
			minus: false,
			onChange: null,
			onComplete: null,
			onResume: null,
			onPause: null,
			leadingZero: false,
			offset: null,
			servertime:null,
			hoursOnly: false,
			direction: "down"
		},
        slice = [].slice,
		floor = Math.floor,
		msPerHr = 36E5,
		msPerDay = 864E5,
		rDate = /(%\{d\}|%\{h\}|%\{m\}|%\{s\})/g,
		rDays = /%\{d\}/,
		rHrs = /%\{h\}/,
		rMins = /%\{m\}/,
		rSecs = /%\{s\}/,
		getTZDate = function( offset, difference ) {

			var hrs,
				dateMS,
				extra,
				tmpDate = new Date();

			if( offset === null ) {
				dateMS = tmpDate.getTime() - difference;
			} else {
				hrs = offset * msPerHr;
				curHrs = tmpDate.getTime() - ( ( -tmpDate.getTimezoneOffset() / 60 ) * msPerHr ) + hrs,
				dateMS = tmpDate.setTime( curHrs ) - difference;
			}
			return new Date( dateMS );
		},
		timerFunc = function() {
			//Function runs at set interval updating countdown
			var $this = this,
				template,
				now,
				date,
				timeLeft,
				eDaysLeft,
				daysLeft,
				eHrsLeft,
				hrsLeft,
				minsLeft,
				eMinsleft,
				secLeft,
				time = "",
				settings = $this.data("jcdData");

			if( !settings ) {
				return false;
			}

			template = settings.htmlTemplate;

			if( settings.offset === null && settings.servertime === null ) {
				now = new Date();
			} else 	if( settings.offset !== null ) {
				now = getTZDate( settings.offset, settings.difference );
			} else {
				now =  getTZDate( null, settings.difference ); //Date now
			}


			date = new Date( settings.date ); //Date to countdown to

			timeLeft = ( settings.direction === "down" ) ? date.getTime() - now.getTime() : now.getTime() - date.getTime();

			eDaysLeft = timeLeft / msPerDay;
			daysLeft = floor( eDaysLeft );
			eHrsLeft = ( eDaysLeft - daysLeft ) * 24;
			hrsLeft = floor( eHrsLeft );
			minsLeft = floor( ( eHrsLeft - hrsLeft ) * 60 );
			eMinsleft = ( eHrsLeft - hrsLeft ) * 60;
			//secLeft = floor( (eMinsleft - minsLeft ) * 60 );
			secLeft = Math.round( (eMinsleft - minsLeft ) * 60 );



			if( settings.hoursOnly ) {
				hrsLeft += daysLeft * 24;
				daysLeft = 0;
			}

			settings.daysLeft = daysLeft;
			settings.hrsLeft = hrsLeft;
			settings.minsLeft = minsLeft;
			settings.secLeft = secLeft;

			if( secLeft == 60 ) {
				secLeft = 0;
			}

			if ( settings.leadingZero ) {
				if ( daysLeft < 10 && !settings.hoursOnly ) {
					daysLeft = "0" + daysLeft;
				}
				if ( hrsLeft < 10 ) {
					hrsLeft = "0" + hrsLeft;
				}
				if ( minsLeft < 10 ) {
					minsLeft = "0" + minsLeft;
				}
				if ( secLeft < 10 ) {
					secLeft = "0" + secLeft;
				}
			}

			if ( settings.direction === "down" && ( now <= date || settings.minus ) ) {
				time = template.replace( rDays, daysLeft ).replace( rHrs, hrsLeft ).replace( rMins, minsLeft ).replace( rSecs, secLeft );
			} else if ( settings.direction === "up" && ( date <= now || settings.minus ) ) {
				time = template.replace( rDays, daysLeft ).replace( rHrs, hrsLeft ).replace( rMins, minsLeft ).replace( rSecs, secLeft );
			} else {
				time = template.replace( rDate, "00");
				settings.hasCompleted = true;
			}

			$this.html( time ).trigger("change.jcdevt", [settings] );

			if ( settings.hasCompleted ) {
				$this.trigger("complete.jcdevt");
				clearInterval( settings.timer );
			}
		},
		methods = {
			init: function( options ) {

				var opts = $.extend( {}, defaults, options ),
					template = opts.htmlTemplate;

				return this.each(function() {
					var $this = $(this),
						settings = {},
						func;

					//If this element already has a countdown timer, just change the settings
					if( $this.data("jcdData") ) {
						$this.countdown("changeSettings", options);
						return true;
					}

					//Add event handlers where set
					if( opts.onChange ) {
						$this.bind("change.jcdevt", opts.onChange );
					}

					if( opts.onComplete ) {
						$this.bind("complete.jcdevt", opts.onComplete );
					}

					if( opts.onPause ) {
						$this.bind("pause.jcdevt", opts.onPause );
					}

					if( opts.onResume ) {
						$this.bind("resume.jcdevt", opts.onResume );
					}

					settings = {
						originalContent : $this.html(),
						date : opts.date,
						hoursOnly : opts.hoursOnly,
						leadingZero : opts.leadingZero,
						updateTime : opts.updateTime,
						direction : opts.direction,
						template : opts.htmlTemplate,
						htmlTemplate : opts.htmlTemplate,
						minus : opts.minus,
						offset : opts.offset,
						servertime: opts.servertime,
						difference: null,
						onChange : opts.onChange,
						onComplete : opts.onComplete,
						onResume : opts.onResume,
						onPause : opts.onPause,
						hasCompleted : false,
						timer : 0
					};

					if( opts.servertime !== null ) {
						var local = new Date(),
							difference = local.getTime() - settings.servertime;
							difference = Number(difference);

						settings.difference = difference;
					}

					func = $.proxy( timerFunc, $this );
					settings.timer = setInterval( func, settings.updateTime );

					$this.data( "jcdData", settings );

					func();
				});
			},
			changeSettings: function( options ) {
				//Like resume but with resetting/changing options
				return this.each(function() {
					var $this  = $(this),
						settings,
						func = $.proxy( timerFunc, $this );

					if( !$this.data("jcdData") ) {
						return true;
					}

					settings = $.extend( {}, $this.data("jcdData"), options );

					$this.unbind(".jcdevt");
					//Clear the timer, as it might not be needed
					clearInterval( settings.timer );

					if( settings.onChange ) {
						$this.bind("change.jcdevt", settings.onChange);
					}

					if( settings.onComplete ) {
						$this.bind("complete.jcdevt", settings.onComplete);
					}

					if( settings.onPause ) {
						$this.bind("pause.jcdevt", settings.onPause );
					}

					if( settings.onResume ) {
						$this.bind("resume.jcdevt", settings.onResume );
					}
					
					if( typeof options['servertime'] != 'undefined' && options.servertime !== null ) {
						var local = new Date(),
							difference = local.getTime() - settings.servertime;
							difference = Number(difference);

						settings.difference = difference;
					}

					settings.timer = setInterval( func, settings.updateTime );
					$this.data("jcdData", settings);
					func(); //Needs to run straight away when changing settings
				});
			},
			resume: function() {
				//Resumes a countdown timer
				return this.each(function() {
					var $this = $(this),
						settings = $this.data("jcdData"),
						func = $.proxy( timerFunc, $this );

					if( !settings ) {
						return true;
					}

					settings.timer = setInterval( func, settings.updateTime );
					$this.data("jcdData", settings).trigger("resume.jcdevt")
					func();
				});
			},
			pause: function() {
				//Pause a countdown timer
				return this.each(function() {
					var $this = $(this),
						settings = $this.data("jcdData");

					if( !settings ) {
						return true;
					}
					//Clear interval (Will be started on resume)
					clearInterval( settings.timer );
					//Trigger pause event handler
					$this.data("jcdData", settings).trigger("pause.jcdevt");
				});
			},
			complete: function() {
				return this.each(function() {
					var $this = $(this),
						settings = $this.data("jcdData");

					if( !settings ) {
						return true;
					}
					//Clear timer
					clearInterval( settings.timer );
					settings.hasCompleted = true;
					//Update setting, trigger complete event handler, then unbind all events
					//We don"t delete the settings in case they need to be checked later on
					$this.data("jcdData", settings).trigger("complete.jcdevt").unbind(".jcdevt");
				});
			},
			destroy: function() {
				return this.each(function() {
					var $this = $(this),
						settings = $this.data("jcdData");

					if( !settings ) {
						return true;
					}
					//Clear timer
					clearInterval( settings.timer );
					//Unbind all events, remove data and put DOM Element back to its original state (HMTL wise)
					$this.unbind(".jcdevt").removeData("jcdData").html( settings.originalContent );
				});
			},
			getSettings: function( name ) {
				var $this = $(this),
					settings = $this.data("jcdData");

				if( !settings ) {
					return undefined;
				}
				//If an individual setting is required
				if( name ) {
					//If it exists, return it
					if( settings.hasOwnProperty( name ) ) {
						return settings[name];
					}
					return undefined;
				}
				//Return all settings
				return settings;
			}
		};

	if( methods[ method ] ) {
		return methods[ method ].apply( this, slice.call( arguments, 1 ) );
	} else if ( typeof method === "object" || !method ) {
		return methods.init.apply( this, arguments );
	} else {
		$.error("Method "+ method +" does not exist in the jCountdown Plugin");
	}
};

})(jQuery);