'use strict';

/**
 * @ngdoc directive
 * @name codeBaseAdminApp.directive:noDataText
 * @description
 * # noDataText
 */
angular.module('codeBaseAdminApp')
  .directive('noDataText', function () {
		return {
		    template : '<h5 class="md-subhead">Let&#8217;s start, click on the Add Button above...</h5>',
			restrict: 'E',
		};
  });
