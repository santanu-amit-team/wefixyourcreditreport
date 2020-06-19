'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Campaigns
 * @description
 * # Campaigns
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('AutoResponder', function ($scope, $templateCache, $compile, $http, $location, $mdDialog, Dialog, $rootScope, Toast, $filter) {

    $scope.currentPage = 1;
    $scope.limit = 50;
    $scope.orderBy = "DESC";
    $scope.orderByField = "id";
    $scope.sortIcon = 'keyboard_arrow_down';
    $scope.lists = [];
    $scope.is_pmt_extention_enabled = false;
    $scope.is_import_export_enabled = false;
    $scope.isUploadProgress = false;

    $scope.filterTypes = [{key : 'all', val : 'All'}];
    $scope.filterActions = [
        { key : 'delete', val : 'Delete', method : 'deleteMultipleAutoresponder' },
        { key : 'changeStatusActive', val : 'Activate', method : 'changeMultipleStatusActivate' },
        { key : 'changeStatusDeactive', val : 'Deactivate', method : 'changeMultipleStatusDeactivate' }
    ];
    $scope.listIds = [];
    $scope.dataLoadComplete = false;
    $scope.trigger_types = [ 'Order Confirmation', 'Custom' ];
    $scope.loadStatus = [];

    $scope.getAutorespondersIds = function(){
        angular.forEach($scope.lists, function (item, index) {
            $scope.listIds.push(item.id);
        });
    }


    $scope.loadTypes = function(){
        angular.forEach($scope.trigger_types, function (type, index) {
            $scope.filterTypes.push({key: type, val : $filter('ucfirst')(type)});
        });
    }
    $scope.loadTypes();

    $scope.getAllTemplate = function ( changeStatusIndex = '') {
        var params = {
            limit: $scope.limit,
            offset: ($scope.currentPage - 1) * $scope.limit,
            orderBy: $scope.orderBy,
            orderByField: $scope.orderByField
        };
        $http.post('../' + REST_API_PATH + 'autoresponder/all/', params)
                .success(function (response) {
                    var data = [];
                    var i = 0;
                    angular.forEach(response.data, function (value, index) {
                        data[i] = value;
                        i++;

                    });

                    $scope.lists = data;
                    $scope.loadStatus = new Array($scope.lists.length).fill(false);
                    $scope.totalItems = response.totalData;
                    if ($scope.totalItems > 0) {
                        $scope.paginationShow = true;
                    } else {
                        $scope.paginationShow = true;
                    }
                    $scope.pages = Math.ceil($scope.totalItems / $scope.limit);
                    angular.copy(response.data, $scope.lists);
                    $scope.fullList = response.data;
                    $scope.getAutorespondersIds();
                    $scope.dataLoadComplete = true;

                    if(!angular.isUndefined(changeStatusIndex) && Number.isInteger(changeStatusIndex))
                        $scope.loadStatus[changeStatusIndex]  = false;
                });
    }, 

    $scope.toggleOrderBy = function (val) {
        if ($scope.orderBy == 'DESC') {
            $scope.orderBy = 'ASC';
            $scope.sortIcon = 'keyboard_arrow_up';
        } else {
            $scope.orderBy = 'DESC';
            $scope.sortIcon = 'keyboard_arrow_down';
        }
        $scope.orderByField = val;
        $scope.getAllTemplate();
    },
            
    $scope.changeLimit = function () {
        $scope.currentPage = 1;
        $scope.orderBy = "DESC";
        $scope.orderByField = "id";
        $scope.sortIcon = 'keyboard_arrow_down';
        $scope.getAllTemplate();
    },
    $scope.clear = function () {
        $scope.searchText = '';
    },
    $scope.getpagesArray = function (num) {
        if (!isNaN(num)) {
            return new Array(num);
        } else {
            return new Array(1);
        }
    },
    $scope.editAtResponder = function (_id) {
        $location.path('/autoresponder-manager/' + _id);
    },
    $scope.deleteAtResponder = function (id) {
        Dialog.showDialog('../' + REST_API_PATH + 'autoresponder/delete/' + id);
    }

    $scope.deleteMultipleAutoresponder = function(ids){
        
        var url = '';
        if(angular.isObject(ids)){
            url = '../' + REST_API_PATH + 'autoresponder/delete/';
            Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(ids))});
        }
        else{
            url = '../' + REST_API_PATH + 'autoresponder/delete/' + ids;
            Dialog.showDialog(url, '', '');
        }
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

            $http.post('../' + REST_API_PATH + 'autoresponder/status/', data).then(function(response){
                if(response.data.success) {
                    $scope.getAllTemplate();
                    Toast.showToast(response.data.success_message);
                }
                else {
                    Toast.showToast(response.data.error_message);
                    $scope.loadStatus[rowIndex]  = false;
                }
                
            });
            return;
        }
        $http.get('../' + REST_API_PATH + 'autoresponder/status/' + id + '/' + status).then(function(response){
            if(response.data.success) {
                $scope.getAllTemplate();
                Toast.showToast(response.data.success_message);
            }
            else {
                Toast.showToast(response.data.error_message);
                $scope.loadStatus[rowIndex]  = false;
            }
            
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