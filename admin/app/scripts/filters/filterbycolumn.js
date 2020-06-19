'use strict';
/**
 * @ngdoc filter
 * @name codeBaseAdminApp.filter:custom
 * @function
 * @description
 * # codeBaseAdminApp
 * Filter in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp').filter('filterByColumn', function($rootScope) {
    return function(data, columnsArray) {
        
        var result = [], expected, colVal;
        angular.forEach(columnsArray, function(colValue, colKey) {
            if(colValue.val !== ''){
                angular.forEach(data, function(value, key) {
                    if(colValue.type === 'string'){
                        expected = ('' + colValue.val).toLowerCase(); 
                        colVal = value[colValue.key].toLowerCase();
                        if(colVal.indexOf(expected) !== -1){
                            result.push(value);
                        }
                    }
                    else{
                        if(value[colValue.key] === colValue.val){
                            result.push(value);
                        }
                    }
                });
                data = result;
                result = [];
            }
        });
        result = data;
        return result;
    };
});