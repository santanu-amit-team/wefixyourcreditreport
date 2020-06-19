'use strict';

/**
 * @ngdoc service
 * @name codeBaseAdminApp.Toast
 * @description
 * # Toast
 * Factory in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp')
	.factory('Toast', function ($mdToast) {
		return {
			showToast: function (text) {
				return $mdToast.show(
					$mdToast.simple()
					.textContent(text)
					.position('bottom right')
					.hideDelay(3000)
					);
			}
		};
	});
