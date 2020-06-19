'use strict';

/**
 * @ngdoc filter
 * @name codeBaseAdminApp.filter:changeCase
 * @function
 * @description
 * # changeCase
 * Filter in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp')
  .filter('changeCase', function () {
    return function (input) {
      
      var _in = input.split('_');
      var _out = [];

      angular.forEach(_in, function(value, key) {

      	if (value.length == 2) {
      		_out.push(value.toUpperCase());
      	} else {
      		_out.push(value.substring(0, 1).toUpperCase() + value.substring(1));
      	}

      });

      return _out.join(' ');

    };
  });
