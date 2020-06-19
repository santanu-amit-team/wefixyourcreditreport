'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:CrmsCtrl
 * @description
 * # CrmsCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('CrmsCtrl', function ($scope, $templateCache, $compile, $http, $location, Dialog, $filter, $rootScope, crms) {
		$scope.dataLoadComplete = false;
		$scope.listIds = [];
		$scope.crmsTypes = crms.crmsTypeList();
		$scope.filterActions = [{key : 'delete', val : 'Delete', method : 'deleteCrms'}];
		$scope.filterTypes = [{key : 'all', val : 'All'}];
		$scope.selectedType = 'all';

		$scope.getLabel = function(key){
			return crms.getLabel(key);
		}
		$scope.loadTypes = function(){
			angular.forEach($scope.crmsTypes, function (crmName, index) {
				$scope.filterTypes.push({key: crmName.key, val : crmName.label});
			});
		}
		$scope.loadTypes();
		$scope.getListIds = function(){
			angular.forEach($scope.lists, function (item, index) {
				$scope.listIds.push(item.id);
			});
		}
		$http.post('../' + REST_API_PATH + 'crms/all/')
		.success(function (response) {
			$scope.lists = response.data;
			$scope.fullList = response.data;
        	$scope.getListIds();
			$scope.dataLoadComplete = true;
		});
		$scope.editCrms = function (_id) {
			$location.path('/crms-manager/' + _id);
		}
		$scope.deleteCrms = function (id) {
			var url = '';
			if(angular.isObject(id)){
				url = '../' + REST_API_PATH + 'crms/delete';
				Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(id))});
			}
			else{
				url = '../' + REST_API_PATH + 'crms/delete/' + id;
				Dialog.showDialog(url, '', '');
			}
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