'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:UserManagement
 * @description
 * # UserManagement
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('UserManagement', function ($scope, $mdToast, $http, $location, Toast, Dialog, $route, $filter) {
		$http.get('../' + REST_API_PATH + 'user-management/user-list')
			.then(function (response) {
				if(response.data.success === true){
				$scope.users = response.data.data;					
				}
				else if(response.data.success === false){

				}
			});
	});