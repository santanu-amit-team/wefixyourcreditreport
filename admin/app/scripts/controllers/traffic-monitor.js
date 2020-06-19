'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Campaigns
 * @description
 * # Campaigns
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('CheckTrafficMonitorExtensionsDialog', function ($scope, $mdDialog, $location) {
    $scope.closeDialog = function () {
        $mdDialog.hide();
        $location.path('/extensions');

    };
}).controller('TrafficMonitor', function ($scope, $templateCache, $compile, $http, $location, $mdDialog, Dialog, $rootScope, Toast) {
    $scope.trafficMonitor = {};
    $scope.Math = window.Math;
    var curDate = new Date();
    $rootScope.startDate = curDate;
    $rootScope.endDate = curDate;
    $scope.isOpen = false;
    $scope.checkDateRange = function (startDate, endDate) {
        $scope.errMessage = '';
        var curDate = new Date();
        var givenStartDate =  new Date(startDate);
        givenStartDate = givenStartDate.setHours(0,0,0,0);
        var givenEndDate =  new Date(endDate);
        givenEndDate = givenEndDate.setHours(0,0,0,0);
        if (givenStartDate > givenEndDate) {
            $scope.errMessage = 'End Date should be greater than start date';
            Toast.showToast($scope.errMessage);
            this.startDate = new Date();
            this.endDate = new Date();
            return false;
        }
        $rootScope.startDate = new Date(startDate);
        $rootScope.endDate = new Date(endDate);
        $scope.getTraffic();

    };
    $scope.currentPage = 1;
    $scope.limit = 50;
    $scope.orderBy = "DESC";
    $scope.orderByField = "id";
    $scope.sortIcon = 'keyboard_arrow_down';
    $rootScope.lists = [];
    $scope.is_pmt_extention_enabled = false;
    $scope.is_import_export_enabled = false;
    $scope.isUploadProgress = false;
    $scope.checkExtensions = function () {
        $http.get('../' + REST_API_PATH + 'trafficmonitor/check-extensions').then(function (response) {
            if (!response.data.extensionTrafficMonitorActive) {
                var message = 'Download <strong>Traffic Monitor</strong> extension' +
                        ' and activate it.';
                $mdDialog.show($mdDialog.alert({
                    template: ' <md-dialog>' +
                            '	<md-dialog-content>' +
                            '	' + '<p style="padding: 15px; padding-top: 30px; text-align: center;">' + message + '</p>' +
                            '  	</md-dialog-content>' +
                            '  	<md-dialog-actions>' +
                            '   	<md-button ng-click="closeDialog()" class="md-primary">' +
                            '      		Ok' +
                            '   	</md-button>' +
                            '  	</md-dialog-actions>' +
                            '</md-dialog>',
                    controller: 'CheckTrafficMonitorExtensionsDialog'
                }));
            }

        });
    };
    $scope.getTraffic = function () {
       // $scope.checkExtensions();
        var params = {
            limit: $scope.limit,
            offset: ($scope.currentPage - 1) * $scope.limit,
            orderBy: $scope.orderBy,
            orderByField: $scope.orderByField,
            startDate: $rootScope.startDate,
            endDate: $rootScope.endDate
        };
        
        $http.post('../' + REST_API_PATH + 'trafficmonitor/all/', params)
                .success(function (response) {
                    var data = [];
                    var i = 0;
                    angular.forEach(response.data, function (value, index) {
                        data[i] = value;
                        i++;
                    });

                    $rootScope.lists = data;
                    $rootScope.totalItems = response.totalData;
                    if ($rootScope.totalItems > 0) {
                        $scope.paginationShow = true;
                    } else {
                        $scope.paginationShow = true;
                    }
                    $scope.pages = Math.ceil($rootScope.totalItems / $scope.limit);
                    angular.copy(response.data, $rootScope.lists);
                });
    }

    $scope.getTraffic();

    $scope.toggleOrderBy = function (val) {
        if ($scope.orderBy == 'DESC') {
            $scope.orderBy = 'ASC';
            $scope.sortIcon = 'keyboard_arrow_up';
        } else {
            $scope.orderBy = 'DESC';
            $scope.sortIcon = 'keyboard_arrow_down';
        }
        $scope.orderByField = val;
        $scope.getTraffic();
    }

    $scope.changeLimit = function () {
        $scope.currentPage = 1;
        $scope.orderBy = "DESC";
        $scope.orderByField = "id";
        $scope.sortIcon = 'keyboard_arrow_down';
        $scope.getTraffic();
    }

    $scope.clear = function () {
        $scope.searchText = '';
    };

    $scope.getpagesArray = function (num) {
        if (!isNaN(num)) {
            return new Array(num);
        } else {
            return new Array(1);
        }
    }

	$scope.filterActions = [{key : 'delete', val : 'Delete', method : ''}];

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