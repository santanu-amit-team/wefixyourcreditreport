'use strict';

/**
 * @ngdoc filter
 * @name codeBaseAdminApp.filter:strReplace
 * @function
 * @description
 * # strReplace
 * Filter in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp')
  .filter('strReplace', function () {
    return function (str, replacestr, replaceBy) {
    	if(!str){
    		return false;
    	}
        if(str.indexOf(replacestr) !== -1){
          str = str.replace(replacestr, replaceBy);
        }
        return str;
    };
  });
