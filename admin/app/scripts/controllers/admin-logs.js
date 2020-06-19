'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:AdminLogs
 * @description
 * # admin-logs
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('CheckAdminLogsExtensionsDialog', function ($scope, $mdDialog, $location) {
    $scope.closeDialog = function () {
        $mdDialog.hide();
        $location.path('/extensions');

    };
}).controller('AdminLogs', function ($scope, $http, $mdDialog) {
    $scope.currentPage = 1;
    $scope.limit = 50;
    $scope.orderBy = "DESC";
    $scope.orderByField = "id";
    $scope.lists = [];

    $scope.checkExtensions = function () {
        $http.get('../' + REST_API_PATH + 'adminLogs/check-extensions').then(function (response) {
            if (!response.data.extensionAdminLogsActive) {
                var message = 'Download <strong>Admin Logs</strong> extension' +
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
                    controller: 'CheckAdminLogsExtensionsDialog'
                }));
            }

        });
    };

    $scope.getAdminLogs = function () {
        //$scope.checkExtensions();
        var params = {
            limit: $scope.limit,
            offset: ($scope.currentPage - 1) * $scope.limit,
            orderBy: $scope.orderBy,
            orderByField: $scope.orderByField
        };
        $http.post('../' + REST_API_PATH + 'adminLogs/all/', params)
                .success(function (response) {
                    var data = [];
                    var i = 0; 
                    

                    $scope.lists = data;
                    $scope.totalItems = response.totalData;
                    if ($scope.totalItems > 0) {
                        $scope.paginationShow = true;
                    } else {
                        $scope.paginationShow = false;
                    }
                    $scope.pages = Math.ceil($scope.totalItems / $scope.limit);
                    angular.copy(response.data, $scope.lists);
                });
    }

    $scope.changeLimit = function () {
        $scope.currentPage = 1;
        $scope.orderBy = "DESC";
        $scope.orderByField = "id";
        $scope.sortIcon = 'keyboard_arrow_down';
        $scope.getAdminLogs();
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
});
