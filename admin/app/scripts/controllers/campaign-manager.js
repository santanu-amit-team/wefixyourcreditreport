'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:CampaignManager
 * @description
 * # CampaignManager
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')

        .controller('CampaignManager', function ($scope, $http, $routeParams, $location, Toast, $rootScope, taOptions, $mdDialog, Dialog) {

            $scope.campaignTypes = [
                {
                    key: '1',
                    val: 'Default'
                },
                {
                    key: '2',
                    val: 'Prepaid'
                },
                {
                    key: '3',
                    val: $rootScope.orderFilterText
                }
            ];
            $scope.defaultProductArray = function () {
                return {'product_id': '', 'product_price': '',
                    'retail_price': '', 'product_quantity': '', 'rebill_product_price': '',
                    'product_schedule': '', 'product_schedule_quantity': '', 'product_key': '', 'nmi_plan_id': '','product_name':''};
            };

            $scope.campaign = {};
            $scope.campaign.product_array = [];
            $scope.campaign.shipping_profiles = [];
            
            $scope.campaign.product_array.push($scope.defaultProductArray());

            $scope.crm_lists = {};
            $scope.adminPath = '../../';
            $scope.is_pmt_extention_enabled = false;
            $scope.uploadedFiles = [];
            $scope.productFiles = [];
            $scope.uploadPath = '../../' + AJAX_PATH + 'extensions/productmanagement/upload-image';
            $scope.isUploadProgress = false;
            $scope.enableCouponextension = false;
            $scope.inputData = {
                couponList: {
                    options: {},
                    default_option: 'Select Coupon Profile',
                    key: 'id',
                    label: 'coupon_label'
                }
            };
            $scope.coupon_code_separator = '\n';
            $scope.couponLoadComplete = false;
            $scope.validation = {
                coupon_ids: {status: true, message: '', required: true}
            };
            $scope.test = 0;


            taOptions.toolbar = [
                ['h1', 'h2', 'h3'],
                ['bold', 'italics', 'underline', 'html'],
                ['insertImage', 'insertLink']
            ];
            $scope.crm_type = '';
            $scope.billingModuleTypes = [{key: 1, label: 'Product Based'}, {key: 2, label: 'Billing Model'}];

            $http.post('../' + REST_API_PATH + 'crms/all/').success(function (response) {
                $scope.crm_lists = response.data;
            });

            $scope.crmTypeChange = function () {
                if ($scope.campaign.hasOwnProperty('crm_id')) {
                    angular.forEach($scope.crm_lists, function (value, key) {
                        if (value.id == $scope.campaign.crm_id) {
                            $scope.crm_type = value.crm_type;
                        }
                    });
                }
            }

            $scope.checkAsyncSplitExtension = function () {
                var url = '../' + REST_API_PATH + 'campaigns/check-product-management-extension';
                $http.get(url).then(function (response) {
                    if (response.data.hasOwnProperty('isProductManagementActive') && response.data.isProductManagementActive) {
                        $scope.is_pmt_extention_enabled = true;
                        $scope.getProductImagePath();
                        $http.get('../../' + AJAX_PATH + 'extensions/productmanagement/get-product-category').then(function (response) {
                            $scope.productTypes = response.data.product_category;
                        });
                    }
                });
            };

            $scope.getProductImagePath = function () {
                var url = '../../' + AJAX_PATH + 'extensions/productmanagement/get-image-path';
                $http.get(url).then(function (response) {
                    if (typeof response != 'undefined' && response.data.image_path) {
                        $scope.product_image_path = response.data.image_path;
                    }
                });
            };

            $http.post('../' + REST_API_PATH + 'campaigns/all/')
                    .success(function (response) {
                        $scope.campaignLists = response.data;
                        if ($routeParams.id) {
                            for (var i = 0; i < $scope.campaignLists.length; i++) {
                                if ($scope.campaignLists[i].id == $routeParams.id) {
                                    $scope.campaign = $scope.campaignLists[i];
                                    $scope.campaign.product_array = $scope.campaign.product_array.length ? JSON.parse($scope.campaign.product_array) : $scope.defaultProductArray();
                                    $scope.campaign.shipping_profiles =  ($scope.campaign.hasOwnProperty('shipping_profiles') && $scope.campaign.shipping_profiles.length) ? JSON.parse($scope.campaign.shipping_profiles) : null;
                                    if (typeof ($scope.campaign.product_paths) != 'undefined' && $scope.campaign.product_paths !== "")
                                    {
                                        $scope.productFiles = ($scope.campaign.product_paths).split("\n");
                                    }
                                    break;
                                }
                            }
                            $scope.crmTypeChange();
                        }
                    });



            $http.post('../' + REST_API_PATH + 'campaigns/check-extensions', { extention : 'Coupons' }).then(function (response) {

                if (!response.data.extensionCouponsActive) {
                    $scope.enableCouponextension = false;
                } 
                else {
                    $scope.enableCouponextension = true;
                }
            });

            // get all coupon list
            $scope.getAllCoupon = function(){

                $http.post('../' + REST_API_PATH + 'coupons/all/').success(function(response) {

                    if(response.data.length) {
                        $scope.inputData.couponList.options = response.data;
                        $scope.couponLoadComplete = true;
                    }
                });
            }

            $scope.getAllCoupon();

            $scope.SubmitForm = function () {
                if (typeof ($scope.campaign.product_paths) != 'undefined') {
                    var oldImages = ($scope.campaign.product_paths).split("\n");
                    var newImages = oldImages.concat($scope.uploadedFiles);
                    var product_paths = newImages.join('\r\n');
                    $scope.campaign.product_paths = product_paths.replace(/^\s+|\s+$/g, '');
                }

                var data = JSON.parse(JSON.stringify($scope.campaign));
                data.product_array = JSON.stringify($scope.campaign.product_array);
                data.shipping_profiles = JSON.stringify($scope.campaign.shipping_profiles);
                //data.coupon_ids = JSON.stringify($scope.campaign.coupon_ids);
              
                var url = '../' + REST_API_PATH + 'campaigns/add';
                if ($routeParams.id) {
                    url = '../' + REST_API_PATH + 'campaigns/edit/' + $routeParams.id;
                }
                console.log(data);
                $http.post(url, data)
                        .then(function (response) {
                            if (!response.data.success) {
                                $scope.CampForm.$submitted = false;
                                Toast.showToast(response.data.error_message);
                            } else {
                                $location.path('/campaigns');
                                Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
                            }
                        });
            }

            $scope.onGlobalSuccess = function (response) {
                if (typeof response != 'undefined' && response.status == 200)
                {
                    if (typeof (response.data.uploaded_files) != 'undefined' && response.data.success)
                    {
                        $scope.uploadedFiles = response.data.uploaded_files;
                    } 
                    else
                    {
                        Toast.showToast('Image file(s) are not valid, Please try again.');
                    }
                }
            };
            $scope.makeDefault = function (id) {
                var productArr = $scope.productFiles;
                productArr = array_move(productArr, id, 0);
                $scope.productFiles = productArr;

                var oldImages = ($scope.campaign.product_paths).split("\n");
                oldImages = array_move(oldImages, id, 0);
                var newImages = oldImages;
                var product_paths = newImages.join('\r\n');
                $scope.campaign.product_paths = product_paths.replace(/^\s+|\s+$/g, '');

            };

            $scope.deleteProduct = function (id) {

                var confirm = $mdDialog.confirm()
                        .title('Delete')
                        .textContent('Are you sure? This action cannot be undone')
                        .ok('Yes')
                        .cancel('No');
                return $mdDialog.show(confirm).then(function () {
                    $scope.unlinkFromServer($scope.productFiles[id]);
                    var productArr = $scope.productFiles;
                    (productArr).splice(id, 1);

                    var oldImages = ($scope.campaign.product_paths).split("\n");
                    (oldImages).splice(id, 1);

                    var newImages = oldImages;
                    var product_paths = newImages.join('\r\n');
                    $scope.campaign.product_paths = product_paths.replace(/^\s+|\s+$/g, '');
                    $scope.productFiles = productArr;
                });
            };

            $scope.deleteTempProduct = function (id) {
                var confirm = $mdDialog.confirm()
                        .title('Delete')
                        .textContent('Are you sure? This action cannot be undone')
                        .ok('Yes')
                        .cancel('No');
                return $mdDialog.show(confirm).then(function () {
                    $scope.unlinkFromServer($scope.uploadedFiles[id]);
                    var productArr = $scope.uploadedFiles;
                    (productArr).splice(id, 1);
                    $scope.uploadedFiles = productArr;
                });

            };

            $scope.unlinkFromServer = function (file) {
                //console.log('Unlink::', file);
                var url = '../../' + AJAX_PATH + 'extensions/productmanagement/unlink-image';
                var data = {unlinkfile: file};
                $http.post(url, data)
                        .then(function (response) {
                            if (response.data.success) {
                                Toast.showToast('Deleted successfully');
                            }
                        });

            };

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

            var formdata = new FormData();
            $scope.getTheFiles = function ($files) {
                angular.forEach($files, function (value, key) {
                    formdata.append(key, value);
                });
            };

            $scope.uploadFiles = function () {

                var confirm = $mdDialog.confirm()
                        .title('Upload')
                        .textContent('Are you sure you want to upload this file ?')
                        .ok('Yes')
                        .cancel('No');
                return $mdDialog.show(confirm).then(function () {
                    $scope.isUploadProgress = true;
                    var request = {
                        method: 'POST',
                        url: $scope.uploadPath,
                        data: formdata,
                        headers: {
                            'Content-Type': undefined
                        }
                    };

                    $http(request)

                            .success(function (response) {
                                $scope.isUploadProgress = false;
                                if (typeof response != 'undefined' && response.success)
                                {
                                    if (typeof (response.uploaded_files) != 'undefined' && response.success)
                                    {
                                        $scope.uploadedFiles = response.uploaded_files;
                                    } 
                                    else
                                    {
                                        Toast.showToast('Image file(s) are not valid, Please try again.');
                                    }
                                }
                            })
                            .error(function () {
                                $scope.isUploadProgress = false;
                                Toast.showToast('Upload process has failed, Please try again.');
                            });
                });
            }
            /*add product*/

            $scope.add = function(arr){
                $scope.campaign.product_array.push($scope.defaultProductArray());
            }
            $scope.remove = function(index){
                $scope.campaign.product_array.splice(index, 1);
            }

            $scope.openImportCmpPopup = function () {
                console.log($scope.campaign.crm_id);
                if ($scope.campaign.crm_id == null || typeof $scope.campaign.crm_id == "undefined"
                        || $scope.campaign.crm_id == "") {

                    Toast.showToast("Please select the CRM from above");
                    return;
                }
                if ($scope.campaign.campaign_id == null || typeof $scope.campaign.campaign_id == "undefined"
                        || $scope.campaign.campaign_id == "") {

                    Toast.showToast("Please enter CRM campaign");
                    return;
                }
                var data = {
                    'crm_id': $scope.campaign.crm_id,
                    'campaign_id': $scope.campaign.campaign_id,
                    'loader': true
                };
                $rootScope.CamapaignSyncData = data;
                $rootScope.crmCampaignData = [];
                $mdDialog.show({
                    controller: "CamapaignSync",
                    templateUrl: '../app/views/modals/importCampaign.html',
                    clickOutsideToClose: true
                });


            }


            $scope.cancel = function () {
                $mdDialog.cancel();
            };
            $scope.$watch(function (scope) {
                return $rootScope.crmCampaignData
            },
                    function () {
                        if(!$rootScope.hasOwnProperty('crmCampaignData') || 
                                !$rootScope.crmCampaignData.hasOwnProperty('productDetails')){
                            return;
                        }
                        var existing_product_array = $scope.campaign.product_array;
                        $scope.campaign.product_array = [];
                        var importProduct = false;
                        for (var i = 0; i < $rootScope.crmCampaignData['productDetails'].length; i++) {
                            if ($rootScope.crmCampaignData['productDetails'][i]['itemSelected']) {
                                importProduct = true;
                                $scope.campaign.product_array.push(
                                        {
                                            'product_id': 
                                            $rootScope.crmCampaignData['productDetails'][i]['product_id'],
                                            'product_price': 
                                            $rootScope.crmCampaignData['productDetails'][i]['product_price'],
                                            'retail_price': 
                                            $rootScope.crmCampaignData['productDetails'][i]['retail_price'], 
                                            'product_quantity': 
                                            $rootScope.crmCampaignData['productDetails'][i]
                                            ['product_quantity'],
                                            'rebill_product_price': 
                                            $rootScope.crmCampaignData['productDetails'][i]
                                            ['rebill_product_price'],
                                            'product_schedule': '', 'product_schedule_quantity': '',
                                            'product_key': '',
                                            'nmi_plan_id': '',
                                            'product_name':$rootScope.crmCampaignData['productDetails'][i]
                                            ['product_name']
                                        });
                            }
                        };
                        if(!importProduct){
                            $scope.campaign.product_array = existing_product_array;
                        }
                        $scope.campaign.shipping_profiles = [];
                        for (var i = 0; i < $rootScope.crmCampaignData['shippingDetails'].length; i++){
                            
                            if($rootScope.crmCampaignData['shippingDetails'][i]['shipping_id'] == 
                                    $scope.campaign.shipping_id){
                                $scope.campaign.shipping_price = 
                                        $rootScope.crmCampaignData['shippingDetails'][i]['shipping_price'];
                            }
                            if ($rootScope.crmCampaignData['shippingDetails'][i]['itemSelected']) {
                                 
//                                $scope.campaign.shipping_price = 
//                                        $rootScope.crmCampaignData['shippingDetails'][i]['shipping_price'];
//                                $scope.campaign.shipping_id = 
//                                        $rootScope.crmCampaignData['shippingDetails'][i]['shipping_id'];
                                
                                $scope.campaign.shipping_profiles.push({
                                    'shipping_id' : 
                                            $rootScope.crmCampaignData['shippingDetails'][i]['shipping_id'],
                                    'shipping_price' : 
                                            $rootScope.crmCampaignData['shippingDetails'][i]['shipping_price']
                                });
                            }
                            
                        }
                        if(!$routeParams.id && !($scope.campaign.shipping_profiles).length > 0){
                            $scope.campaign.shipping_id = '';
                            $scope.campaign.shipping_price = '';
                        }
                        if ($scope.campaign.shipping_profiles[0] &&
                                $scope.campaign.shipping_profiles[0]['shipping_id']){

                            $scope.campaign.shipping_id = $scope.campaign.shipping_profiles
                            [0]['shipping_id'];
                            $scope.campaign.shipping_price = $scope.campaign.shipping_profiles
                             [0]['shipping_price'];
                            ($scope.campaign.shipping_profiles).splice(0, 1);
                            console.log($scope.campaign.shipping_profiles);
                        }
                        
                    }
            );
    
            $scope.setDefaultShipping = function(index){
                var shipping_id = $scope.campaign.shipping_id;
                var shipping_price = $scope.campaign.shipping_price;
                $scope.campaign.shipping_id = $scope.campaign.shipping_profiles[index]['shipping_id'];
                $scope.campaign.shipping_price = $scope.campaign.shipping_profiles[index]['shipping_price'];
                if(typeof shipping_id == "undefined" || shipping_id == ""){
                    ($scope.campaign.shipping_profiles).splice(index, 1);
                    return;
                }
                $scope.campaign.shipping_profiles[index]['shipping_id'] = shipping_id;
                $scope.campaign.shipping_profiles[index]['shipping_price'] = shipping_price;
            }
            $scope.removeShipping = function(index){
                 $scope.campaign.shipping_profiles.splice(index, 1);
            }

            /*add product*/
        }).directive('ngFiles', ['$parse', function ($parse) {
        function fn_link(scope, element, attrs) {
            var onChange = $parse(attrs.ngFiles);
            element.on('change', function (event) {
                onChange(scope, {$files: event.target.files});
            });
            };
        return {
            link: fn_link
        }
    }]);
