'use strict';

/**
 * @ngdoc directive
 * @name codeBaseAdminApp.directive:flatButtonDirective
 * @description
 * # flatButtonDirective
 */
angular.module('codeBaseAdminApp')
	.directive('flatButtonDirective', function () {
		return {
			template: '<md-button class="md-fab" aria-label="Add" ng-if="type == \'add\'" ng-href="{{url}}"><md-tooltip md-direction="right" md-visible="tooltipVisible">Add New {{title}}</md-tooltip><i class="material-icons">&#xE145;</i></md-button><md-button class="md-fab" aria-label="back" ng-if="type == \'back\'" ng-href="{{url}}"><md-tooltip md-direction="left" md-visible="tooltipVisible">Back</md-tooltip><i class="material-icons">&#xE5C4;</i></md-button>',
			restrict: 'E',
			link: function postLink(scope, element, attrs) {
				scope.url = attrs.url;
				scope.title = attrs.title;
				scope.type = attrs.type;
			}
		};
	});
