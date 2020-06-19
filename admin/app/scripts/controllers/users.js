'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Users
 * @description
 * # Users
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('Users', function ($scope, $mdToast, $http, $location, Toast, Dialog, $route, $filter) {

		$scope.filterActions = [{key : 'delete', val : 'Delete', method : 'deleteUser'}];
		
		$scope.userTypes = [
			{
				name: 'Super Admin',
				key : '3'
			},
			{
				name: 'Standard',
				key : '2'
			},
			{
				name: 'Admin',
				key : '1'
			}
		];
		$scope.userIds = [];
		$scope.dataLoadComplete = false;
		$scope.filterTypes = [{key : 'all', val : 'All'}];

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


		$scope.getUserIds = function(){
			angular.forEach($scope.users, function (item, index) {
				$scope.userIds.push(item.id);
			});
		}

		$scope.loadTypes = function(){
			angular.forEach($scope.userTypes, function (type, index) {
				$scope.filterTypes.push({key: type.key, val : $filter('ucfirst')(type.name)});
			});
		}
		$scope.loadTypes();

		$http.get('../' + REST_API_PATH + 'users/user-list')
			.then(function (response) {
				if(response.data.success === true){
					
					$scope.users = response.data.data;
					$scope.fullList = response.data.data;	
					$scope.getUserIds();
					$scope.dataLoadComplete = true;				
				}
				else if(response.data.success === false){

				}
        });

        $scope.editUser = function (_id) {
            $location.path('/user-manager/' + _id);
        }
        
        $scope.deleteUser = function (id) {

			var url = '';
            if(angular.isObject(id)) {

                url = '../' + REST_API_PATH + 'users/delete';
                Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(id))});
            }
            else {
                url = '../' + REST_API_PATH + 'users/delete/' + id;
                Dialog.showDialog(url, '', '');
            }
        }
	});