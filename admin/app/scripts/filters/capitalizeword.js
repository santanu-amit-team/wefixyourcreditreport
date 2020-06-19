'use strict';
/**
 * @ngdoc filter
 * @name codeBaseAdminApp.filter:capitalizeword
 * @function
 * @description
 * # capitalizeword
 * Filter in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp').filter('capitalizeword', function() {  
  return function(input){
    if(input.indexOf(' ') !== -1){
      var inputPieces,
          i;
      inputPieces = input.split(' ');

      for(i = 0; i < inputPieces.length; i++){
        inputPieces[i] = capitalizeString(inputPieces[i]);
      }

      return inputPieces.toString().replace(/,/g, ' ');
    }
    else {
      return capitalizeString(input);
    }

    function capitalizeString(inputString){
      return inputString.substring(0,1).toUpperCase() + inputString.substring(1);
    }
  };
});