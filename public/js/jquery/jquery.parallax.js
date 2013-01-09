/** 
 * Parallax 0.2
 * 
 * Add a simple parallax effect to a page
 * 
 * Requires jQuery. Tested with v1.3.2.
 * 
 * options: object, Contains all the options required to run the parallax effect:
 * options.useHTML: boolean, If set to true the script will use the HTML element
 *   instead of the container to capture mousemove events
 * options.elements: array, An array of objects of the following structure:
 *   {
 *     'selector': 'div.test',
 *     'properties': {
 *       'x': {
 *         'left': {
 *           'initial': 0,
 *           'multiplier': 0.1,
 *           'invert': true,
 *           'unit': 'px',
 *           'min': -160,
 *           'max': 160
 *         }
 *       },
 *       'y': {
 *          'top': {
 *           'initial': 0,
 *           'multiplier': 0.1,
 *           'invert': false,
 *           'unit': 'px',
 *           'min': 90,
 *           'max': 110
 *          }
 *       }
 *     }
 *   }
 * 
 * options.elements[n].selector: string, The jQuery selector for the element
 * options.elements[n].properties: object, Contains 'x' and 'y' keys for the properties
 *   that are affected by either horizontal, or vertical movement respectively
 * options.elements[n].properties[x || y]: object, Contains keys relating to the CSS
 *   property to be changed on movement
 * options.elements[n].properties[x || y][cssProperty]: object, Must contain at least
 *   two keys 'initial' and 'multiplier'.
 *   'initial' is the starting point for the property and 'multiplier' is used to create
 *   the parallax effect. For example to have the element property move exactly with the
 *   mouse cursor you'd use 1, lower values move less...
 *   'min' and 'max' should be fairly self explanetory, the value will be prevented from
 *   deviating beyond these boundaries (both are optional)
 *   'unit' is also optional unit of measurement (the default is 'px')
 *   'invert' is also an optional boolean, if true, the number will be negated
 * 
 * Free to use anywhere for anything, but I'd love to see what anyone does with it...
 * 
 * dom111.co.uk
 * 
 * Changelog:
 * 0.2
 *   Added an optional unit and invert paramter to each item
 *   Turned the function into a jQuery plugin
 * 
 * 0.1
 *   Initial release
 */
(function($) {
  $.fn.parallax = function(options) {
    // options
    var options = $.extend({
      // useHTML: use the whole document as a listener
      'useHTML': true,
      // elements: the elements to manipulate
      'elements': []
    }, options || {});

    // attach the mousemove event to the specified element
    $((options.useHTML) ? 'html' : this).mousemove(function(e) {
      // set up the element as a variable
      var el = $(this);

      // calculate the center
      var center = {
        'x': Math.floor(parseInt(el.width()) / 2),
        'y': Math.floor(parseInt(el.height()) / 2)
      }

      // the the cursor's position
      var pos = {
        'x': (e.pageX - el.offset().left),
        'y': (e.pageY - el.offset().top)
      }

      // calculate the offset
      var offset = {
        'x': (pos.x - center.x),
        'y': (pos.y - center.y)
      }

      // loop through all the elements
      for (var i = options.elements.length - 1; i >= 0; i--) {
        // set up a container for the properties
        var opts = {}, value, p;

        // loop through all the properties specified
        for (var property in options.elements[i].properties.x) {
          // store the objet in a nicer variable
          p = options.elements[i].properties.x[property];

          // set the value
          value = p.initial + (offset.x * p.multiplier);

          // check that the value's within the bounds
          if ('min' in p && value < p.min) {
            value = p.min;

          } else if ('max' in p && value > p.max) {
            value = p.max;
          }

          // invert the value if required
          if ('invert' in p && p.invert) {
            value = -(value);
          }

          // check if a unit has been specified
          if (!('unit' in p)) {
            p.unit = 'px';
          }

          // append it
          opts[property] = value + p.unit;
        }

        for (var property in options.elements[i].properties.y) {
          p = options.elements[i].properties.y[property];
          
          value = p.initial + (offset.y * p.multiplier);
          
          if ('min' in p && value < p.min) {
            value = p.min;
            
          } else if ('max' in p && value > p.max) {
            value = p.max;
          }
          
          if ('invert' in p && p.invert) {
            value = -(value);
          }

          if (!('unit' in p)) {
            p.unit = 'px';
          }

          opts[property] = value + p.unit;
        }

        // fix for firefox
        if ('background-position-x' in opts || 'background-position-y' in opts) {
          opts['background-position'] = '' + (('background-position-x' in opts) ? opts['background-position-x'] : '0px') + ' ' + (('background-position-y' in opts) ? opts['background-position-y'] : '0px');

          delete opts['background-position-x'];
          delete opts['background-position-y'];
        }

        // here's the magic! simples!
        $(options.elements[i].selector).css(opts);
      };
    });
  }
})(jQuery);
