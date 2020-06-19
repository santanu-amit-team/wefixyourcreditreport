'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Campaigns
 * @description
 * # Campaigns
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
    .controller('SplitTest', function ($scope, $templateCache, $compile, $http, $location, $mdDialog, Dialog, $rootScope,
        Toast, $filter, crms) {

        $scope.dataLoadComplete = true;
        $scope.currentPage = 1;
        $scope.limit = 50;
        $scope.orderBy = "DESC";
        $scope.orderByField = "id";
        $scope.sortIcon = 'keyboard_arrow_down';
        $scope.lists = [];
        $scope.filterActions = [{
                key: 'delete',
                val: 'Delete',
                method: 'deleteSplitTest'
            }];
        $scope.filterTypes = [{
                key: 'all',
                val: 'All'
            }];
        $scope.selectedType = 'all';
        $scope.listIds = [];
        $scope.getSplitTest = function () {
            var params = {
                limit: $scope.limit,
                offset: ($scope.currentPage - 1) * $scope.limit,
                orderBy: $scope.orderBy,
                orderByField: $scope.orderByField
            };
            $http.post('../' + REST_API_PATH + 'split-test/all/', params)
                .success(function (response) {
                    var data = [];
                    var i = 0;
                    angular.forEach(response.data, function (value, index) {
                        if (($rootScope.advanced_mode == false && value.campaign_type != 3) || $rootScope.advanced_mode)
                        {
                            data[i] = value;
                            i++;
                        }
                    });
                    $scope.lists = data;
                    console.log($scope.lists);

                    $scope.totalItems = response.totalData;
                    if ($scope.totalItems > 0) {
                        $scope.paginationShow = true;
                    } else {
                        $scope.paginationShow = false;
                    }
                    $scope.pages = Math.ceil($scope.totalItems / $scope.limit);
                    angular.copy(response.data, $scope.lists);
                    $scope.fullList = response.data;
                    $scope.getListIds();
                });
        };

        $scope.getListIds = function () {
            angular.forEach($scope.lists, function (item, index) {
                $scope.listIds.push(item.id);
            });
        };
        $scope.toggleOrderBy = function (val) {
            if ($scope.orderBy == 'DESC') {
                $scope.orderBy = 'ASC';
                $scope.sortIcon = 'keyboard_arrow_up';
            } else {
                $scope.orderBy = 'DESC';
                $scope.sortIcon = 'keyboard_arrow_down';
            }
            $scope.orderByField = val;
            $scope.getSplitTest();
        }

        $scope.changeLimit = function () {
            $scope.currentPage = 1;
            $scope.orderBy = "DESC";
            $scope.orderByField = "id";
            $scope.sortIcon = 'keyboard_arrow_down';
            $scope.getSplitTest();
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

        $scope.editSplitTest = function (_id) {
            $location.path('/split-test-manager/' + _id);
        }

        $scope.deleteSplitTest = function (id) {
            var url = '';
            if (angular.isObject(id)) {
                url = '../' + REST_API_PATH + 'split-test/delete';
                Dialog.showDialog(url, '', {
                    ids: JSON.parse(JSON.stringify(id))
                });
            } else {
                url = '../' + REST_API_PATH + 'split-test/delete/' + id;
                Dialog.showDialog(url, '', '');
            }
        }


        /** Multiple Checkbox work **/
        $scope.selectedRows = [];
        $scope.toggle = function (item) {
            var idx = $scope.selectedRows.indexOf(item);
            if (idx > -1) {
                $scope.selectedRows.splice(idx, 1);
            } else {
                $scope.selectedRows.push(item);
            }
        };
        $scope.exists = function (item) {
            return $scope.selectedRows.indexOf(item) > -1;
        };

        /*Quick View*/
        $scope.quickViewModal = function (campaign) {
            var data = {
                campaign_id: campaign.campaign_id,
                campaign_label: campaign.campaign_label,
                crm: campaign.crm_label,
                shipping_id: campaign.shipping_id,
                campaign_type: $scope.campaignTypes[campaign.campaign_type],
                billing_type: $scope.billingTypes[campaign.billing_type]
            }
            Dialog.dynamicTemplate('../app/views/modals/campaignQuickView.html', data);
        }

        $scope.showQuickView = function (content) {
            var data = {
                experiment_name: content.experiment_name,
                last_modified_formated: content.last_modified_formated,
                quick_view: content.view_data.quick_view
            }
            Dialog.dynamicTemplate('../app/views/modals/splitTestQuickView.html', data);
        };

    });
