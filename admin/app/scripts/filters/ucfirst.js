'use strict';
/**
 * @ngdoc filter
 * @name codeBaseAdminApp.filter:ucfirst
 * @function
 * @description
 * # ucfirst
 * Filter in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp')
	.filter('ucfirst', function() {
	    return function(input, scope) {
	        if (input != null) {
	            input = input.toLowerCase();
	            input = input.replace('_', ' ');
	            return input.substring(0, 1).toUpperCase() + input.substring(1);
	        }
	    }
	});