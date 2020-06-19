'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:CampaignManager
 * @description
 * # CampaignManager
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('CheckCouponExtensionsDialog', function ($scope, $mdDialog, $location) {
    $scope.closeDialog = function () {
        $mdDialog.hide();
        $location.path('/extensions');

    };
}).controller('CouponManager', function ($scope, $http, $routeParams, $location, Toast, $rootScope, taOptions, $mdDialog, $filter) {

    $scope.coupon = {
    };
    $scope.coupon_codes = {};

    $scope.validation = {
        coupon_code : {status : true, message : '', required: true }
    };
    $scope.coupon_code_separator = '\n';
    $scope.couponLoadComplete = false;
    $scope.adminPath = '../../';
    $scope.couponsTypes = ['flat', 'percentage'];
    $scope.appliedOns = [
        {
            'label': 'Product',
            'values': 'product'
        },
       {
           'label': 'Order',
           'values': 'order_total'
       },
        {
            'label': 'Shipping',
            'values': 'shipping_total'
        },
    ]
    taOptions.toolbar = [
        ['h1', 'h2', 'h3'],
        ['bold', 'italics', 'underline', 'html']
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

    //$scope.checkExtensions();
    if ($routeParams.id) {
        $http.post('../' + REST_API_PATH + 'coupons/get/' + $routeParams.id)
                .then(function (response) {
                    if (response.data.success) {
                        $scope.coupon = response.data.data;
                        $scope.coupon.coupon_end_date = $scope.coupon.coupon_end_date.length ?  new Date($scope.coupon.coupon_end_date) : '';
                        $scope.coupon.coupon_start_date = $scope.coupon.coupon_start_date.length ? new Date($scope.coupon.coupon_start_date) : '';
                    }
                    $scope.couponLoadComplete = true;
                });
    }
    else {
        $scope.couponLoadComplete = true;
    }


    $scope.SubmitForm = function () {
        var url = '../' + REST_API_PATH + 'coupons/add';
        if ($routeParams.id) {
            url = '../' + REST_API_PATH + 'coupons/edit/' + $routeParams.id;
        }
        var data = angular.copy($scope.coupon);

        data.start_date = $filter('date')(data.coupon_start_date, 'yyyy-MM-dd'); 
        data.end_date = $filter('date')(data.coupon_end_date, 'yyyy-MM-dd');

        $http.post(url, data)
                .then(function (response) {
                    if (!response.data.success) {
                        $scope.CouponForm.$submitted = false;
                        Toast.showToast(response.data.error_message);
                    } else {
                        $location.path('/coupons');
                        Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
                    }
                });
    },
            function array_move(arr, old_index, new_index) {
                if (new_index >= arr.length) {
                    var k = new_index - arr.length + 1;
                    while (k--) {
                        arr.push(undefined);
                    }
                }
                arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
                return arr;
            }
    ;


    $scope.validationMethod = function() {
        
        var validation = true;
        angular.forEach($scope.validation, function(value, index) {
            if (!value.status) {
                validation = false;
            }
        });

        if (!$scope.coupon.coupon_code) {

            return false;
        }
        return validation;
    }


});
