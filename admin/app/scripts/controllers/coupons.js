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
}).controller('Coupons', function ($scope, $templateCache, $compile, $http, $location, $mdDialog, Dialog, $rootScope, Toast,  $filter) {
    $scope.currentPage = 1;
    $scope.limit = 50;
    $scope.orderBy = "DESC";
    $scope.orderByField = "id";
    $scope.sortIcon = 'keyboard_arrow_down';
    $scope.lists = [];
    $scope.listIds = [];
    $scope.is_pmt_extention_enabled = false;
    $scope.is_import_export_enabled = false;
    $scope.isUploadProgress = false;
    $scope.filterTypes = [{key : 'all', val : 'All'}];
    $scope.couponsTypes = ['flat', 'percentage'];

	$scope.filterActions = [{key : 'delete', val : 'Delete', method : 'deleteMultipleCoupon'}];
    


    $scope.getCouponIds = function(){
        angular.forEach($scope.lists, function (item, index) {
            $scope.listIds.push(item.id);
        });
    }

    $scope.loadTypes = function(){
        angular.forEach($scope.couponsTypes, function (type, index) {
            $scope.filterTypes.push({key: type, val : $filter('ucfirst')(type)});
        });
    }
    $scope.loadTypes();


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

    $scope.getCoupons = function () {
       // $scope.checkExtensions();
        var params = {
            limit: $scope.limit,
            offset: ($scope.currentPage - 1) * $scope.limit,
            orderBy: $scope.orderBy,
            orderByField: $scope.orderByField
        };
        $http.post('../' + REST_API_PATH + 'coupons/all/', params)
                .success(function (response) {
                    var data = [];
                    var i = 0; 
                    angular.forEach(response.data, function (value, index) {
                        var dicountArr = value.applied_on.split("_");
                        value.applied_on = '';
                        for (var j = 0; j < dicountArr.length; j++) {
                            value.applied_on += dicountArr[j].toUpperCase() + ' ';
                        }
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
                    $scope.fullList = response.data;
                    $scope.getCouponIds();
                    $scope.dataLoadComplete = true;
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
        $scope.getCoupons();
    }

    $scope.changeLimit = function () {
        $scope.currentPage = 1;
        $scope.orderBy = "DESC";
        $scope.orderByField = "id";
        $scope.sortIcon = 'keyboard_arrow_down';
        $scope.getCoupons();
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

    $scope.editCoupon = function (_id) {
        $location.path('/coupon-manager/' + _id);
    }

    $scope.deleteMultipleCoupon = function (ids) {

        $http.post('../' + REST_API_PATH + 'coupons/get-asssigned-coupon-with-multiple-id/', { ids: ids}).then(function (response) {
            
            if (!response.data.success) {
                var message = ids.length > 1 ? 'Some coupons already assigned to campaign' : 'This coupon already assigned to campaign';
                var campaignIDs = [];
                angular.forEach(response.data.data, function (value, index) {
                    campaignIDs[index] = value.id;
                   
                });
                 message += ' '+ campaignIDs.join();
                 Dialog.alertDialog(message, 'Ok');

            } else {

                
                var url = '';
                if(angular.isObject(ids)){
                    url = '../' + REST_API_PATH + 'coupons/delete/';
                    Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(ids))});
                }
                else{
                    url = '../' + REST_API_PATH + 'coupons/delete/' + ids;
                    Dialog.showDialog(url, '', '');
                }
            }
            
        });
    }


    $scope.couponUser = function (_id) {
        $location.path('/coupon-user/' + _id);
    }


    var formdata = new FormData();

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



}).controller('couponDeleteDialog', function ($scope, $mdDialog, $location) {
    $scope.closeDialog = function () {
        $mdDialog.hide();

    };
});
