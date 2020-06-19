'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:ChangelogManager
 * @description
 * # ChangelogManager
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('ChangelogManager', function($scope, $mdToast, $http, 
    $location, Toast, Dialog, $mdDialog, $timeout) {
	$scope.content = '';
	$scope.loaded = false;
	$http.post('../' + REST_API_PATH + 'extensions/changelog/framework')
	    .then(function(response) {
	        $scope.loaded = true;
	        $scope.content = response.data.data;
	    },function(error){  
	        console.log(error); 
	    });
});