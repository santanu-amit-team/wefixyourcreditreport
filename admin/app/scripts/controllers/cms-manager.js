'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:CmsManagerCtrl
 * @description
 * # CmsManagerCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('CmsManagerCtrl', function ($scope, $http, $routeParams, $location, textAngularManager, Toast) {
		$scope.content = {};
		$scope.content.status = 'draft';
		$scope.statusArray = [{key : 'draft', val : 'Draft'}, {key : 'published', val : 'Published'}];
		if ($routeParams.id) {
			$http.post('../' + REST_API_PATH + 'cms/get/' + $routeParams.id)
				.then(function (response) {
					if (response.data.success) {
						$scope.content = response.data.data;
					}
				});
		}

		/**
		 * [saveContent description]
		 * @return {[type]} [description]
		 */
		$scope.saveContent = function () {
			var url = '../' + REST_API_PATH + 'cms/add';
			if ($routeParams.id) {
				url = '../' + REST_API_PATH + 'cms/edit/' + $routeParams.id;
			}
			var data = angular.copy($scope.content);
			data.last_edited = new Date();

			$http.post(url, data)
				.then(function (response) {
					if (!response.data.success) {
						Toast.showToast(response.data.error_message);
					} else {
						$location.path('/cms/');
						Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
					}
				});
		};
	});
