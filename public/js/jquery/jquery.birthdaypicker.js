/*!
 * jQuery Birthday Picker: v1.2 - 3/21/2011
 * http://abecoffman.com/stuff/birthdaypicker
 *
 * Copyright (c) 2010 Abe Coffman
 * Dual licensed under the MIT and GPL licenses.
 *
 */

(function( $ ){

  // plugin variables
  var curDate = new Date(),
      curYear = curDate.getFullYear(),
      curMonth = curDate.getMonth() + 1,
      curDay = curDate.getDate();


  $.fn.birthdaypicker = function( options ) {

    var settings = {
	  "months"		  : {
		  "short": ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
		  "long": ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
	  },
	  "titles"		  : ["Year:", "Month:", "Day:"],
      "maxAge"        : 120,
      "futureDates"   : false,
      "maxYear"       : curYear,
      "dateFormat"    : "middleEndian",
      "monthFormat"   : "short",
      "placeholder"   : true,
      "legend"        : "",
      "defaultDate"   : false,
      "hiddenDate"    : true
    };

    return this.each(function() {

      if (options) { $.extend(settings, options); }

      // Create the html picker skeleton
      var $fieldset = $("<fieldset class='birthday-picker'></fieldset>"),
          $year = $("<select class='birth-year' name='birth[year]'></select>"),
          $month = $("<select class='birth-month' name='birth[month]'></select>"),
          $day = $("<select class='birth-day' name='birth[day]'></select>");

      if(settings["legend"]) { $("<legend>" + settings["legend"] + "</legend>").appendTo($fieldset); }

      // Deal with the various Date Formats
      var hiddenDate;
      if(settings["dateFormat"] == "bigEndian") {
        hiddenDate = curYear + "-" + curMonth + "-" + curDay;
        $fieldset.append($year).append($month).append($day);
      } else if(settings["dateFormat"] == "littleEndian") {
        hiddenDate = curDay + "-" + curMonth + "-" + curYear;
        $fieldset.append($day).append($month).append($year);
      } else {
        $fieldset.append($month).append($day).append($year);
        hiddenDate = curMonth + "-" + curDay + "-" + curYear;
      }

	 // Add the option placeholders if specified
      if(settings["placeholder"]) {
        $("<option value='0'>" + settings['titles'][0] + "</option>").appendTo($year);
        $("<option value='0'>" + settings['titles'][1] + "</option>").appendTo($month);
        $("<option value='0'>" + settings['titles'][2] + "</option>").appendTo($day);
        hiddenDate = null;
      }

	  if (!settings["defaultDate"] && $(this)[0].tagName == 'INPUT') {
		  var v = $(this)[0].value.split('.');
		  if (v.length == 3) {
			  settings["defaultDate"] = v[2] + "/" + v[1] + "/"  + v[0];
			  hiddenDate = $(this)[0].value.replace(/\./gi, '-');
		  }
	  }

      // Account for a default date and set hiddenDate accordingly
      if(settings["defaultDate"] && !hiddenDate) { hiddenDate = settings["defaultDate"] }

      // Create the hidden date markup
      if(settings["hiddenDate"]) {
        $("<input type='hidden' name='" + ($(this)[0].tagName == 'INPUT' ? $(this)[0].name : 'birthdate') + "' />").val(hiddenDate).appendTo($fieldset);
      }

      // Build the initial option sets
      var startYear = curYear;
      var endYear = curYear - settings["maxAge"];
      if(settings["futureDates"] && settings["maxYear"] != curYear) {
        if (settings["maxYear"] > 1000) { startYear = settings["maxYear"]; }
        else { startYear = curYear + settings["maxYear"]; }
      }
      while(startYear >= endYear) { $("<option></option>").attr("value", startYear).text(startYear).appendTo($year); startYear--; }
      for( var j=0; j<12; j++) { $("<option></option>").attr("value", j+1).text(settings['months'][settings["monthFormat"]][j]).appendTo($month); }
      for(var k=1; k<32; k++) { $("<option></option>").attr("value", k).text(k).appendTo($day); }

	  if ($(this)[0].tagName == 'INPUT') $(this).after($fieldset).remove();
	  else $(this).append($fieldset);

      // Set the default date if given
      if(settings["defaultDate"]) {
        var date = new Date(Date.parse(settings["defaultDate"]));
		$year.val(date.getFullYear());
        $month.val(date.getMonth() + 1);
        $day.val(date.getDate());
      }

      // Update the option sets according to options and user selections
      $fieldset.change(function(){
        var curNumMonths = parseInt($month.children(":last").val()),
            curNumDays = parseInt($day.children(":last").val()),
            dd = new Date($year.val(), $month.val(), 0),
            actNumDays = dd.getDate();
        // Dealing with the number of days in a month
        if(curNumDays > actNumDays) { while(curNumDays > actNumDays) { $day.children(":last").remove(); curNumDays--; } }
        else if(curNumDays < actNumDays) {
          while(curNumDays < actNumDays) {
            $("<option></option>").attr("value", curNumDays+1).text(curNumDays+1).appendTo($day); curNumDays++;
          }
        }

        // Dealing with future months/days in current year
        if(!settings["futureDates"] && $year.val() == curYear) {
          if(curNumMonths > curMonth) { while(curNumMonths > curMonth) { $month.children(":last").remove(); curNumMonths--; } }
          if($month.val() == curMonth) {
            if(curNumDays > curDay) { while(curNumDays > curDay) { $day.children(":last").remove(); curNumDays--; } }
          }
        }

        // Adding months back that may have been removed
        if($year.val() != curYear && curNumMonths != 12) {
          while(curNumMonths < 12) {
            $("<option></option>").attr("value", curNumMonths+1).text( months[settings["monthFormat"]][curNumMonths] ).appendTo($month);
            curNumMonths++;
          }
        }

        // update the hidden date
        var curYear = $year.val(),
            curMonth = $month.val(),
            curDay = $day.val();
        if((curYear * curMonth * curDay) != 0) {
          if(settings["dateFormat"] == "bigEndian") {
            hiddenDate = curYear + "-" + curMonth + "-" + curDay;
          } else if(settings["dateFormat"] == "littleEndian") {
            hiddenDate = curDay + "-" + curMonth + "-" + curYear;
          } else {
            hiddenDate = curMonth + "-" + curDay + "-" + curYear;
          }
        }
		else {
			hiddenDate = '';
		}
		var dp =  $(this).children('input[type="hidden"]');
		if (dp.length == 0) dp = $(this).next('input[type="hidden"]');
		dp.val(hiddenDate);
      });
    });
  };
})( jQuery );