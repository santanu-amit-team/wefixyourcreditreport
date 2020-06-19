'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Authentication
 * @description
 * # Authentication
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('Authentication', function ($scope, $templateCache, $compile, $http, $location, Toast, $rootScope) {

		$http.post('../' + REST_API_PATH + 'auth/check-login')
			.then(function (response) {
				if (!response.data.success) {

				} else {
					$rootScope.advanced_mode = true;
					$location.path($rootScope.redirectPage);
				}
			});

		$scope.SubmitForm = function () {
			$http.post('../' + REST_API_PATH + 'auth/validate-login', $scope.auth)
				.then(function (response) {
					if (!response.data.success) {
						Toast.showToast(response.data.error_message);
					} else {
						$rootScope.advanced_mode = true;
						$location.path($rootScope.redirectPage);
					}
				});
		}

	});
