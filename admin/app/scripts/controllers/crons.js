'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:CronJobsCtrl
 * @description
 * # CronJobsCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('CronJobsCtrl', function ($scope, $http, $filter, $location, Dialog, Toast, $mdDialog  ) {

		$scope.lists = [];
		$scope.filterTypes = [{key : 'all', val : 'All', value : true, identifier : 'all', type : 'boolean'}];
		$scope.JobTypes = [
			 [ 'active' , true], [ 'inactive', false ]
		];
		$scope.filterActions = [
			{key : 'delete', val : 'Delete', method : 'deleteMultipleJobs'},
			{ key : 'changeStatusActive', val : 'Activate', method : 'changeMultipleStatusActivate' },
        	{ key : 'changeStatusDeactive', val : 'Deactivate', method : 'changeMultipleStatusDeactivate' }
		];
		$scope.dataLoadComplete = false;
		$scope.listIds = [];
		$scope.loadStatus = [];

		$scope.getJobIds = function(){
			angular.forEach($scope.lists, function (item, index) {
				$scope.listIds.push(item.id);
			});
		}
	
		$scope.loadTypes = function(){
			angular.forEach($scope.JobTypes, function (type, index) {
				$scope.filterTypes.push({ key: type[0], val : $filter('ucfirst')(type[0]), value : type[1], identifier: 'status', type : 'boolean' });
			});
		}
		$scope.loadTypes();

		$scope.loadAll = function( changeStatusIndex = '' ) {
			$http.post('../' + REST_API_PATH + 'cron/all/')
			.success(function (response) {

				angular.copy(response.data, $scope.lists);
				$scope.fullList = response.data;

				angular.forEach($scope.lists, function(element, index){

					$scope.lists[index]['created_at'] = new Date($scope.lists[index]['created_at']);
				});

				angular.forEach($scope.fullList, function(element, index){

					$scope.fullList[index]['created_at'] = new Date($scope.fullList[index]['created_at']);
				});
				$scope.loadStatus = new Array($scope.lists.length).fill(false);
				$scope.listIds = [];
				$scope.getJobIds();

				$scope.dataLoadComplete = true;
				$scope.$broadcast('statusChanged',{"val":$scope.lists});
				if(!angular.isUndefined(changeStatusIndex) && Number.isInteger(changeStatusIndex))
                        $scope.loadStatus[changeStatusIndex]  = false;
			});
		}
		
		$scope.loadAll();

		$scope.editJob = function (_id) {
			$location.path('/cron-manager/' + _id);
		}

		$scope.deleteMultipleJobs = function(ids) {
			var url = '';
			if(angular.isObject(ids)){
				url = '../' + REST_API_PATH + 'cron/delete/';
				Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(ids))});
			}
			else{
				url = '../' + REST_API_PATH + 'cron/delete/' + ids;
				Dialog.showDialog(url, '', '');
			}
		}

		$scope.moveUp = function(_id, priority) {

			var confirm = $mdDialog.confirm()
						.title('Warning')
						.textContent('Are you sure to update scheduler priority.')
						.ok('Yes')
						.cancel('No');
			
			$mdDialog.show(confirm).then(function () {

				$http.post('../' + REST_API_PATH + 'cron/priority-up/' + _id + '/' + priority)
				.success(function (response) {
					if(response.success) {
						Toast.showToast(response.message);
						$scope.loadAll();
					}
					else {
						Toast.showToast(response.error_message);
					}
				});
				
			});
			
		}

		$scope.moveDown = function(_id, priority) {
			
			var confirm = $mdDialog.confirm()
						.title('Warning')
						.textContent('Are you sure to update scheduler priority.')
						.ok('Yes')
						.cancel('No');

			$mdDialog.show(confirm).then(function () {

				$http.post('../' + REST_API_PATH + 'cron/priority-down/' + _id + '/' + priority)
				.success(function (response) {
					if(response.success) {
						Toast.showToast(response.message);
						$scope.loadAll();
					}
					else {
						Toast.showToast(response.error_message);
					}
				});
				
			});
		}

		$scope.changeMultipleStatusActivate = function(ids) {
    
			var indexes = $scope.lists.reduce((a, c, i) => {
	
				if (  ids.indexOf(c.id) >= 0 ) a.push(i); 
				return a;
	
			  }, []);
	
			$scope.changeStatus(ids, 'true', indexes);
		}

		$scope.changeMultipleStatusDeactivate = function(ids) {

			var indexes = $scope.lists.reduce((a, c, i) => {
	
			if (  ids.indexOf(c.id) >= 0 ) a.push(i); 
			return a;
	
			}, []);
	
		   $scope.changeStatus(ids, false, indexes);
		}

		$scope.changeStatus = function(id, status, rowIndex){

			if (Array.isArray(rowIndex) ){
				rowIndex.map( function(index) {
					$scope.loadStatus[index] = true;
				});
			}  
			else {
	
				$scope.loadStatus[rowIndex] = true;
			}
	
			if (angular.isObject(id)) {
	
				var data = { ids : JSON.parse(JSON.stringify(id)) , status: status };
	
				$http.post('../' + REST_API_PATH + 'cron/status/', data).then(function(response){
					if(response.data.success) {
						$scope.loadAll();
						Toast.showToast(response.data.success_message);
					}
					else {
						Toast.showToast(response.data.error_message);
						$scope.loadStatus[rowIndex]  = false;
					}
					
				});
				return;
			}
			$http.get('../' + REST_API_PATH + 'cron/status/' + id + '/' + status).then(function(response){
				if(response.data.success) {
					$scope.loadAll();
					Toast.showToast(response.data.success_message);
				}
				else {
					Toast.showToast(response.data.error_message);
					$scope.loadStatus[rowIndex]  = false;
				}
				
			});
		}

		$scope.showLog = function(id, handler) {

			handler = handler.split("\\").join('|');
			$http.post('../' + REST_API_PATH + 'cron/get-log/' + id + '/' + handler)
            .then(function(response) {

                if (response.data.success) {
                    var content = response.data.data;
                    Dialog.dynamicTemplate('../app/views/modals/viewCronLog.html', content, '','','','ShowLog');
        
                } else {

                    Toast.showToast(response.data.error_message);
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
	
	})
	.controller('ShowLog', function ($rootScope, $scope, $mdDialog, content) {
		$scope.data = content;
		$scope.showMore = new Array($scope.data.length).fill(false);
		$scope.cancel = function () {
			$mdDialog.cancel();
		};

		$scope.toggleShowMore = function(index) {
			$scope.showMore[index] = $scope.showMore[index] ? false : true;
		}
	});
