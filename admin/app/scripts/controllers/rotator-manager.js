'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:RotatorManager
 * @description
 * # RotatorManager
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('RotatorManager', function ($scope, $http, $routeParams, $location, Toast) {

		$http.post('../' + REST_API_PATH + 'configurations/all/')
			.success(function (response) {
				$scope.configurations = response.data;
			});

		$scope.rotator = {};
		if ($routeParams.id) {
			$http.post('../' + REST_API_PATH + 'rotators/get/' + $routeParams.id)
				.then(function (response) {
					if (response.data.success) {
						$scope.rotator = response.data.data;

						if (!$scope.rotator.configuration_mapping.length)
						{
							$scope.rotator.configuration_mapping = [['','']];
						}
					}
				});
		}

		$scope.rotator.configuration_mapping = [['','']];
		$scope.addRowConfig = function () {
			$scope.rotator.configuration_mapping.push(['','']);
		}

		$scope.deleteRowConfig = function (index) {
			$scope.rotator.configuration_mapping.splice(index, 1);
		}

		$scope.SubmitForm = function () {
			$scope.checkPercentage().then(function (data) {
				if (data.valid)
				{
					$scope.rotator.configuration_mapping = ($scope.rotator.configuration_mapping.length) ? JSON.stringify($scope.rotator.configuration_mapping) : '';
			
					var url = '../' + REST_API_PATH + 'rotators/add';
					if ($routeParams.id) {
						url = '../' + REST_API_PATH + 'rotators/edit/' + $routeParams.id;
					}
					$http.post(url, $scope.rotator)
						.then(function (response) {
							if (!response.data.success) {
								$scope.RotatorForm.$submitted = false;
								Toast.showToast(response.data.error_message);
							} else {
								$location.path('/rotators');
								Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
							}
						});
				}
				else
				{
					$scope.RotatorForm.$submitted = false;
					Toast.showToast("Sorry! Total percentage should be equal to or less than 100");
				}
			});
		}

		$scope.checkPercentage = function ()
		{
			return new Promise(function (resolve, reject) {
				$http.post('../' + REST_API_PATH + 'rotators/get-total-percentage/' + ($routeParams.id ? $routeParams.id : ''))
					.success(function (response) {
						var total_percentage = parseInt($scope.rotator.percentage.length ? $scope.rotator.percentage : 0) + parseInt(response.data);
						resolve(
							{
								valid: !(total_percentage > 100)
							}
						);
					});
			});

		};
	});
