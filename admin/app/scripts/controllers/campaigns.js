'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Campaigns
 * @description
 * # Campaigns
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
        .controller('Campaigns', function ($scope, $templateCache, $compile, $http, $location, $mdDialog, Dialog, $rootScope, 
            Toast, $filter, crms) {
            $scope.dataLoadComplete = false;
            $scope.currentPage = 1;
            $scope.limit = 50;
            $scope.orderBy = "DESC";
            $scope.orderByField = "id";
            $scope.sortIcon = 'keyboard_arrow_down';
            $scope.lists = [];
            $scope.fullList = [];
            $scope.csvUploadPath = '../../' + AJAX_PATH + 'extensions/productmanagement/import-campaigns';
            $scope.csvExportPath = '../../' + AJAX_PATH + 'extensions/productmanagement/export-campaigns';
            $scope.is_pmt_extention_enabled = false;
            $scope.is_import_export_enabled = false;
            $scope.isUploadProgress = false;

            // $scope.crmsTypes = ['limelight', 'konnektive', 'responsecrm' , 'velox', 'emanage', 'infusionsoft', 'sixcrm','nmi','m1billing'];
            $scope.crmsTypes = crms.crmsTypeList();

            $scope.filterActions = [{key : 'delete', val : 'Delete', method : 'deleteCampaign'}];
            $scope.filterTypes = [{key : 'all', val : 'All'}];
            $scope.selectedType = 'all';
            $scope.listIds = [];
            $scope.campaignTypes = {1: 'Default', 2 : 'Prepaid', 3 : $rootScope.orderFilterText};
            $scope.billingTypes = {1: 'Product Based', 2 : 'Billing Model'};

            $scope.crm_lists = {};
            
            $scope.configuration_lists = {};

            $scope.getConfigurations = function(){
                return new Promise((resolve, reject) => {
                    $http.post('../' + REST_API_PATH + 'configurations/all/').success(function(response) {
                        $scope.configuration_lists = response.data;
                        resolve(1);
                    });
                });
            }
            
            $scope.configCount = function(campaign_id){
                var count = 0;
                angular.forEach($scope.configuration_lists, function (value, key) {
                    var campIds = value.campaign_ids;
                    if((campIds).indexOf(campaign_id) != -1){
                        count = count + 1;
                    }
                });
                return count;
            }

            $scope.checkAsyncSplitExtension = function () {
                var url = '../' + REST_API_PATH + 'campaigns/check-product-management-extension';
                $http.get(url).then(function (response) {
                    if (response.data.hasOwnProperty('isProductManagementActive') && response.data.isProductManagementActive) {
                        $scope.is_pmt_extention_enabled = true;
                        $scope.is_import_export_enabled = response.data.enableImportExport;
                    }
                });
            };

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

            $scope.combineCRM = function(){
                return new Promise((resolve, reject) => {
                    $http.post('../' + REST_API_PATH + 'crms/all/').success(function(response) {
                        $scope.crm_lists = response.data;
                        angular.forEach($scope.lists, function (item, index) {
                            angular.forEach($scope.crm_lists, function (crm, index) {
                                if(item.crm_id == crm.id){
                                    item.crm_label = crm.crm_label;
                                    item.crm_type = crm.crm_type;
                                }
                            });
                            $scope.lists[index] = item;
                            $scope.fullList[index] = item;
                            resolve(1);
                        });
                    });
                });
            }

            $scope.getCampaigns = function () {
                var params = {
                    limit: $scope.limit,
                    offset: ($scope.currentPage - 1) * $scope.limit,
                    orderBy: $scope.orderBy,
                    orderByField: $scope.orderByField
                };
                $http.post('../' + REST_API_PATH + 'campaigns/all/', params)
                    .success(async function (response) {
                        await $scope.getConfigurations();
                        var data = [];
                        var i = 0;
                        angular.forEach(response.data, function (value, index) {
                            value.config_count = $scope.configCount(value.id);
                            if (($rootScope.advanced_mode == false && value.campaign_type != 3) || $rootScope.advanced_mode)
                            {
                                data[i] = value;
                                i++;
                            }
                        })
                        $scope.lists = data;
                        $scope.fullList = data;
                        $scope.totalItems = response.totalData;
                        if($scope.totalItems > 0){
                            $scope.paginationShow = true;
                        }
                        else{
                            $scope.paginationShow = false;
                        }
                        $scope.pages = Math.ceil($scope.totalItems / $scope.limit);

                        $scope.getListIds();
                        await $scope.combineCRM();
                        $scope.dataLoadComplete = true;
                    });
            }
            
            $scope.toggleOrderBy = function(val) {
                if($scope.orderBy == 'DESC') {
                    $scope.orderBy = 'ASC';
                    $scope.sortIcon = 'keyboard_arrow_up';
                }else{
                    $scope.orderBy = 'DESC';
                    $scope.sortIcon = 'keyboard_arrow_down';
                }
                $scope.orderByField = val;
                $scope.getCampaigns();
            }
            
            $scope.changeLimit = function() {
                $scope.currentPage = 1;
                $scope.orderBy = "DESC";
                $scope.orderByField = "id";
                $scope.sortIcon = 'keyboard_arrow_down';
                $scope.getCampaigns();
            }
            
            $scope.clear = function () {
                $scope.searchText = '';
            };
            
            $scope.getpagesArray = function(num) {
                if(!isNaN(num)) {
                    return new Array(num);
                }else{
                    return new Array(1);
                }
            }

            $scope.editCampaign = function (_id) {
                $location.path('/campaign-manager/' + _id);
            }

            $scope.deleteCampaign = function (id) {
                var url = '';
                if(angular.isObject(id)){
                    url = '../' + REST_API_PATH + 'campaigns/delete';
                    Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(id))});
                }
                else{
                    url = '../' + REST_API_PATH + 'campaigns/delete/' + id;
                    Dialog.showDialog(url, '', '');
                }
            }

            $scope.copyCampaign = function (index) {
                var copy = $scope.lists[index];
                Dialog.showCopyDialog('../' + REST_API_PATH + 'campaigns/add/', copy, '/campaign-manager/');
            };

            var formdata = new FormData();
            $scope.getTheFiles = function ($files) {

                formdata.append('file', $files[0]);
            };

            $scope.uploadFiles = function () {

                var confirm = $mdDialog.confirm()
                        .title('Upload')
                        .textContent('Are you sure you want to upload this file ?')
                        .ok('Yes')
                        .cancel('No');
                return $mdDialog.show(confirm).then(function () {
                    
                    var request = {
                        method: 'POST',
                        url: $scope.csvUploadPath,
                        data: formdata,
                        headers: {
                            'Content-Type': undefined
                        }
                    };
                    $scope.isUploadProgress = true;
                    $http(request)

                            .success(function (response) {
                                $scope.isUploadProgress = false;
                                if (typeof response != 'undefined' && response.success)
                                {
                                    Toast.showToast('File has been successfully imported.');
                                    $scope.getCampaigns();

                                } else
                                {
                                    if (typeof (response.not_valid) != 'undefined' && response.not_valid)
                                    {
                                        Toast.showToast('File was invalid, Please try again.');
                                    }
                                    else {
                                        Toast.showToast('File import has been failed, Please try again.');
                                    }

                                }
                            })
                            .error(function () {
                                $scope.isUploadProgress = false;
                                Toast.showToast('File import has been failed, Please try again.');
                            });
                });
            }

            $scope.campaignViewPopup = function(){
                
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

            /*Quick View*/
            $scope.quickViewModal = function(campaign){
                var data = {
                    campaign_id : campaign.campaign_id,
                    campaign_label : campaign.campaign_label,
                    crm : campaign.crm_label,
                    shipping_id : campaign.shipping_id,
                    campaign_type : $scope.campaignTypes[campaign.campaign_type],
                    billing_type : $scope.billingTypes[campaign.billing_type]
                }
                Dialog.dynamicTemplate('../app/views/modals/campaignQuickView.html', data);
            }

            $scope.updatePage = function(pageNumber) {
                $scope.currentPage = pageNumber;
                $scope.getCampaigns();
            }

            $scope.previousPage = function() {
                $scope.currentPage -= 1;
                $scope.getCampaigns();
            }

            $scope.nextPage = function() {
                $scope.currentPage += 1;
                $scope.getCampaigns();
            }

        }).directive('ngFiles', ['$parse', function ($parse) {

        function fn_link(scope, element, attrs) {
            var onChange = $parse(attrs.ngFiles);
            element.on('change', function (event) {
                onChange(scope, {$files: event.target.files});
            });
        }
        ;

        return {
            link: fn_link
        }
    }]);
