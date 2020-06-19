'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Campaigns
 * @description
 * # Campaigns
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('CheckCouponExtensionsDialog', function ($scope, $mdDialog, $location) {
    $scope.closeDialog = function () {
        $mdDialog.hide();
        $location.path('/extensions');

    };
}).controller('CouponUser', function ($scope, $templateCache, $compile, $http, $routeParams, $location, $mdDialog, Dialog, $rootScope, Toast) {
    $scope.currentPage = 1;
    $scope.limit = 50;
    $scope.orderBy = "DESC";
    $scope.orderByField = "id";
    $scope.sortIcon = 'keyboard_arrow_down';
    $scope.lists = [];
    $scope.is_pmt_extention_enabled = false;
    $scope.is_import_export_enabled = false;
    $scope.isUploadProgress = false;
    $scope.couponuser = {};
    $scope.status = [
        'active', 'inactive'
    ];
    $scope.usedStatus = [
        {
            'label': 'Active',
            'values': 'active'
        },
        {
            'label': 'Inactive',
            'values': 'inactive'
        }
    ];
    $scope.checkExtensions = function () {
        $http.get('../' + REST_API_PATH + 'coupons/check-extensions').then(function (response) {
            if (!response.data.extensionCouponsActive) {
                var message = 'Download <strong>Coupons</strong> extension' +
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
                    controller: 'CheckCouponExtensionsDialog'
                }));
            }

        });
    };

    $scope.getUser = function () {
        //$scope.checkExtensions();
        var params = {
            limit: $scope.limit,
            offset: ($scope.currentPage - 1) * $scope.limit,
            orderBy: $scope.orderBy,
            orderByField: $scope.orderByField
        };
        $http.post('../' + REST_API_PATH + 'coupons/get-coupon-user/' + $routeParams.id, params)
                .success(function (response) {
                    console.log(response);
                    var data = [];
                    var i = 0;
                    angular.forEach(response.data, function (value, index) {
                        data[i] = value;
                        i++;

                    })

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

    $scope.toggleOrderBy = function (val) {
        if ($scope.orderBy == 'DESC') {
            $scope.orderBy = 'ASC';
            $scope.sortIcon = 'keyboard_arrow_up';
        } else {
            $scope.orderBy = 'DESC';
            $scope.sortIcon = 'keyboard_arrow_down';
        }
        $scope.orderByField = val;
        $scope.getUser();
    }

    $scope.changeLimit = function () {
        $scope.currentPage = 1;
        $scope.orderBy = "DESC";
        $scope.orderByField = "id";
        $scope.sortIcon = 'keyboard_arrow_down';
        $scope.getUser();
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

    $scope.updateUserCoupon = function (userCouponID, couponid, status, couponLimit) {
        console.log(status + ' ' + couponLimit);
        var params = {
            id: couponid,
            userCouponID: userCouponID,
            status: status,
            couponLimit: String(couponLimit)
        };
        $http.post('../' + REST_API_PATH + 'coupons/update-user-coupon-details/', params)
                .success(function (response) {
                     if (!response.success) {
                        Toast.showToast("Something went wrong");
                    } else {
                        Toast.showToast('Saved successfully');
                    }
                   
                });

    }


    var formdata = new FormData();





});
