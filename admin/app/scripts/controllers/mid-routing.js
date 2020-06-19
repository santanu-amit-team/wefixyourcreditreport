'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:MidRouting
 * @description
 * # MidRouting
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('CheckRoutingExtensionsDialog', function ($scope, $mdDialog, $location) {
        // $scope.closeDialog = function () {
        //     $mdDialog.hide();
        //     $location.path('/extensions');

        // };
    }).controller('MidRouting', function($scope, Toast, Dialog, $http, $location, $window, $routeParams, $filter, $rootScope, $route, $mdDialog) {
    
    $scope.currentPage = 1;
    $scope.limit = 10;
    $scope.fullList = [];
    $scope.lists = [];
    $scope.listIds = [];
    $scope.dataLoadComplete = false;
    $scope.filterTypes = [{key : 'all', val : 'All'}];
	$scope.filterActions = [{key : 'delete', val : 'Delete', method : 'deleteRouting'}];

    $scope.getMidRouteIds = function(){
        angular.forEach($scope.lists, function (item, index) {
            $scope.listIds.push(item.id);
        });
    }

    $http.post('../' + REST_API_PATH + 'midrouting/all/')
			.success(function (response) {
				$scope.lists = response.data;
                $scope.fullList = response.data;
                $scope.getMidRouteIds();
				$scope.dataLoadComplete = true;
            });
            
    $scope.editRouting = function(_id) {
        $location.path('/routing-manager/' + _id);
    }
    
    $scope.deleteRouting = function(_ids) {

        var url = '';
        // if(angular.isObject(ids)){
        //     url = '../' + REST_API_PATH + 'midrouting/delete-data/';
        //     Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(_ids))});
        // }
        // else{
        //     url = '../' + REST_API_PATH + 'midrouting/delete-data/' + _ids;
        //     Dialog.showDialog(url, '', '');
        // }

        $http.post('../' + REST_API_PATH + 'midrouting/get-asssigned-mid-with-multiple-id/', { ids: _ids})
        .then(function (response) {
            
            if (!response.data.success) {
                var message = _ids.length > 1 ? 'Some routings already assigned to configurations' : 'This routing already assigned to configuration';
                var configIDs = [];
                angular.forEach(response.data.data, function (value, index) {
                    configIDs[index] = value.id;
                   
                });
                 message += ' '+ configIDs.join();
                 Dialog.alertDialog(message, 'Ok');

            } else {

                
                if(angular.isObject(_ids)){
                    url = '../' + REST_API_PATH + 'midrouting/delete-routing/';
                    Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(_ids))});
                }
                else{
                    url = '../' + REST_API_PATH + 'midrouting/delete-routing/' + _ids;
                    Dialog.showDialog(url, '', '');
                }
            }
            
        });
    }





    /** Multiple Checkbox work **/
    $scope.selectedRows = [];
	$scope.toggle = function (item) {
	    var idx = $scope.selectedRows.indexOf(item);
		if (idx > -1) { $scope.selectedRows.splice(idx, 1);}
		else { $scope.selectedRows.push(item); }
	};
    
    $scope.exists = function (item) {
		return $scope.selectedRows.indexOf(item) > -1;
	};

});