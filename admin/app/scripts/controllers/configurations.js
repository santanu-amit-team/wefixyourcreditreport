'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('Configurations', function ($scope, $templateCache, $compile, $http, $location, Dialog, $filter,
		crms) {


		$scope.dataLoadComplete = false;
		$scope.listIds = [];
		// $scope.crmsTypes = ['limelight', 'konnektive', 'responsecrm' , 'velox', 'emanage', 'infusionsoft', 'sixcrm','nmi','m1billing'];
		$scope.crmsTypes = crms.crmsTypeList();
		$scope.filterActions = [{key : 'delete', val : 'Delete', method : 'deleteOffer'}];
		$scope.filterTypes = [{key : 'all', val : 'All'}];
		$scope.selectedType = 'all';

		/* Configure Dropdown */
		var originatorEv;


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

		this.openMenu = function ($mdOpenMenu, ev) {
			originatorEv = ev;
			$mdOpenMenu(ev);
		};

		$scope.searchFieldName = 'configuration_label';
		$scope.searchFieldMetas = [
			{fieldName: 'configuration_label', fieldLabel: 'Label'},
			{fieldName: 'campaign_name', fieldLabel: 'Campaign'},
			{fieldName: 'crm_name', fieldLabel: 'CRM'}
		]; 

		$scope.clear = function () {
            $scope.searchText = '';
        };

        $scope.changeSearchField = function(){
        	for(var ii in $scope.lists){
				$scope.lists[ii]['search'] = $scope.lists[ii][$scope.searchFieldName];
			}
        }

		$http.post('../' + REST_API_PATH + 'configurations/all/')
			.success(function (response) {
				$scope.lists = response.data;
				$scope.fullList = response.data;
				$scope.getListIds();
				$scope.changeSearchField();
				$scope.dataLoadComplete = true;
			});
		$scope.editOffer = function (_id) {
			$location.path('/configuration-manager/' + _id);
		}

		$scope.deleteOffer = function (id) {
			var url = '';
			if(angular.isObject(id)){
				url = '../' + REST_API_PATH + 'configurations/delete/';
				Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(id))});
			}
			else{
				url = '../' + REST_API_PATH + 'configurations/delete/' + id;
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
