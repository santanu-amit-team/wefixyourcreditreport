'use strict';

/**
 * @ngdoc directive
 * @name codeBaseAdminApp.directive:formGroup
 * @description
 * # formGroup
 */
angular.module('codeBaseAdminApp')
  .directive('fg', function () {
    return {
      templateUrl: 'views/form-group.html',
      transclude : true,
      restrict: 'E',
      scope : {
      	//form : "=form",
      	text : "=",
      	label : "="
      },
      link: function postLink(scope, element, attrs) {

        scope.form = scope.$parent.pixelForm;
      	// console.log(scope.$parent.test1);
      	//scope.label = attrs.label;
        //element.text('this is the formGroup directive');
      }
    };
  });
