function doubleval(mixed_var) {
  //  discuss at: http://phpjs.org/functions/doubleval/
  // original by: Brett Zamir (http://brett-zamir.me)
  //  depends on: floatval
  //        note: 1.0 is simplified to 1 before it can be accessed by the function, this makes
  //        note: it different from the PHP implementation. We can't fix this unfortunately.
  //   example 1: doubleval(186);
  //   returns 1: 186.00

  return this.floatval(mixed_var);
}
function empty(mixed_var) {
  //  discuss at: http://phpjs.org/functions/empty/
  // original by: Philippe Baumann
  //    input by: Onno Marsman
  //    input by: LH
  //    input by: Stoyan Kyosev (http://www.svest.org/)
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Onno Marsman
  // improved by: Francesco
  // improved by: Marc Jansen
  // improved by: Rafal Kukawski
  //   example 1: empty(null);
  //   returns 1: true
  //   example 2: empty(undefined);
  //   returns 2: true
  //   example 3: empty([]);
  //   returns 3: true
  //   example 4: empty({});
  //   returns 4: true
  //   example 5: empty({'aFunc' : function () { alert('humpty'); } });
  //   returns 5: false

  var undef, key, i, len;
  var emptyValues = [undef, null, false, 0, '', '0'];

  for (i = 0, len = emptyValues.length; i < len; i++) {
    if (mixed_var === emptyValues[i]) {
      return true;
    }
  }

  if (typeof mixed_var === 'object') {
    for (key in mixed_var) {
      // TODO: should we check for own properties only?
      //if (mixed_var.hasOwnProperty(key)) {
      return false;
      //}
    }
    return true;
  }

  return false;
}
function floatval(mixed_var) {
  //  discuss at: http://phpjs.org/functions/floatval/
  // original by: Michael White (http://getsprink.com)
  //        note: The native parseFloat() method of JavaScript returns NaN when it encounters a string before an int or float value.
  //   example 1: floatval('150.03_page-section');
  //   returns 1: 150.03
  //   example 2: floatval('page: 3');
  //   example 2: floatval('-50 + 8');
  //   returns 2: 0
  //   returns 2: -50

  return (parseFloat(mixed_var) || 0);
}
function gettype(mixed_var) {
  //  discuss at: http://phpjs.org/functions/gettype/
  // original by: Paulo Freitas
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Douglas Crockford (http://javascript.crockford.com)
  // improved by: Brett Zamir (http://brett-zamir.me)
  //    input by: KELAN
  //  depends on: is_float
  //        note: 1.0 is simplified to 1 before it can be accessed by the function, this makes
  //        note: it different from the PHP implementation. We can't fix this unfortunately.
  //   example 1: gettype(1);
  //   returns 1: 'integer'
  //   example 2: gettype(undefined);
  //   returns 2: 'undefined'
  //   example 3: gettype({0: 'Kevin van Zonneveld'});
  //   returns 3: 'object'
  //   example 4: gettype('foo');
  //   returns 4: 'string'
  //   example 5: gettype({0: function () {return false;}});
  //   returns 5: 'object'
  //   example 6: gettype({0: 'test', length: 1, splice: function () {}});
  //   example 6: gettype(['test']);
  //   returns 6: 'object'
  //   returns 6: 'array'

  var s = typeof mixed_var,
    name;
  var getFuncName = function(fn) {
    var name = (/\W*function\s+([\w\$]+)\s*\(/)
      .exec(fn);
    if (!name) {
      return '(Anonymous)';
    }
    return name[1];
  };
  if (s === 'object') {
    if (mixed_var !== null) {
      // From: http://javascript.crockford.com/remedial.html
      if (typeof mixed_var.length === 'number' && !(mixed_var.propertyIsEnumerable('length')) && typeof mixed_var
        .splice === 'function') {
        s = 'array';
      } else if (mixed_var.constructor && getFuncName(mixed_var.constructor)) {
        name = getFuncName(mixed_var.constructor);
        if (name === 'Date') {
          // not in PHP
          s = 'date';
        } else if (name === 'RegExp') {
          // not in PHP
          s = 'regexp';
        } else if (name === 'PHPJS_Resource') {
          // Check against our own resource constructor
          s = 'resource';
        }
      }
    } else {
      s = 'null';
    }
  } else if (s === 'number') {
    s = this.is_float(mixed_var) ? 'double' : 'integer';
  }
  return s;
}
function intval(mixed_var, base) {
  //  discuss at: http://phpjs.org/functions/intval/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: stensi
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Rafał Kukawski (http://kukawski.pl)
  //    input by: Matteo
  //   example 1: intval('Kevin van Zonneveld');
  //   returns 1: 0
  //   example 2: intval(4.2);
  //   returns 2: 4
  //   example 3: intval(42, 8);
  //   returns 3: 42
  //   example 4: intval('09');
  //   returns 4: 9
  //   example 5: intval('1e', 16);
  //   returns 5: 30

  var tmp;

  var type = typeof mixed_var;

  if (type === 'boolean') {
    return +mixed_var;
  } else if (type === 'string') {
    tmp = parseInt(mixed_var, base || 10);
    return (isNaN(tmp) || !isFinite(tmp)) ? 0 : tmp;
  } else if (type === 'number' && isFinite(mixed_var)) {
    return mixed_var | 0;
  } else {
    return 0;
  }
}
function is_array(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_array/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Legaev Andrey
  // improved by: Onno Marsman
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Nathan Sepulveda
  // improved by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Cord
  // bugfixed by: Manish
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //        note: In php.js, javascript objects are like php associative arrays, thus JavaScript objects will also
  //        note: return true in this function (except for objects which inherit properties, being thus used as objects),
  //        note: unless you do ini_set('phpjs.objectsAsArrays', 0), in which case only genuine JavaScript arrays
  //        note: will return true
  //   example 1: is_array(['Kevin', 'van', 'Zonneveld']);
  //   returns 1: true
  //   example 2: is_array('Kevin van Zonneveld');
  //   returns 2: false
  //   example 3: is_array({0: 'Kevin', 1: 'van', 2: 'Zonneveld'});
  //   returns 3: true
  //   example 4: is_array(function tmp_a(){this.name = 'Kevin'});
  //   returns 4: false

  var ini,
    _getFuncName = function(fn) {
      var name = (/\W*function\s+([\w\$]+)\s*\(/)
        .exec(fn);
      if (!name) {
        return '(Anonymous)';
      }
      return name[1];
    };
  _isArray = function(mixed_var) {
    // return Object.prototype.toString.call(mixed_var) === '[object Array]';
    // The above works, but let's do the even more stringent approach: (since Object.prototype.toString could be overridden)
    // Null, Not an object, no length property so couldn't be an Array (or String)
    if (!mixed_var || typeof mixed_var !== 'object' || typeof mixed_var.length !== 'number') {
      return false;
    }
    var len = mixed_var.length;
    mixed_var[mixed_var.length] = 'bogus';
    // The only way I can think of to get around this (or where there would be trouble) would be to have an object defined
    // with a custom "length" getter which changed behavior on each call (or a setter to mess up the following below) or a custom
    // setter for numeric properties, but even that would need to listen for specific indexes; but there should be no false negatives
    // and such a false positive would need to rely on later JavaScript innovations like __defineSetter__
    if (len !== mixed_var.length) {
      // We know it's an array since length auto-changed with the addition of a
      // numeric property at its length end, so safely get rid of our bogus element
      mixed_var.length -= 1;
      return true;
    }
    // Get rid of the property we added onto a non-array object; only possible
    // side-effect is if the user adds back the property later, it will iterate
    // this property in the older order placement in IE (an order which should not
    // be depended on anyways)
    delete mixed_var[mixed_var.length];
    return false;
  };

  if (!mixed_var || typeof mixed_var !== 'object') {
    return false;
  }

  // BEGIN REDUNDANT
  this.php_js = this.php_js || {};
  this.php_js.ini = this.php_js.ini || {};
  // END REDUNDANT

  ini = this.php_js.ini['phpjs.objectsAsArrays'];

  return _isArray(mixed_var) ||
    // Allow returning true unless user has called
    // ini_set('phpjs.objectsAsArrays', 0) to disallow objects as arrays
    ((!ini || (// if it's not set to 0 and it's not 'off', check for objects as arrays
      (parseInt(ini.local_value, 10) !== 0 && (!ini.local_value.toLowerCase || ini.local_value.toLowerCase() !==
        'off')))) && (
      Object.prototype.toString.call(mixed_var) === '[object Object]' && _getFuncName(mixed_var.constructor) ===
      'Object' // Most likely a literal and intended as assoc. array
    ));
}
function is_bool(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_bool/
  // original by: Onno Marsman
  // improved by: CoursesWeb (http://www.coursesweb.net/)
  //   example 1: is_bool(false);
  //   returns 1: true
  //   example 2: is_bool(0);
  //   returns 2: false

  return (mixed_var === true || mixed_var === false); // Faster (in FF) than type checking
}
function is_double(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_double/
  // original by: Paulo Freitas
  //  depends on: is_float
  //        note: 1.0 is simplified to 1 before it can be accessed by the function, this makes
  //        note: it different from the PHP implementation. We can't fix this unfortunately.
  //   example 1: is_double(186.31);
  //   returns 1: true

  return this.is_float(mixed_var);
}
function is_float(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_float/
  // original by: Paulo Freitas
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // improved by: WebDevHobo (http://webdevhobo.blogspot.com/)
  // improved by: Rafał Kukawski (http://blog.kukawski.pl)
  //        note: 1.0 is simplified to 1 before it can be accessed by the function, this makes
  //        note: it different from the PHP implementation. We can't fix this unfortunately.
  //   example 1: is_float(186.31);
  //   returns 1: true

  return +mixed_var === mixed_var && (!isFinite(mixed_var) || !!(mixed_var % 1));
}
function is_int(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_int/
  // original by: Alex
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: WebDevHobo (http://webdevhobo.blogspot.com/)
  // improved by: Rafał Kukawski (http://blog.kukawski.pl)
  //  revised by: Matt Bradley
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //        note: 1.0 is simplified to 1 before it can be accessed by the function, this makes
  //        note: it different from the PHP implementation. We can't fix this unfortunately.
  //   example 1: is_int(23)
  //   returns 1: true
  //   example 2: is_int('23')
  //   returns 2: false
  //   example 3: is_int(23.5)
  //   returns 3: false
  //   example 4: is_int(true)
  //   returns 4: false

  return mixed_var === +mixed_var && isFinite(mixed_var) && !(mixed_var % 1);
}
function is_integer(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_integer/
  // original by: Paulo Freitas
  //  depends on: is_int
  //        note: 1.0 is simplified to 1 before it can be accessed by the function, this makes
  //        note: it different from the PHP implementation. We can't fix this unfortunately.
  //   example 1: is_integer(186.31);
  //   returns 1: false
  //   example 2: is_integer(12);
  //   returns 2: true

  return this.is_int(mixed_var);
}
function is_long(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_long/
  // original by: Paulo Freitas
  //  depends on: is_float
  //        note: 1.0 is simplified to 1 before it can be accessed by the function, this makes
  //        note: it different from the PHP implementation. We can't fix this unfortunately.
  //   example 1: is_long(186.31);
  //   returns 1: true

  return this.is_float(mixed_var);
}
function is_null(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_null/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //   example 1: is_null('23');
  //   returns 1: false
  //   example 2: is_null(null);
  //   returns 2: true

  return (mixed_var === null);
}
function is_numeric(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_numeric/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: David
  // improved by: taith
  // bugfixed by: Tim de Koning
  // bugfixed by: WebDevHobo (http://webdevhobo.blogspot.com/)
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Denis Chenu (http://shnoulle.net)
  //   example 1: is_numeric(186.31);
  //   returns 1: true
  //   example 2: is_numeric('Kevin van Zonneveld');
  //   returns 2: false
  //   example 3: is_numeric(' +186.31e2');
  //   returns 3: true
  //   example 4: is_numeric('');
  //   returns 4: false
  //   example 5: is_numeric([]);
  //   returns 5: false
  //   example 6: is_numeric('1 ');
  //   returns 6: false

  var whitespace =
    " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
  return (typeof mixed_var === 'number' || (typeof mixed_var === 'string' && whitespace.indexOf(mixed_var.slice(-1)) ===
    -
    1)) && mixed_var !== '' && !isNaN(mixed_var);
}
function is_object(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_object/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Legaev Andrey
  // improved by: Michael White (http://getsprink.com)
  //   example 1: is_object('23');
  //   returns 1: false
  //   example 2: is_object({foo: 'bar'});
  //   returns 2: true
  //   example 3: is_object(null);
  //   returns 3: false

  if (Object.prototype.toString.call(mixed_var) === '[object Array]') {
    return false;
  }
  return mixed_var !== null && typeof mixed_var === 'object';
}
function is_string(mixed_var) {
  //  discuss at: http://phpjs.org/functions/is_string/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //   example 1: is_string('23');
  //   returns 1: true
  //   example 2: is_string(23.5);
  //   returns 2: false

  return (typeof mixed_var === 'string');
}
function isset() {
  //  discuss at: http://phpjs.org/functions/isset/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: FremyCompany
  // improved by: Onno Marsman
  // improved by: Rafał Kukawski
  //   example 1: isset( undefined, true);
  //   returns 1: false
  //   example 2: isset( 'Kevin van Zonneveld' );
  //   returns 2: true

  var a = arguments,
    l = a.length,
    i = 0,
    undef;

  if (l === 0) {
    throw new Error('Empty isset');
  }

  while (i !== l) {
    if (a[i] === undef || a[i] === null) {
      return false;
    }
    i++;
  }
  return true;
}
function strval(str) {
  //  discuss at: http://phpjs.org/functions/strval/
  // original by: Brett Zamir (http://brett-zamir.me)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //  depends on: gettype
  //   example 1: strval({red: 1, green: 2, blue: 3, white: 4});
  //   returns 1: 'Object'

  var type = '';

  if (str === null) {
    return '';
  }

  type = this.gettype(str);

  // Comment out the entire switch if you want JS-like
  // behavior instead of PHP behavior
  switch (type) {
  case 'boolean':
    if (str === true) {
      return '1';
    }
    return '';
  case 'array':
    return 'Array';
  case 'object':
    return 'Object';
  }

  return str;
}
function function_exists(func_name) {
  //  discuss at: http://phpjs.org/functions/function_exists/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Steve Clay
  // improved by: Legaev Andrey
  // improved by: Brett Zamir (http://brett-zamir.me)
  //   example 1: function_exists('isFinite');
  //   returns 1: true

  if (typeof func_name === 'string') {
    func_name = this.window[func_name];
  }
  return typeof func_name === 'function';
}
function array() {
  //  discuss at: http://phpjs.org/functions/array/
  // original by: d3x
  // improved by: Brett Zamir (http://brett-zamir.me)
  //        test: skip
  //   example 1: array('Kevin', 'van', 'Zonneveld');
  //   returns 1: ['Kevin', 'van', 'Zonneveld']
  //   example 2: ini_set('phpjs.return_phpjs_arrays', 'on');
  //   example 2: array({0:2}, {a:41}, {2:3}).change_key_case('CASE_UPPER').keys();
  //   returns 2: [0,'A',2]

  try {
    this.php_js = this.php_js || {};
  } catch (e) {
    this.php_js = {};
  }

  var arrInst, e, __, that = this,
    PHPJS_Array = function PHPJS_Array() {};
  mainArgs = arguments, p = this.php_js,
    _indexOf = function(value, from, strict) {
      var i = from || 0,
        nonstrict = !strict,
        length = this.length;
      while (i < length) {
        if (this[i] === value || (nonstrict && this[i] == value)) {
          return i;
        }
        i++;
      }
      return -1;
    };
  // BEGIN REDUNDANT
  if (!p.Relator) {
    p.Relator = (function() {
      // Used this functional class for giving privacy to the class we are creating
      // Code adapted from http://www.devpro.it/code/192.html
      // Relator explained at http://webreflection.blogspot.com/2008/07/javascript-relator-object-aka.html
      // Its use as privacy technique described at http://webreflection.blogspot.com/2008/10/new-relator-object-plus-unshared.html
      // 1) At top of closure, put: var __ = Relator.$();
      // 2) In constructor, put: var _ = __.constructor(this);
      // 3) At top of each prototype method, put: var _ = __.method(this);
      // 4) Use like:  _.privateVar = 5;
      function _indexOf(value) {
        var i = 0,
          length = this.length;
        while (i < length) {
          if (this[i] === value) {
            return i;
          }
          i++;
        }
        return -1;
      }

      function Relator() {
        var Stack = [],
          Array = [];
        if (!Stack.indexOf) {
          Stack.indexOf = _indexOf;
        }
        return {
          // create a new relator
          $           : function() {
            return Relator();
          },
          constructor : function(that) {
            var i = Stack.indexOf(that);
            ~
            i ? Array[i] : Array[Stack.push(that) - 1] = {};
            this.method(that)
              .that = that;
            return this.method(that);
          },
          method      : function(that) {
            return Array[Stack.indexOf(that)];
          }
        };
      }
      return Relator();
    }());
  }
  // END REDUNDANT

  if (p && p.ini && p.ini['phpjs.return_phpjs_arrays'].local_value.toLowerCase() === 'on') {
    if (!p.PHPJS_Array) {
      // We keep this Relator outside the class in case adding prototype methods below
      // Prototype methods added elsewhere can also use this ArrayRelator to share these "pseudo-global mostly-private" variables
      __ = p.ArrayRelator = p.ArrayRelator || p.Relator.$();
      // We could instead allow arguments of {key:XX, value:YY} but even more cumbersome to write
      p.PHPJS_Array = function PHPJS_Array() {
        var _ = __.constructor(this),
          args = arguments,
          i = 0,
          argl, p;
        args = (args.length === 1 && args[0] && typeof args[0] === 'object' &&
          // If first and only arg is an array, use that (Don't depend on this)
          args[0].length && !args[0].propertyIsEnumerable('length')) ? args[0] : args;
        if (!_.objectChain) {
          _.objectChain = args;
          _.object = {};
          _.keys = [];
          _.values = [];
        }
        for (argl = args.length; i < argl; i++) {
          for (p in args[i]) {
            // Allow for access by key; use of private members to store sequence allows these to be iterated via for...in (but for read-only use, with hasOwnProperty or function filtering to avoid prototype methods, and per ES, potentially out of order)
            this[p] = _.object[p] = args[i][p];
            // Allow for easier access by prototype methods
            _.keys[_.keys.length] = p;
            _.values[_.values.length] = args[i][p];
            break;
          }
        }
      };
      e = p.PHPJS_Array.prototype;
      e.change_key_case = function(cs) {
        var _ = __.method(this),
          oldkey, newkey, i = 0,
          kl = _.keys.length,
          case_fn = (!cs || cs === 'CASE_LOWER') ? 'toLowerCase' : 'toUpperCase';
        while (i < kl) {
          oldkey = _.keys[i];
          newkey = _.keys[i] = _.keys[i][case_fn]();
          if (oldkey !== newkey) {
            // Break reference before deleting
            this[oldkey] = _.object[oldkey] = _.objectChain[i][oldkey] = null;
            delete this[oldkey];
            delete _.object[oldkey];
            delete _.objectChain[i][oldkey];
            // Fix: should we make a deep copy?
            this[newkey] = _.object[newkey] = _.objectChain[i][newkey] = _.values[i];
          }
          i++;
        }
        return this;
      };
      e.flip = function() {
        var _ = __.method(this),
          i = 0,
          kl = _.keys.length;
        while (i < kl) {
          oldkey = _.keys[i];
          newkey = _.values[i];
          if (oldkey !== newkey) {
            // Break reference before deleting
            this[oldkey] = _.object[oldkey] = _.objectChain[i][oldkey] = null;
            delete this[oldkey];
            delete _.object[oldkey];
            delete _.objectChain[i][oldkey];
            this[newkey] = _.object[newkey] = _.objectChain[i][newkey] = oldkey;
            _.keys[i] = newkey;
          }
          i++;
        }
        return this;
      };
      e.walk = function(funcname, userdata) {
        var _ = __.method(this),
          obj, func, ini, i = 0,
          kl = 0;

        try {
          if (typeof funcname === 'function') {
            for (i = 0, kl = _.keys.length; i < kl; i++) {
              if (arguments.length > 1) {
                funcname(_.values[i], _.keys[i], userdata);
              } else {
                funcname(_.values[i], _.keys[i]);
              }
            }
          } else if (typeof funcname === 'string') {
            this.php_js = this.php_js || {};
            this.php_js.ini = this.php_js.ini || {};
            ini = this.php_js.ini['phpjs.no-eval'];
            if (ini && (
                parseInt(ini.local_value, 10) !== 0 && (!ini.local_value.toLowerCase || ini.local_value
                  .toLowerCase() !== 'off')
              )) {
              if (arguments.length > 1) {
                for (i = 0, kl = _.keys.length; i < kl; i++) {
                  this.window[funcname](_.values[i], _.keys[i], userdata);
                }
              } else {
                for (i = 0, kl = _.keys.length; i < kl; i++) {
                  this.window[funcname](_.values[i], _.keys[i]);
                }
              }
            } else {
              if (arguments.length > 1) {
                for (i = 0, kl = _.keys.length; i < kl; i++) {
                  eval(funcname + '(_.values[i], _.keys[i], userdata)');
                }
              } else {
                for (i = 0, kl = _.keys.length; i < kl; i++) {
                  eval(funcname + '(_.values[i], _.keys[i])');
                }
              }
            }
          } else if (funcname && typeof funcname === 'object' && funcname.length === 2) {
            obj = funcname[0];
            func = funcname[1];
            if (arguments.length > 1) {
              for (i = 0, kl = _.keys.length; i < kl; i++) {
                obj[func](_.values[i], _.keys[i], userdata);
              }
            } else {
              for (i = 0, kl = _.keys.length; i < kl; i++) {
                obj[func](_.values[i], _.keys[i]);
              }
            }
          } else {
            return false;
          }
        } catch (e) {
          return false;
        }

        return this;
      };
      // Here we'll return actual arrays since most logical and practical for these functions to do this
      e.keys = function(search_value, argStrict) {
        var _ = __.method(this),
          pos,
          search = typeof search_value !== 'undefined',
          tmp_arr = [],
          strict = !!argStrict;
        if (!search) {
          return _.keys;
        }
        while ((pos = _indexOf(_.values, pos, strict)) !== -1) {
          tmp_arr[tmp_arr.length] = _.keys[pos];
        }
        return tmp_arr;
      };
      e.values = function() {
        var _ = __.method(this);
        return _.values;
      };
      // Return non-object, non-array values, since most sensible
      e.search = function(needle, argStrict) {
        var _ = __.method(this),
          strict = !!argStrict,
          haystack = _.values,
          i, vl, val, flags;
        if (typeof needle === 'object' && needle.exec) {
          // Duck-type for RegExp
          if (!strict) {
            // Let's consider case sensitive searches as strict
            flags = 'i' + (needle.global ? 'g' : '') +
              (needle.multiline ? 'm' : '') +
              // sticky is FF only
              (needle.sticky ? 'y' : '');
            needle = new RegExp(needle.source, flags);
          }
          for (i = 0, vl = haystack.length; i < vl; i++) {
            val = haystack[i];
            if (needle.test(val)) {
              return _.keys[i];
            }
          }
          return false;
        }
        for (i = 0, vl = haystack.length; i < vl; i++) {
          val = haystack[i];
          if ((strict && val === needle) || (!strict && val == needle)) {
            return _.keys[i];
          }
        }
        return false;
      };
      e.sum = function() {
        var _ = __.method(this),
          sum = 0,
          i = 0,
          kl = _.keys.length;
        while (i < kl) {
          if (!isNaN(parseFloat(_.values[i]))) {
            sum += parseFloat(_.values[i]);
          }
          i++;
        }
        return sum;
      };
      // Experimental functions
      e.foreach = function(handler) {
        var _ = __.method(this),
          i = 0,
          kl = _.keys.length;
        while (i < kl) {
          if (handler.length === 1) {
            // only pass the value
            handler(_.values[i]);
          } else {
            handler(_.keys[i], _.values[i]);
          }
          i++;
        }
        return this;
      };
      e.list = function() {
        var key, _ = __.method(this),
          i = 0,
          argl = arguments.length;
        while (i < argl) {
          key = _.keys[i];
          if (key && key.length === parseInt(key, 10)
            .toString()
            .length && // Key represents an int
            parseInt(key, 10) < argl) {
            // Key does not exceed arguments
            that.window[arguments[key]] = _.values[key];
          }
          i++;
        }
        return this;
      };
      // Parallel functionality and naming of built-in JavaScript array methods
      e.forEach = function(handler) {
        var _ = __.method(this),
          i = 0,
          kl = _.keys.length;
        while (i < kl) {
          handler(_.values[i], _.keys[i], this);
          i++;
        }
        return this;
      };
      // Our own custom convenience functions
      e.$object = function() {
        var _ = __.method(this);
        return _.object;
      };
      e.$objectChain = function() {
        var _ = __.method(this);
        return _.objectChain;
      };
    }
    PHPJS_Array.prototype = p.PHPJS_Array.prototype;
    arrInst = new PHPJS_Array();
    p.PHPJS_Array.apply(arrInst, mainArgs);
    return arrInst;
  }
  return Array.prototype.slice.call(mainArgs);
}
function array_key_exists(key, search) {
  //  discuss at: http://phpjs.org/functions/array_key_exists/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Felix Geisendoerfer (http://www.debuggable.com/felix)
  //   example 1: array_key_exists('kevin', {'kevin': 'van Zonneveld'});
  //   returns 1: true

  if (!search || (search.constructor !== Array && search.constructor !== Object)) {
    return false;
  }

  return key in search;
}
function array_keys(input, search_value, argStrict) {
  //  discuss at: http://phpjs.org/functions/array_keys/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Brett Zamir (http://brett-zamir.me)
  //    input by: P
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // improved by: jd
  // improved by: Brett Zamir (http://brett-zamir.me)
  //   example 1: array_keys( {firstname: 'Kevin', surname: 'van Zonneveld'} );
  //   returns 1: {0: 'firstname', 1: 'surname'}

  var search = typeof search_value !== 'undefined',
    tmp_arr = [],
    strict = !!argStrict,
    include = true,
    key = '';

  if (input && typeof input === 'object' && input.change_key_case) {
    // Duck-type check for our own array()-created PHPJS_Array
    return input.keys(search_value, argStrict);
  }

  for (key in input) {
    if (input.hasOwnProperty(key)) {
      include = true;
      if (search) {
        if (strict && input[key] !== search_value) {
          include = false;
        } else if (input[key] != search_value) {
          include = false;
        }
      }

      if (include) {
        tmp_arr[tmp_arr.length] = key;
      }
    }
  }

  return tmp_arr;
}
function array_merge() {
  //  discuss at: http://phpjs.org/functions/array_merge/
  // original by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Nate
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //    input by: josh
  //   example 1: arr1 = {"color": "red", 0: 2, 1: 4}
  //   example 1: arr2 = {0: "a", 1: "b", "color": "green", "shape": "trapezoid", 2: 4}
  //   example 1: array_merge(arr1, arr2)
  //   returns 1: {"color": "green", 0: 2, 1: 4, 2: "a", 3: "b", "shape": "trapezoid", 4: 4}
  //   example 2: arr1 = []
  //   example 2: arr2 = {1: "data"}
  //   example 2: array_merge(arr1, arr2)
  //   returns 2: {0: "data"}

  var args = Array.prototype.slice.call(arguments),
    argl = args.length,
    arg,
    retObj = {},
    k = '',
    argil = 0,
    j = 0,
    i = 0,
    ct = 0,
    toStr = Object.prototype.toString,
    retArr = true;

  for (i = 0; i < argl; i++) {
    if (toStr.call(args[i]) !== '[object Array]') {
      retArr = false;
      break;
    }
  }

  if (retArr) {
    retArr = [];
    for (i = 0; i < argl; i++) {
      retArr = retArr.concat(args[i]);
    }
    return retArr;
  }

  for (i = 0, ct = 0; i < argl; i++) {
    arg = args[i];
    if (toStr.call(arg) === '[object Array]') {
      for (j = 0, argil = arg.length; j < argil; j++) {
        retObj[ct++] = arg[j];
      }
    } else {
      for (k in arg) {
        if (arg.hasOwnProperty(k)) {
          if (parseInt(k, 10) + '' === k) {
            retObj[ct++] = arg[k];
          } else {
            retObj[k] = arg[k];
          }
        }
      }
    }
  }
  return retObj;
}
function array_merge_recursive(arr1, arr2) {
  //  discuss at: http://phpjs.org/functions/array_merge_recursive/
  // original by: Subhasis Deb
  //    input by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //  depends on: array_merge
  //   example 1: arr1 = {'color': {'favourite': 'read'}, 0: 5}
  //   example 1: arr2 = {0: 10, 'color': {'favorite': 'green', 0: 'blue'}}
  //   example 1: array_merge_recursive(arr1, arr2)
  //   returns 1: {'color': {'favorite': {0: 'red', 1: 'green'}, 0: 'blue'}, 1: 5, 1: 10}

  var idx = '';

  if (arr1 && Object.prototype.toString.call(arr1) === '[object Array]' &&
    arr2 && Object.prototype.toString.call(arr2) === '[object Array]') {
    for (idx in arr2) {
      arr1.push(arr2[idx]);
    }
  } else if ((arr1 && (arr1 instanceof Object)) && (arr2 && (arr2 instanceof Object))) {
    for (idx in arr2) {
      if (idx in arr1) {
        if (typeof arr1[idx] === 'object' && typeof arr2 === 'object') {
          arr1[idx] = this.array_merge(arr1[idx], arr2[idx]);
        } else {
          arr1[idx] = arr2[idx];
        }
      } else {
        arr1[idx] = arr2[idx];
      }
    }
  }

  return arr1;
}
function in_array(needle, haystack, argStrict) {
  //  discuss at: http://phpjs.org/functions/in_array/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: vlado houba
  // improved by: Jonas Sciangula Street (Joni2Back)
  //    input by: Billy
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //   example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
  //   returns 1: true
  //   example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
  //   returns 2: false
  //   example 3: in_array(1, ['1', '2', '3']);
  //   example 3: in_array(1, ['1', '2', '3'], false);
  //   returns 3: true
  //   returns 3: true
  //   example 4: in_array(1, ['1', '2', '3'], true);
  //   returns 4: false

  var key = '',
    strict = !!argStrict;

  //we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] == ndl)
  //in just one for, in order to improve the performance 
  //deciding wich type of comparation will do before walk array
  if (strict) {
    for (key in haystack) {
      if (haystack[key] === needle) {
        return true;
      }
    }
  } else {
    for (key in haystack) {
      if (haystack[key] == needle) {
        return true;
      }
    }
  }

  return false;
}
function count(mixed_var, mode) {
  //  discuss at: http://phpjs.org/functions/count/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Waldo Malqui Silva (http://waldo.malqui.info)
  //    input by: merabi
  // bugfixed by: Soren Hansen
  // bugfixed by: Olivier Louvignes (http://mg-crea.com/)
  // improved by: Brett Zamir (http://brett-zamir.me)
  //   example 1: count([[0,0],[0,-4]], 'COUNT_RECURSIVE');
  //   returns 1: 6
  //   example 2: count({'one' : [1,2,3,4,5]}, 'COUNT_RECURSIVE');
  //   returns 2: 6

  var key, cnt = 0;

  if (mixed_var === null || typeof mixed_var === 'undefined') {
    return 0;
  } else if (mixed_var.constructor !== Array && mixed_var.constructor !== Object) {
    return 1;
  }

  if (mode === 'COUNT_RECURSIVE') {
    mode = 1;
  }
  if (mode != 1) {
    mode = 0;
  }

  for (key in mixed_var) {
    if (mixed_var.hasOwnProperty(key)) {
      cnt++;
      if (mode == 1 && mixed_var[key] && (mixed_var[key].constructor === Array || mixed_var[key].constructor ===
          Object)) {
        cnt += this.count(mixed_var[key], 1);
      }
    }
  }

  return cnt;
}
function ltrim(str, charlist) {
  //  discuss at: http://phpjs.org/functions/ltrim/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Erkekjetter
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  //   example 1: ltrim('    Kevin van Zonneveld    ');
  //   returns 1: 'Kevin van Zonneveld    '

  charlist = !charlist ? ' \\s\u00A0' : (charlist + '')
    .replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
  var re = new RegExp('^[' + charlist + ']+', 'g');
  return (str + '')
    .replace(re, '');
}
function rtrim(str, charlist) {
  //  discuss at: http://phpjs.org/functions/rtrim/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Erkekjetter
  //    input by: rem
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //   example 1: rtrim('    Kevin van Zonneveld    ');
  //   returns 1: '    Kevin van Zonneveld'

  charlist = !charlist ? ' \\s\u00A0' : (charlist + '')
    .replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\\$1');
  var re = new RegExp('[' + charlist + ']+$', 'g');
  return (str + '')
    .replace(re, '');
}
function trim(str, charlist) {
  //  discuss at: http://phpjs.org/functions/trim/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: mdsjack (http://www.mdsjack.bo.it)
  // improved by: Alexander Ermolaev (http://snippets.dzone.com/user/AlexanderErmolaev)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Steven Levithan (http://blog.stevenlevithan.com)
  // improved by: Jack
  //    input by: Erkekjetter
  //    input by: DxGx
  // bugfixed by: Onno Marsman
  //   example 1: trim('    Kevin van Zonneveld    ');
  //   returns 1: 'Kevin van Zonneveld'
  //   example 2: trim('Hello World', 'Hdle');
  //   returns 2: 'o Wor'
  //   example 3: trim(16, 1);
  //   returns 3: 6

  var whitespace, l = 0,
    i = 0;
  str += '';

  if (!charlist) {
    // default list
    whitespace =
      ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000';
  } else {
    // preg_quote custom list
    charlist += '';
    whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
  }

  l = str.length;
  for (i = 0; i < l; i++) {
    if (whitespace.indexOf(str.charAt(i)) === -1) {
      str = str.substring(i);
      break;
    }
  }

  l = str.length;
  for (i = l - 1; i >= 0; i--) {
    if (whitespace.indexOf(str.charAt(i)) === -1) {
      str = str.substring(0, i + 1);
      break;
    }
  }

  return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}
function strtolower(str) {
  //  discuss at: http://phpjs.org/functions/strtolower/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Onno Marsman
  //   example 1: strtolower('Kevin van Zonneveld');
  //   returns 1: 'kevin van zonneveld'

  return (str + '')
    .toLowerCase();
}
function strtoupper(str) {
  //  discuss at: http://phpjs.org/functions/strtoupper/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Onno Marsman
  //   example 1: strtoupper('Kevin van Zonneveld');
  //   returns 1: 'KEVIN VAN ZONNEVELD'

  return (str + '')
    .toUpperCase();
}
function strpos(haystack, needle, offset) {
  //  discuss at: http://phpjs.org/functions/strpos/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Onno Marsman
  // improved by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Daniel Esteban
  //   example 1: strpos('Kevin van Zonneveld', 'e', 5);
  //   returns 1: 14

  var i = (haystack + '')
    .indexOf(needle, (offset || 0));
  return i === -1 ? false : i;
}
function stripos(f_haystack, f_needle, f_offset) {
  //  discuss at: http://phpjs.org/functions/stripos/
  // original by: Martijn Wieringa
  //  revised by: Onno Marsman
  //   example 1: stripos('ABC', 'a');
  //   returns 1: 0

  var haystack = (f_haystack + '')
    .toLowerCase();
  var needle = (f_needle + '')
    .toLowerCase();
  var index = 0;

  if ((index = haystack.indexOf(needle, f_offset)) !== -1) {
    return index;
  }
  return false;
}
function strripos(haystack, needle, offset) {
  //  discuss at: http://phpjs.org/functions/strripos/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //    input by: saulius
  //   example 1: strripos('Kevin van Zonneveld', 'E');
  //   returns 1: 16

  haystack = (haystack + '')
    .toLowerCase();
  needle = (needle + '')
    .toLowerCase();

  var i = -1;
  if (offset) {
    i = (haystack + '')
      .slice(offset)
      .lastIndexOf(needle); // strrpos' offset indicates starting point of range till end,
    // while lastIndexOf's optional 2nd argument indicates ending point of range from the beginning
    if (i !== -1) {
      i += offset;
    }
  } else {
    i = (haystack + '')
      .lastIndexOf(needle);
  }
  return i >= 0 ? i : false;
}
function strrpos(haystack, needle, offset) {
  //  discuss at: http://phpjs.org/functions/strrpos/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //    input by: saulius
  //   example 1: strrpos('Kevin van Zonneveld', 'e');
  //   returns 1: 16
  //   example 2: strrpos('somepage.com', '.', false);
  //   returns 2: 8
  //   example 3: strrpos('baa', 'a', 3);
  //   returns 3: false
  //   example 4: strrpos('baa', 'a', 2);
  //   returns 4: 2

  var i = -1;
  if (offset) {
    i = (haystack + '')
      .slice(offset)
      .lastIndexOf(needle); // strrpos' offset indicates starting point of range till end,
    // while lastIndexOf's optional 2nd argument indicates ending point of range from the beginning
    if (i !== -1) {
      i += offset;
    }
  } else {
    i = (haystack + '')
      .lastIndexOf(needle);
  }
  return i >= 0 ? i : false;
}
function explode(delimiter, string, limit) {
  //  discuss at: http://phpjs.org/functions/explode/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //   example 1: explode(' ', 'Kevin van Zonneveld');
  //   returns 1: {0: 'Kevin', 1: 'van', 2: 'Zonneveld'}

  if (arguments.length < 2 || typeof delimiter === 'undefined' || typeof string === 'undefined') return null;
  if (delimiter === '' || delimiter === false || delimiter === null) return false;
  if (typeof delimiter === 'function' || typeof delimiter === 'object' || typeof string === 'function' || typeof string ===
    'object') {
    return {
      0 : ''
    };
  }
  if (delimiter === true) delimiter = '1';

  // Here we go...
  delimiter += '';
  string += '';

  var s = string.split(delimiter);

  if (typeof limit === 'undefined') return s;

  // Support for limit
  if (limit === 0) limit = 1;

  // Positive limit
  if (limit > 0) {
    if (limit >= s.length) return s;
    return s.slice(0, limit - 1)
      .concat([s.slice(limit - 1)
        .join(delimiter)
      ]);
  }

  // Negative limit
  if (-limit >= s.length) return [];

  s.splice(s.length + limit);
  return s;
}
function split(delimiter, string) {
  //  discuss at: http://phpjs.org/functions/split/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //  depends on: explode
  //   example 1: split(' ', 'Kevin van Zonneveld');
  //   returns 1: {0: 'Kevin', 1: 'van', 2: 'Zonneveld'}

  return this.explode(delimiter, string);
}
function substr(str, start, len) {
  //  discuss at: http://phpjs.org/functions/substr/
  //     version: 909.322
  // original by: Martijn Wieringa
  // bugfixed by: T.Wild
  // improved by: Onno Marsman
  // improved by: Brett Zamir (http://brett-zamir.me)
  //  revised by: Theriault
  //        note: Handles rare Unicode characters if 'unicode.semantics' ini (PHP6) is set to 'on'
  //   example 1: substr('abcdef', 0, -1);
  //   returns 1: 'abcde'
  //   example 2: substr(2, 0, -6);
  //   returns 2: false
  //   example 3: ini_set('unicode.semantics',  'on');
  //   example 3: substr('a\uD801\uDC00', 0, -1);
  //   returns 3: 'a'
  //   example 4: ini_set('unicode.semantics',  'on');
  //   example 4: substr('a\uD801\uDC00', 0, 2);
  //   returns 4: 'a\uD801\uDC00'
  //   example 5: ini_set('unicode.semantics',  'on');
  //   example 5: substr('a\uD801\uDC00', -1, 1);
  //   returns 5: '\uD801\uDC00'
  //   example 6: ini_set('unicode.semantics',  'on');
  //   example 6: substr('a\uD801\uDC00z\uD801\uDC00', -3, 2);
  //   returns 6: '\uD801\uDC00z'
  //   example 7: ini_set('unicode.semantics',  'on');
  //   example 7: substr('a\uD801\uDC00z\uD801\uDC00', -3, -1)
  //   returns 7: '\uD801\uDC00z'

  var i = 0,
    allBMP = true,
    es = 0,
    el = 0,
    se = 0,
    ret = '';
  str += '';
  var end = str.length;

  // BEGIN REDUNDANT
  this.php_js = this.php_js || {};
  this.php_js.ini = this.php_js.ini || {};
  // END REDUNDANT
  switch ((this.php_js.ini['unicode.semantics'] && this.php_js.ini['unicode.semantics'].local_value.toLowerCase())) {
  case 'on':
    // Full-blown Unicode including non-Basic-Multilingual-Plane characters
    // strlen()
    for (i = 0; i < str.length; i++) {
      if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
        allBMP = false;
        break;
      }
    }

    if (!allBMP) {
      if (start < 0) {
        for (i = end - 1, es = (start += end); i >= es; i--) {
          if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
            start--;
            es--;
          }
        }
      } else {
        var surrogatePairs = /[\uD800-\uDBFF][\uDC00-\uDFFF]/g;
        while ((surrogatePairs.exec(str)) != null) {
          var li = surrogatePairs.lastIndex;
          if (li - 2 < start) {
            start++;
          } else {
            break;
          }
        }
      }

      if (start >= end || start < 0) {
        return false;
      }
      if (len < 0) {
        for (i = end - 1, el = (end += len); i >= el; i--) {
          if (/[\uDC00-\uDFFF]/.test(str.charAt(i)) && /[\uD800-\uDBFF]/.test(str.charAt(i - 1))) {
            end--;
            el--;
          }
        }
        if (start > end) {
          return false;
        }
        return str.slice(start, end);
      } else {
        se = start + len;
        for (i = start; i < se; i++) {
          ret += str.charAt(i);
          if (/[\uD800-\uDBFF]/.test(str.charAt(i)) && /[\uDC00-\uDFFF]/.test(str.charAt(i + 1))) {
            // Go one further, since one of the "characters" is part of a surrogate pair
            se++;
          }
        }
        return ret;
      }
      break;
    }
    // Fall-through
  case 'off':
    // assumes there are no non-BMP characters;
    //    if there may be such characters, then it is best to turn it on (critical in true XHTML/XML)
  default:
    if (start < 0) {
      start += end;
    }
    end = typeof len === 'undefined' ? end : (len < 0 ? len + end : len + start);
    // PHP returns false if start does not fall within the string.
    // PHP returns false if the calculated end comes before the calculated start.
    // PHP returns an empty string if start and end are the same.
    // Otherwise, PHP returns the portion of the string from start to end.
    return start >= str.length || start < 0 || start > end ? !1 : str.slice(start, end);
  }
  // Please Netbeans
  return undefined;
}
function htmlentities(string, quote_style, charset, double_encode) {
  //  discuss at: http://phpjs.org/functions/htmlentities/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: nobbler
  // improved by: Jack
  // improved by: Rafał Kukawski (http://blog.kukawski.pl)
  // improved by: Dj (http://phpjs.org/functions/htmlentities:425#comment_134018)
  // bugfixed by: Onno Marsman
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //    input by: Ratheous
  //  depends on: get_html_translation_table
  //        note: function is compatible with PHP 5.2 and older
  //   example 1: htmlentities('Kevin & van Zonneveld');
  //   returns 1: 'Kevin &amp; van Zonneveld'
  //   example 2: htmlentities("foo'bar","ENT_QUOTES");
  //   returns 2: 'foo&#039;bar'

  var hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style),
    symbol = '';

  string = string == null ? '' : string + '';

  if (!hash_map) {
    return false;
  }

  if (quote_style && quote_style === 'ENT_QUOTES') {
    hash_map["'"] = '&#039;';
  }

  double_encode = double_encode == null || !!double_encode;

  var regex = new RegExp("&(?:#\\d+|#x[\\da-f]+|[a-zA-Z][\\da-z]*);|[" +
    Object.keys(hash_map)
    .join("")
    // replace regexp special chars
    .replace(/([()[\]{}\-.*+?^$|\/\\])/g, "\\$1") + "]",
    "g");

  return string.replace(regex, function(ent) {
    if (ent.length > 1) {
      return double_encode ? hash_map["&"] + ent.substr(1) : ent;
    }

    return hash_map[ent];
  });
}
function htmlspecialchars(string, quote_style, charset, double_encode) {
  //       discuss at: http://phpjs.org/functions/htmlspecialchars/
  //      original by: Mirek Slugen
  //      improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      bugfixed by: Nathan
  //      bugfixed by: Arno
  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
  //       revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //         input by: Ratheous
  //         input by: Mailfaker (http://www.weedem.fr/)
  //         input by: felix
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //             note: charset argument not supported
  //        example 1: htmlspecialchars("<a href='test'>Test</a>", 'ENT_QUOTES');
  //        returns 1: '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
  //        example 2: htmlspecialchars("ab\"c'd", ['ENT_NOQUOTES', 'ENT_QUOTES']);
  //        returns 2: 'ab"c&#039;d'
  //        example 3: htmlspecialchars('my "&entity;" is still here', null, null, false);
  //        returns 3: 'my &quot;&entity;&quot; is still here'

  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined' || quote_style === null) {
    quote_style = 2;
  }
  string = string.toString();
  if (double_encode !== false) {
    // Put this first to avoid double-encoding
    string = string.replace(/&/g, '&amp;');
  }
  string = string.replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

  var OPTS = {
    'ENT_NOQUOTES'          : 0,
    'ENT_HTML_QUOTE_SINGLE' : 1,
    'ENT_HTML_QUOTE_DOUBLE' : 2,
    'ENT_COMPAT'            : 2,
    'ENT_QUOTES'            : 3,
    'ENT_IGNORE'            : 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') {
    // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/'/g, '&#039;');
  }
  if (!noquotes) {
    string = string.replace(/"/g, '&quot;');
  }

  return string;
}
function htmlspecialchars_decode(string, quote_style) {
  //       discuss at: http://phpjs.org/functions/htmlspecialchars_decode/
  //      original by: Mirek Slugen
  //      improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      bugfixed by: Mateusz "loonquawl" Zalega
  //      bugfixed by: Onno Marsman
  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
  //         input by: ReverseSyntax
  //         input by: Slawomir Kaniecki
  //         input by: Scott Cariss
  //         input by: Francois
  //         input by: Ratheous
  //         input by: Mailfaker (http://www.weedem.fr/)
  //       revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //        example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
  //        returns 1: '<p>this -> &quot;</p>'
  //        example 2: htmlspecialchars_decode("&amp;quot;");
  //        returns 2: '&quot;'

  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString()
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
  var OPTS = {
    'ENT_NOQUOTES'          : 0,
    'ENT_HTML_QUOTE_SINGLE' : 1,
    'ENT_HTML_QUOTE_DOUBLE' : 2,
    'ENT_COMPAT'            : 2,
    'ENT_QUOTES'            : 3,
    'ENT_IGNORE'            : 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') {
    // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');

  return string;
}
function checkdate(m, d, y) {
  //  discuss at: http://phpjs.org/functions/checkdate/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Pyerre
  // improved by: Theriault
  //   example 1: checkdate(12, 31, 2000);
  //   returns 1: true
  //   example 2: checkdate(2, 29, 2001);
  //   returns 2: false
  //   example 3: checkdate(3, 31, 2008);
  //   returns 3: true
  //   example 4: checkdate(1, 390, 2000);
  //   returns 4: false

  return m > 0 && m < 13 && y > 0 && y < 32768 && d > 0 && d <= (new Date(y, m, 0))
    .getDate();
}
function date(format, timestamp) {
  //  discuss at: http://phpjs.org/functions/date/
  // original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
  // original by: gettimeofday
  //    parts by: Peter-Paul Koch (http://www.quirksmode.org/js/beat.html)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: MeEtc (http://yass.meetcweb.com)
  // improved by: Brad Touesnard
  // improved by: Tim Wiel
  // improved by: Bryan Elliott
  // improved by: David Randall
  // improved by: Theriault
  // improved by: Theriault
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Theriault
  // improved by: Thomas Beaucourt (http://www.webapp.fr)
  // improved by: JT
  // improved by: Theriault
  // improved by: Rafał Kukawski (http://blog.kukawski.pl)
  // improved by: Theriault
  //    input by: Brett Zamir (http://brett-zamir.me)
  //    input by: majak
  //    input by: Alex
  //    input by: Martin
  //    input by: Alex Wilson
  //    input by: Haravikk
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: majak
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: omid (http://phpjs.org/functions/380:380#comment_137122)
  // bugfixed by: Chris (http://www.devotis.nl/)
  //        note: Uses global: php_js to store the default timezone
  //        note: Although the function potentially allows timezone info (see notes), it currently does not set
  //        note: per a timezone specified by date_default_timezone_set(). Implementers might use
  //        note: this.php_js.currentTimezoneOffset and this.php_js.currentTimezoneDST set by that function
  //        note: in order to adjust the dates in this function (or our other date functions!) accordingly
  //   example 1: date('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400);
  //   returns 1: '09:09:40 m is month'
  //   example 2: date('F j, Y, g:i a', 1062462400);
  //   returns 2: 'September 2, 2003, 2:26 am'
  //   example 3: date('Y W o', 1062462400);
  //   returns 3: '2003 36 2003'
  //   example 4: x = date('Y m d', (new Date()).getTime()/1000);
  //   example 4: (x+'').length == 10 // 2009 01 09
  //   returns 4: true
  //   example 5: date('W', 1104534000);
  //   returns 5: '53'
  //   example 6: date('B t', 1104534000);
  //   returns 6: '999 31'
  //   example 7: date('W U', 1293750000.82); // 2010-12-31
  //   returns 7: '52 1293750000'
  //   example 8: date('W', 1293836400); // 2011-01-01
  //   returns 8: '52'
  //   example 9: date('W Y-m-d', 1293974054); // 2011-01-02
  //   returns 9: '52 2011-01-02'

  var that = this;
  var jsdate, f;
  // Keep this here (works, but for code commented-out below for file size reasons)
  // var tal= [];
  var txt_words = [
    'Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur',
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  // trailing backslash -> (dropped)
  // a backslash followed by any character (including backslash) -> the character
  // empty string -> empty string
  var formatChr = /\\?(.?)/gi;
  var formatChrCb = function(t, s) {
    return f[t] ? f[t]() : s;
  };
  var _pad = function(n, c) {
    n = String(n);
    while (n.length < c) {
      n = '0' + n;
    }
    return n;
  };
  f = {
    // Day
    d : function() {
      // Day of month w/leading 0; 01..31
      return _pad(f.j(), 2);
    },
    D : function() {
      // Shorthand day name; Mon...Sun
      return f.l()
        .slice(0, 3);
    },
    j : function() {
      // Day of month; 1..31
      return jsdate.getDate();
    },
    l : function() {
      // Full day name; Monday...Sunday
      return txt_words[f.w()] + 'day';
    },
    N : function() {
      // ISO-8601 day of week; 1[Mon]..7[Sun]
      return f.w() || 7;
    },
    S : function() {
      // Ordinal suffix for day of month; st, nd, rd, th
      var j = f.j();
      var i = j % 10;
      if (i <= 3 && parseInt((j % 100) / 10, 10) == 1) {
        i = 0;
      }
      return ['st', 'nd', 'rd'][i - 1] || 'th';
    },
    w : function() {
      // Day of week; 0[Sun]..6[Sat]
      return jsdate.getDay();
    },
    z : function() {
      // Day of year; 0..365
      var a = new Date(f.Y(), f.n() - 1, f.j());
      var b = new Date(f.Y(), 0, 1);
      return Math.round((a - b) / 864e5);
    },

    // Week
    W : function() {
      // ISO-8601 week number
      var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3);
      var b = new Date(a.getFullYear(), 0, 4);
      return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
    },

    // Month
    F : function() {
      // Full month name; January...December
      return txt_words[6 + f.n()];
    },
    m : function() {
      // Month w/leading 0; 01...12
      return _pad(f.n(), 2);
    },
    M : function() {
      // Shorthand month name; Jan...Dec
      return f.F()
        .slice(0, 3);
    },
    n : function() {
      // Month; 1...12
      return jsdate.getMonth() + 1;
    },
    t : function() {
      // Days in month; 28...31
      return (new Date(f.Y(), f.n(), 0))
        .getDate();
    },

    // Year
    L : function() {
      // Is leap year?; 0 or 1
      var j = f.Y();
      return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
    },
    o : function() {
      // ISO-8601 year
      var n = f.n();
      var W = f.W();
      var Y = f.Y();
      return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
    },
    Y : function() {
      // Full year; e.g. 1980...2010
      return jsdate.getFullYear();
    },
    y : function() {
      // Last two digits of year; 00...99
      return f.Y()
        .toString()
        .slice(-2);
    },

    // Time
    a : function() {
      // am or pm
      return jsdate.getHours() > 11 ? 'pm' : 'am';
    },
    A : function() {
      // AM or PM
      return f.a()
        .toUpperCase();
    },
    B : function() {
      // Swatch Internet time; 000..999
      var H = jsdate.getUTCHours() * 36e2;
      // Hours
      var i = jsdate.getUTCMinutes() * 60;
      // Minutes
      // Seconds
      var s = jsdate.getUTCSeconds();
      return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
    },
    g : function() {
      // 12-Hours; 1..12
      return f.G() % 12 || 12;
    },
    G : function() {
      // 24-Hours; 0..23
      return jsdate.getHours();
    },
    h : function() {
      // 12-Hours w/leading 0; 01..12
      return _pad(f.g(), 2);
    },
    H : function() {
      // 24-Hours w/leading 0; 00..23
      return _pad(f.G(), 2);
    },
    i : function() {
      // Minutes w/leading 0; 00..59
      return _pad(jsdate.getMinutes(), 2);
    },
    s : function() {
      // Seconds w/leading 0; 00..59
      return _pad(jsdate.getSeconds(), 2);
    },
    u : function() {
      // Microseconds; 000000-999000
      return _pad(jsdate.getMilliseconds() * 1000, 6);
    },

    // Timezone
    e : function() {
      // Timezone identifier; e.g. Atlantic/Azores, ...
      // The following works, but requires inclusion of the very large
      // timezone_abbreviations_list() function.
      /*              return that.date_default_timezone_get();
       */
      throw 'Not supported (see source code of date() for timezone on how to add support)';
    },
    I : function() {
      // DST observed?; 0 or 1
      // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
      // If they are not equal, then DST is observed.
      var a = new Date(f.Y(), 0);
      // Jan 1
      var c = Date.UTC(f.Y(), 0);
      // Jan 1 UTC
      var b = new Date(f.Y(), 6);
      // Jul 1
      // Jul 1 UTC
      var d = Date.UTC(f.Y(), 6);
      return ((a - c) !== (b - d)) ? 1 : 0;
    },
    O : function() {
      // Difference to GMT in hour format; e.g. +0200
      var tzo = jsdate.getTimezoneOffset();
      var a = Math.abs(tzo);
      return (tzo > 0 ? '-' : '+') + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
    },
    P : function() {
      // Difference to GMT w/colon; e.g. +02:00
      var O = f.O();
      return (O.substr(0, 3) + ':' + O.substr(3, 2));
    },
    T : function() {
      // Timezone abbreviation; e.g. EST, MDT, ...
      // The following works, but requires inclusion of the very
      // large timezone_abbreviations_list() function.
      /*              var abbr, i, os, _default;
      if (!tal.length) {
        tal = that.timezone_abbreviations_list();
      }
      if (that.php_js && that.php_js.default_timezone) {
        _default = that.php_js.default_timezone;
        for (abbr in tal) {
          for (i = 0; i < tal[abbr].length; i++) {
            if (tal[abbr][i].timezone_id === _default) {
              return abbr.toUpperCase();
            }
          }
        }
      }
      for (abbr in tal) {
        for (i = 0; i < tal[abbr].length; i++) {
          os = -jsdate.getTimezoneOffset() * 60;
          if (tal[abbr][i].offset === os) {
            return abbr.toUpperCase();
          }
        }
      }
      */
      return 'UTC';
    },
    Z : function() {
      // Timezone offset in seconds (-43200...50400)
      return -jsdate.getTimezoneOffset() * 60;
    },

    // Full Date/Time
    c : function() {
      // ISO-8601 date.
      return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
    },
    r : function() {
      // RFC 2822
      return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
    },
    U : function() {
      // Seconds since UNIX epoch
      return jsdate / 1000 | 0;
    }
  };
  this.date = function(format, timestamp) {
    that = this;
    jsdate = (timestamp === undefined ? new Date() : // Not provided
      (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
      new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
    );
    return format.replace(formatChr, formatChrCb);
  };
  return this.date(format, timestamp);
}
function date_parse(date) {
  //  discuss at: http://phpjs.org/functions/date_parse/
  // original by: Brett Zamir (http://brett-zamir.me)
  //  depends on: strtotime
  //   example 1: date_parse('2006-12-12 10:00:00.5');
  //   returns 1: {year : 2006, month: 12, day: 12, hour: 10, minute: 0, second: 0, fraction: 0.5, warning_count: 0, warnings: [], error_count: 0, errors: [], is_localtime: false}

  // BEGIN REDUNDANT
  this.php_js = this.php_js || {};
  // END REDUNDANT

  var ts,
    warningsOffset = this.php_js.warnings ? this.php_js.warnings.length : null,
    errorsOffset = this.php_js.errors ? this.php_js.errors.length : null;

  try {
    // Allow strtotime to return a decimal (which it normally does not)
    this.php_js.date_parse_state = true;
    ts = this.strtotime(date);
    this.php_js.date_parse_state = false;
  } finally {
    if (!ts) {
      return false;
    }
  }

  var dt = new Date(ts * 1000);

  var retObj = {
    // Grab any new warnings or errors added (not implemented yet in strtotime()); throwing warnings, notices, or errors could also be easily monitored by using 'watch' on this.php_js.latestWarning, etc. and/or calling any defined error handlers
    warning_count : warningsOffset !== null ? this.php_js.warnings.slice(warningsOffset)
      .length : 0,
    warnings      : warningsOffset !== null ? this.php_js.warnings.slice(warningsOffset) : [],
    error_count   : errorsOffset !== null ? this.php_js.errors.slice(errorsOffset)
      .length : 0,
    errors        : errorsOffset !== null ? this.php_js.errors.slice(errorsOffset) : []
  };
  retObj.year = dt.getFullYear();
  retObj.month = dt.getMonth() + 1;
  retObj.day = dt.getDate();
  retObj.hour = dt.getHours();
  retObj.minute = dt.getMinutes();
  retObj.second = dt.getSeconds();
  retObj.fraction = parseFloat('0.' + dt.getMilliseconds());
  retObj.is_localtime = dt.getTimezoneOffset() !== 0;

  return retObj;
}
function getdate(timestamp) {
  //  discuss at: http://phpjs.org/functions/getdate/
  // original by: Paulo Freitas
  //    input by: Alex
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //   example 1: getdate(1055901520);
  //   returns 1: {'seconds': 40, 'minutes': 58, 'hours': 21, 'mday': 17, 'wday': 2, 'mon': 6, 'year': 2003, 'yday': 167, 'weekday': 'Tuesday', 'month': 'June', '0': 1055901520}

  var _w = ['Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur'];
  var _m = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October',
    'November', 'December'
  ];
  var d = ((typeof timestamp === 'undefined') ? new Date() : // Not provided
    (typeof timestamp === 'object') ? new Date(timestamp) : // Javascript Date()
    new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
  );
  var w = d.getDay();
  var m = d.getMonth();
  var y = d.getFullYear();
  var r = {};

  r.seconds = d.getSeconds();
  r.minutes = d.getMinutes();
  r.hours = d.getHours();
  r.mday = d.getDate();
  r.wday = w;
  r.mon = m + 1;
  r.year = y;
  r.yday = Math.floor((d - (new Date(y, 0, 1))) / 86400000);
  r.weekday = _w[w] + 'day';
  r.month = _m[m];
  r['0'] = parseInt(d.getTime() / 1000, 10);

  return r;
}
function strtotime(text, now) {
  //  discuss at: http://phpjs.org/functions/strtotime/
  //     version: 1109.2016
  // original by: Caio Ariede (http://caioariede.com)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Caio Ariede (http://caioariede.com)
  // improved by: A. Matías Quezada (http://amatiasq.com)
  // improved by: preuter
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Mirko Faber
  //    input by: David
  // bugfixed by: Wagner B. Soares
  // bugfixed by: Artur Tchernychev
  // bugfixed by: Stephan Bösch-Plepelits (http://github.com/plepe)
  //        note: Examples all have a fixed timestamp to prevent tests to fail because of variable time(zones)
  //   example 1: strtotime('+1 day', 1129633200);
  //   returns 1: 1129719600
  //   example 2: strtotime('+1 week 2 days 4 hours 2 seconds', 1129633200);
  //   returns 2: 1130425202
  //   example 3: strtotime('last month', 1129633200);
  //   returns 3: 1127041200
  //   example 4: strtotime('2009-05-04 08:30:00 GMT');
  //   returns 4: 1241425800
  //   example 5: strtotime('2009-05-04 08:30:00+00');
  //   returns 5: 1241425800
  //   example 6: strtotime('2009-05-04 08:30:00+02:00');
  //   returns 6: 1241418600
  //   example 7: strtotime('2009-05-04T08:30:00Z');
  //   returns 7: 1241425800

  var parsed, match, today, year, date, days, ranges, len, times, regex, i, fail = false;

  if (!text) {
    return fail;
  }

  // Unecessary spaces
  text = text.replace(/^\s+|\s+$/g, '')
    .replace(/\s{2,}/g, ' ')
    .replace(/[\t\r\n]/g, '')
    .toLowerCase();

  // in contrast to php, js Date.parse function interprets:
  // dates given as yyyy-mm-dd as in timezone: UTC,
  // dates with "." or "-" as MDY instead of DMY
  // dates with two-digit years differently
  // etc...etc...
  // ...therefore we manually parse lots of common date formats
  match = text.match(
    /^(\d{1,4})([\-\.\/\:])(\d{1,2})([\-\.\/\:])(\d{1,4})(?:\s(\d{1,2}):(\d{2})?:?(\d{2})?)?(?:\s([A-Z]+)?)?$/);

  if (match && match[2] === match[4]) {
    if (match[1] > 1901) {
      switch (match[2]) {
      case '-': {
        // YYYY-M-D
        if (match[3] > 12 || match[5] > 31) {
          return fail;
        }

        return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
      }
      case '.': {
        // YYYY.M.D is not parsed by strtotime()
        return fail;
      }
      case '/': {
        // YYYY/M/D
        if (match[3] > 12 || match[5] > 31) {
          return fail;
        }

        return new Date(match[1], parseInt(match[3], 10) - 1, match[5],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
      }
      }
    } else if (match[5] > 1901) {
      switch (match[2]) {
      case '-': {
        // D-M-YYYY
        if (match[3] > 12 || match[1] > 31) {
          return fail;
        }

        return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
      }
      case '.': {
        // D.M.YYYY
        if (match[3] > 12 || match[1] > 31) {
          return fail;
        }

        return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
      }
      case '/': {
        // M/D/YYYY
        if (match[1] > 12 || match[3] > 31) {
          return fail;
        }

        return new Date(match[5], parseInt(match[1], 10) - 1, match[3],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
      }
      }
    } else {
      switch (match[2]) {
      case '-': {
        // YY-M-D
        if (match[3] > 12 || match[5] > 31 || (match[1] < 70 && match[1] > 38)) {
          return fail;
        }

        year = match[1] >= 0 && match[1] <= 38 ? +match[1] + 2000 : match[1];
        return new Date(year, parseInt(match[3], 10) - 1, match[5],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
      }
      case '.': {
        // D.M.YY or H.MM.SS
        if (match[5] >= 70) {
          // D.M.YY
          if (match[3] > 12 || match[1] > 31) {
            return fail;
          }

          return new Date(match[5], parseInt(match[3], 10) - 1, match[1],
            match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
        }
        if (match[5] < 60 && !match[6]) {
          // H.MM.SS
          if (match[1] > 23 || match[3] > 59) {
            return fail;
          }

          today = new Date();
          return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
            match[1] || 0, match[3] || 0, match[5] || 0, match[9] || 0) / 1000;
        }

        // invalid format, cannot be parsed
        return fail;
      }
      case '/': {
        // M/D/YY
        if (match[1] > 12 || match[3] > 31 || (match[5] < 70 && match[5] > 38)) {
          return fail;
        }

        year = match[5] >= 0 && match[5] <= 38 ? +match[5] + 2000 : match[5];
        return new Date(year, parseInt(match[1], 10) - 1, match[3],
          match[6] || 0, match[7] || 0, match[8] || 0, match[9] || 0) / 1000;
      }
      case ':': {
        // HH:MM:SS
        if (match[1] > 23 || match[3] > 59 || match[5] > 59) {
          return fail;
        }

        today = new Date();
        return new Date(today.getFullYear(), today.getMonth(), today.getDate(),
          match[1] || 0, match[3] || 0, match[5] || 0) / 1000;
      }
      }
    }
  }

  // other formats and "now" should be parsed by Date.parse()
  if (text === 'now') {
    return now === null || isNaN(now) ? new Date()
      .getTime() / 1000 | 0 : now | 0;
  }
  if (!isNaN(parsed = Date.parse(text))) {
    return parsed / 1000 | 0;
  }
  // Browsers != Chrome have problems parsing ISO 8601 date strings, as they do
  // not accept lower case characters, space, or shortened time zones.
  // Therefore, fix these problems and try again.
  // Examples:
  //   2015-04-15 20:33:59+02
  //   2015-04-15 20:33:59z
  //   2015-04-15t20:33:59+02:00
  if (match = text.match(
      /^([0-9]{4}-[0-9]{2}-[0-9]{2})[ t]([0-9]{2}:[0-9]{2}:[0-9]{2}(\.[0-9]+)?)([\+-][0-9]{2}(:[0-9]{2})?|z)/)) {
    // fix time zone information
    if (match[4] == 'z') {
      match[4] = 'Z';
    } else if (match[4].match(/^([\+-][0-9]{2})$/)) {
      match[4] = match[4] + ':00';
    }

    if (!isNaN(parsed = Date.parse(match[1] + 'T' + match[2] + match[4]))) {
      return parsed / 1000 | 0;
    }
  }

  date = now ? new Date(now * 1000) : new Date();
  days = {
    'sun' : 0,
    'mon' : 1,
    'tue' : 2,
    'wed' : 3,
    'thu' : 4,
    'fri' : 5,
    'sat' : 6
  };
  ranges = {
    'yea' : 'FullYear',
    'mon' : 'Month',
    'day' : 'Date',
    'hou' : 'Hours',
    'min' : 'Minutes',
    'sec' : 'Seconds'
  };

  function lastNext(type, range, modifier) {
    var diff, day = days[range];

    if (typeof day !== 'undefined') {
      diff = day - date.getDay();

      if (diff === 0) {
        diff = 7 * modifier;
      } else if (diff > 0 && type === 'last') {
        diff -= 7;
      } else if (diff < 0 && type === 'next') {
        diff += 7;
      }

      date.setDate(date.getDate() + diff);
    }
  }

  function process(val) {
    var splt = val.split(' '), // Todo: Reconcile this with regex using \s, taking into account browser issues with split and regexes
      type = splt[0],
      range = splt[1].substring(0, 3),
      typeIsNumber = /\d+/.test(type),
      ago = splt[2] === 'ago',
      num = (type === 'last' ? -1 : 1) * (ago ? -1 : 1);

    if (typeIsNumber) {
      num *= parseInt(type, 10);
    }

    if (ranges.hasOwnProperty(range) && !splt[1].match(/^mon(day|\.)?$/i)) {
      return date['set' + ranges[range]](date['get' + ranges[range]]() + num);
    }

    if (range === 'wee') {
      return date.setDate(date.getDate() + (num * 7));
    }

    if (type === 'next' || type === 'last') {
      lastNext(type, range, num);
    } else if (!typeIsNumber) {
      return false;
    }

    return true;
  }

  times = '(years?|months?|weeks?|days?|hours?|minutes?|min|seconds?|sec' +
    '|sunday|sun\\.?|monday|mon\\.?|tuesday|tue\\.?|wednesday|wed\\.?' +
    '|thursday|thu\\.?|friday|fri\\.?|saturday|sat\\.?)';
  regex = '([+-]?\\d+\\s' + times + '|' + '(last|next)\\s' + times + ')(\\sago)?';

  match = text.match(new RegExp(regex, 'gi'));
  if (!match) {
    return fail;
  }

  for (i = 0, len = match.length; i < len; i++) {
    if (!process(match[i])) {
      return fail;
    }
  }

  // ECMAScript 5 only
  // if (!match.every(process))
  //    return false;

  return (date.getTime() / 1000);
}
function time() {
  //  discuss at: http://phpjs.org/functions/time/
  // original by: GeekFG (http://geekfg.blogspot.com)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: metjay
  // improved by: HKM
  //   example 1: timeStamp = time();
  //   example 1: timeStamp > 1000000000 && timeStamp < 2000000000
  //   returns 1: true

  return Math.floor(new Date()
    .getTime() / 1000);
}
function base64_decode(data) {
  //  discuss at: http://phpjs.org/functions/base64_decode/
  // original by: Tyler Akins (http://rumkin.com)
  // improved by: Thunder.m
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Aman Gupta
  //    input by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Onno Marsman
  // bugfixed by: Pellentesque Malesuada
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //   example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
  //   returns 1: 'Kevin van Zonneveld'
  //   example 2: base64_decode('YQ===');
  //   returns 2: 'a'
  //   example 3: base64_decode('4pyTIMOgIGxhIG1vZGU=');
  //   returns 3: '✓ à la mode'

  var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
  var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
    ac = 0,
    dec = '',
    tmp_arr = [];

  if (!data) {
    return data;
  }

  data += '';

  do {
    // unpack four hexets into three octets using index points in b64
    h1 = b64.indexOf(data.charAt(i++));
    h2 = b64.indexOf(data.charAt(i++));
    h3 = b64.indexOf(data.charAt(i++));
    h4 = b64.indexOf(data.charAt(i++));

    bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

    o1 = bits >> 16 & 0xff;
    o2 = bits >> 8 & 0xff;
    o3 = bits & 0xff;

    if (h3 == 64) {
      tmp_arr[ac++] = String.fromCharCode(o1);
    } else if (h4 == 64) {
      tmp_arr[ac++] = String.fromCharCode(o1, o2);
    } else {
      tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
    }
  } while (i < data.length);

  dec = tmp_arr.join('');

  return decodeURIComponent(escape(dec.replace(/\0+$/, '')));
}
function base64_encode(data) {
  //  discuss at: http://phpjs.org/functions/base64_encode/
  // original by: Tyler Akins (http://rumkin.com)
  // improved by: Bayron Guevara
  // improved by: Thunder.m
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Rafał Kukawski (http://kukawski.pl)
  // bugfixed by: Pellentesque Malesuada
  //   example 1: base64_encode('Kevin van Zonneveld');
  //   returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
  //   example 2: base64_encode('a');
  //   returns 2: 'YQ=='
  //   example 3: base64_encode('✓ à la mode');
  //   returns 3: '4pyTIMOgIGxhIG1vZGU='

  var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
  var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
    ac = 0,
    enc = '',
    tmp_arr = [];

  if (!data) {
    return data;
  }

  data = unescape(encodeURIComponent(data));

  do {
    // pack three octets into four hexets
    o1 = data.charCodeAt(i++);
    o2 = data.charCodeAt(i++);
    o3 = data.charCodeAt(i++);

    bits = o1 << 16 | o2 << 8 | o3;

    h1 = bits >> 18 & 0x3f;
    h2 = bits >> 12 & 0x3f;
    h3 = bits >> 6 & 0x3f;
    h4 = bits & 0x3f;

    // use hexets to index into b64, and append result to encoded string
    tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
  } while (i < data.length);

  enc = tmp_arr.join('');

  var r = data.length % 3;

  return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}
function get_headers(url, format) {
  //  discuss at: http://phpjs.org/functions/get_headers/
  // original by: Paulo Freitas
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //  depends on: array_filter
  //        note: This function uses XmlHttpRequest and cannot retrieve resource from different domain.
  //        note: Synchronous so may lock up browser, mainly here for study purposes.
  //        test: skip
  //   example 1: get_headers('http://kevin.vanzonneveld.net/pj_test_supportfile_1.htm')[0];
  //   returns 1: 'Date: Wed, 13 May 2009 23:53:11 GMT'

  var req = this.window.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest();

  if (!req) {
    throw new Error('XMLHttpRequest not supported');
  }
  var tmp, headers, pair, i, j = 0;
  ß;
  req.open('HEAD', url, false);
  req.send(null);

  if (req.readyState < 3) {
    return false;
  }

  tmp = req.getAllResponseHeaders();
  tmp = tmp.split('\n');
  tmp = this.array_filter(tmp, function(value) {
    return value.substring(1) !== '';
  });
  headers = format ? {} : [];

  for (var i in tmp) {
    if (format) {
      pair = tmp[i].split(':');
      headers[pair.splice(0, 1)] = pair.join(':')
        .substring(1);
    } else {
      headers[j++] = tmp[i];
    }
  }

  return headers;
}
function get_meta_tags(file) {
  //  discuss at: http://phpjs.org/functions/get_meta_tags/
  // original by: Brett Zamir (http://brett-zamir.me)
  //        note: This function uses XmlHttpRequest and cannot retrieve resource from different domain.
  //        note: Synchronous so may lock up browser, mainly here for study purposes.
  //  depends on: file_get_contents
  //        test: skip
  //   example 1: get_meta_tags('http://kevin.vanzonneveld.net/pj_test_supportfile_2.htm');
  //   returns 1: {description: 'a php manual', author: 'name', keywords: 'php documentation', 'geo_position': '49.33;-86.59'}

  var fulltxt = '';

  if (false) {
    // Use this for testing instead of the line above:
    fulltxt = '<meta name="author" content="name">' + '<meta name="keywords" content="php documentation">' +
      '<meta name="DESCRIPTION" content="a php manual">' + '<meta name="geo.position" content="49.33;-86.59">' +
      '</head>';
  } else {
    fulltxt = this.file_get_contents(file)
      .match(/^[\s\S]*<\/head>/i); // We have to disallow some character, so we choose a Unicode non-character
  }

  var patt = /<meta[^>]*?>/gim;
  var patt1 = /<meta\s+.*?name\s*=\s*(['"]?)(.*?)\1\s+.*?content\s*=\s*(['"]?)(.*?)\3/gim;
  var patt2 = /<meta\s+.*?content\s*=\s*(['"?])(.*?)\1\s+.*?name\s*=\s*(['"]?)(.*?)\3/gim;
  var txt, match, name, arr = {};

  while ((txt = patt.exec(fulltxt)) !== null) {
    while ((match = patt1.exec(txt)) !== null) {
      name = match[2].replace(/\W/g, '_')
        .toLowerCase();
      arr[name] = match[4];
    }
    while ((match = patt2.exec(txt)) !== null) {
      name = match[4].replace(/\W/g, '_')
        .toLowerCase();
      arr[name] = match[2];
    }
  }
  return arr;
}
function http_build_query(formdata, numeric_prefix, arg_separator) {
  //  discuss at: http://phpjs.org/functions/http_build_query/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Legaev Andrey
  // improved by: Michael White (http://getsprink.com)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Brett Zamir (http://brett-zamir.me)
  //  revised by: stag019
  //    input by: Dreamer
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: MIO_KODUKI (http://mio-koduki.blogspot.com/)
  //        note: If the value is null, key and value are skipped in the http_build_query of PHP while in phpjs they are not.
  //  depends on: urlencode
  //   example 1: http_build_query({foo: 'bar', php: 'hypertext processor', baz: 'boom', cow: 'milk'}, '', '&amp;');
  //   returns 1: 'foo=bar&amp;php=hypertext+processor&amp;baz=boom&amp;cow=milk'
  //   example 2: http_build_query({'php': 'hypertext processor', 0: 'foo', 1: 'bar', 2: 'baz', 3: 'boom', 'cow': 'milk'}, 'myvar_');
  //   returns 2: 'myvar_0=foo&myvar_1=bar&myvar_2=baz&myvar_3=boom&php=hypertext+processor&cow=milk'

  var value, key, tmp = [],
    that = this;

  var _http_build_query_helper = function(key, val, arg_separator) {
    var k, tmp = [];
    if (val === true) {
      val = '1';
    } else if (val === false) {
      val = '0';
    }
    if (val != null) {
      if (typeof val === 'object') {
        for (k in val) {
          if (val[k] != null) {
            tmp.push(_http_build_query_helper(key + '[' + k + ']', val[k], arg_separator));
          }
        }
        return tmp.join(arg_separator);
      } else if (typeof val !== 'function') {
        return that.urlencode(key) + '=' + that.urlencode(val);
      } else {
        throw new Error('There was an error processing for http_build_query().');
      }
    } else {
      return '';
    }
  };

  if (!arg_separator) {
    arg_separator = '&';
  }
  for (key in formdata) {
    value = formdata[key];
    if (numeric_prefix && !isNaN(key)) {
      key = String(numeric_prefix) + key;
    }
    var query = _http_build_query_helper(key, value, arg_separator);
    if (query !== '') {
      tmp.push(query);
    }
  }

  return tmp.join(arg_separator);
}
function parse_url(str, component) {
  //       discuss at: http://phpjs.org/functions/parse_url/
  //      original by: Steven Levithan (http://blog.stevenlevithan.com)
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //         input by: Lorenzo Pisani
  //         input by: Tony
  //      improved by: Brett Zamir (http://brett-zamir.me)
  //             note: original by http://stevenlevithan.com/demo/parseuri/js/assets/parseuri.js
  //             note: blog post at http://blog.stevenlevithan.com/archives/parseuri
  //             note: demo at http://stevenlevithan.com/demo/parseuri/js/assets/parseuri.js
  //             note: Does not replace invalid characters with '_' as in PHP, nor does it return false with
  //             note: a seriously malformed URL.
  //             note: Besides function name, is essentially the same as parseUri as well as our allowing
  //             note: an extra slash after the scheme/protocol (to allow file:/// as in PHP)
  //        example 1: parse_url('http://username:password@hostname/path?arg=value#anchor');
  //        returns 1: {scheme: 'http', host: 'hostname', user: 'username', pass: 'password', path: '/path', query: 'arg=value', fragment: 'anchor'}
  //        example 2: parse_url('http://en.wikipedia.org/wiki/%22@%22_%28album%29');
  //        returns 2: {scheme: 'http', host: 'en.wikipedia.org', path: '/wiki/%22@%22_%28album%29'}
  //        example 3: parse_url('https://host.domain.tld/a@b.c/folder')
  //        returns 3: {scheme: 'https', host: 'host.domain.tld', path: '/a@b.c/folder'}
  //        example 4: parse_url('https://gooduser:secretpassword@www.example.com/a@b.c/folder?foo=bar');
  //        returns 4: { scheme: 'https', host: 'www.example.com', path: '/a@b.c/folder', query: 'foo=bar', user: 'gooduser', pass: 'secretpassword' }

  try {
    this.php_js = this.php_js || {};
  } catch (e) {
    this.php_js = {};
  }

  var query;
  var ini = (this.php_js && this.php_js.ini) || {};
  var mode = (ini['phpjs.parse_url.mode'] && ini['phpjs.parse_url.mode'].local_value) || 'php';
  var key = [
    'source',
    'scheme',
    'authority',
    'userInfo',
    'user',
    'pass',
    'host',
    'port',
    'relative',
    'path',
    'directory',
    'file',
    'query',
    'fragment'
  ];
  var parser = {
    php    : /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
    strict : /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
    loose  : /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // Added one optional slash to post-scheme to catch file:/// (should restrict this)
  };

  var m = parser[mode].exec(str);
  var uri = {};
  var i = 14;

  while (i--) {
    if (m[i]) {
      uri[key[i]] = m[i];
    }
  }

  if (component) {
    return uri[component.replace('PHP_URL_', '')
      .toLowerCase()];
  }

  if (mode !== 'php') {
    var name = (ini['phpjs.parse_url.queryKey'] &&
      ini['phpjs.parse_url.queryKey'].local_value) || 'queryKey';
    parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
    uri[name] = {};
    query = uri[key[12]] || '';
    query.replace(parser, function($0, $1, $2) {
      if ($1) {
        uri[name][$1] = $2;
      }
    });
  }

  delete uri.source;
  return uri;
}
function rawurldecode(str) {
  //       discuss at: http://phpjs.org/functions/rawurldecode/
  //      original by: Brett Zamir (http://brett-zamir.me)
  //         input by: travc
  //         input by: Brett Zamir (http://brett-zamir.me)
  //         input by: Ratheous
  //         input by: lovio
  //      bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //      improved by: Brett Zamir (http://brett-zamir.me)
  //             note: Please be aware that this function expects to decode from UTF-8 encoded strings, as found on
  //             note: pages served as UTF-8
  //        example 1: rawurldecode('Kevin+van+Zonneveld%21');
  //        returns 1: 'Kevin+van+Zonneveld!'
  //        example 2: rawurldecode('http%3A%2F%2Fkevin.vanzonneveld.net%2F');
  //        returns 2: 'http://kevin.vanzonneveld.net/'
  //        example 3: rawurldecode('http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3Dphp.js%26ie%3Dutf-8%26oe%3Dutf-8%26aq%3Dt%26rls%3Dcom.ubuntu%3Aen-US%3Aunofficial%26client%3Dfirefox-a');
  //        returns 3: 'http://www.google.nl/search?q=php.js&ie=utf-8&oe=utf-8&aq=t&rls=com.ubuntu:en-US:unofficial&client=firefox-a'

  return decodeURIComponent((str + '')
    .replace(/%(?![\da-f]{2})/gi, function() {
      // PHP tolerates poorly formed escape sequences
      return '%25';
    }));
}
function rawurlencode(str) {
  //       discuss at: http://phpjs.org/functions/rawurlencode/
  //      original by: Brett Zamir (http://brett-zamir.me)
  //         input by: travc
  //         input by: Brett Zamir (http://brett-zamir.me)
  //         input by: Michael Grier
  //         input by: Ratheous
  //      bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
  //      bugfixed by: Joris
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //             note: This reflects PHP 5.3/6.0+ behavior
  //             note: Please be aware that this function expects to encode into UTF-8 encoded strings, as found on
  //             note: pages served as UTF-8
  //        example 1: rawurlencode('Kevin van Zonneveld!');
  //        returns 1: 'Kevin%20van%20Zonneveld%21'
  //        example 2: rawurlencode('http://kevin.vanzonneveld.net/');
  //        returns 2: 'http%3A%2F%2Fkevin.vanzonneveld.net%2F'
  //        example 3: rawurlencode('http://www.google.nl/search?q=php.js&ie=utf-8&oe=utf-8&aq=t&rls=com.ubuntu:en-US:unofficial&client=firefox-a');
  //        returns 3: 'http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3Dphp.js%26ie%3Dutf-8%26oe%3Dutf-8%26aq%3Dt%26rls%3Dcom.ubuntu%3Aen-US%3Aunofficial%26client%3Dfirefox-a'

  str = (str + '')
    .toString();

  // Tilde should be allowed unescaped in future versions of PHP (as reflected below), but if you want to reflect current
  // PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
  return encodeURIComponent(str)
    .replace(/!/g, '%21')
    .replace(/'/g, '%27')
    .replace(/\(/g, '%28')
    .
  replace(/\)/g, '%29')
    .replace(/\*/g, '%2A');
}
function urldecode(str) {
  //       discuss at: http://phpjs.org/functions/urldecode/
  //      original by: Philip Peterson
  //      improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      improved by: Brett Zamir (http://brett-zamir.me)
  //      improved by: Lars Fischer
  //      improved by: Orlando
  //      improved by: Brett Zamir (http://brett-zamir.me)
  //      improved by: Brett Zamir (http://brett-zamir.me)
  //         input by: AJ
  //         input by: travc
  //         input by: Brett Zamir (http://brett-zamir.me)
  //         input by: Ratheous
  //         input by: e-mike
  //         input by: lovio
  //      bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      bugfixed by: Rob
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //             note: info on what encoding functions to use from: http://xkr.us/articles/javascript/encode-compare/
  //             note: Please be aware that this function expects to decode from UTF-8 encoded strings, as found on
  //             note: pages served as UTF-8
  //        example 1: urldecode('Kevin+van+Zonneveld%21');
  //        returns 1: 'Kevin van Zonneveld!'
  //        example 2: urldecode('http%3A%2F%2Fkevin.vanzonneveld.net%2F');
  //        returns 2: 'http://kevin.vanzonneveld.net/'
  //        example 3: urldecode('http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3Dphp.js%26ie%3Dutf-8%26oe%3Dutf-8%26aq%3Dt%26rls%3Dcom.ubuntu%3Aen-US%3Aunofficial%26client%3Dfirefox-a');
  //        returns 3: 'http://www.google.nl/search?q=php.js&ie=utf-8&oe=utf-8&aq=t&rls=com.ubuntu:en-US:unofficial&client=firefox-a'
  //        example 4: urldecode('%E5%A5%BD%3_4');
  //        returns 4: '\u597d%3_4'

  return decodeURIComponent((str + '')
    .replace(/%(?![\da-f]{2})/gi, function() {
      // PHP tolerates poorly formed escape sequences
      return '%25';
    })
    .replace(/\+/g, '%20'));
}
function urlencode(str) {
  //       discuss at: http://phpjs.org/functions/urlencode/
  //      original by: Philip Peterson
  //      improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      improved by: Brett Zamir (http://brett-zamir.me)
  //      improved by: Lars Fischer
  //         input by: AJ
  //         input by: travc
  //         input by: Brett Zamir (http://brett-zamir.me)
  //         input by: Ratheous
  //      bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      bugfixed by: Joris
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //             note: This reflects PHP 5.3/6.0+ behavior
  //             note: Please be aware that this function expects to encode into UTF-8 encoded strings, as found on
  //             note: pages served as UTF-8
  //        example 1: urlencode('Kevin van Zonneveld!');
  //        returns 1: 'Kevin+van+Zonneveld%21'
  //        example 2: urlencode('http://kevin.vanzonneveld.net/');
  //        returns 2: 'http%3A%2F%2Fkevin.vanzonneveld.net%2F'
  //        example 3: urlencode('http://www.google.nl/search?q=php.js&ie=utf-8&oe=utf-8&aq=t&rls=com.ubuntu:en-US:unofficial&client=firefox-a');
  //        returns 3: 'http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3Dphp.js%26ie%3Dutf-8%26oe%3Dutf-8%26aq%3Dt%26rls%3Dcom.ubuntu%3Aen-US%3Aunofficial%26client%3Dfirefox-a'

  str = (str + '')
    .toString();

  // Tilde should be allowed unescaped in future versions of PHP (as reflected below), but if you want to reflect current
  // PHP behavior, you would need to add ".replace(/~/g, '%7E');" to the following.
  return encodeURIComponent(str)
    .replace(/!/g, '%21')
    .replace(/'/g, '%27')
    .replace(/\(/g, '%28')
    .replace(/\)/g, '%29')
    .replace(/\*/g, '%2A')
    .replace(/%20/g, '+');
}