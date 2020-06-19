'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:OfferManagerCtrl
 * @description
 * # OfferManagerCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('ConfigurationManager', function($scope, $http, $routeParams, $location, Toast, $mdDialog) {
    $scope.configLoadComplete = false;
    $scope.upsellMethod = {
        newOrder: 'New Order',
        newOrderCardOnFile: 'New Order Card On File',
        newOrderCardOnFileWithCvv: 'New Order Card On File with CVV',
        importUpsell: 'Import Upsell',
        upsell: 'Upsell Order',
    };
    $scope.delay_types = [{key: 'fixed', label: 'Fixed'}, {key: 'dynamic', label: 'Dynamic'}];
    $scope.retry_preauth_amount_separator = ',';
    $scope.decline_reason_separator = "\n";
    $scope.validation = {
            retry_preauth_amount : {status : true, message : ''}
            }; 
    $scope.split_validation = {
            retry_preauth_amount_split : {status : true, message : ''}
            }; 
    $scope.steps = [
        {key : 1, value : 'Initial'}, 
        {key : 2, value : 'Upsell'}, 
        {key : 3, value : 'Downsell'}, 
        {key : 4 , value : 'Others'}
    ];
    $scope.subscription_value_type_list = {
        recurring_days: 'Recurring Days',
        subscription_week: 'Subscription Week',
        subscription_day: 'Subscription Day'
    };
    $scope.url_sources = [
        {key: 'static', label: 'Static'}, 
        {key: 'siteurl', label: 'Site URL'}, 
        {key: 'mid_routing', label: 'Mid Routing'}
    ];
    $scope.crm_gateway_settings_list = [
        {key: 'default', label: 'Default'},
        {key: 'force_gateway', label: 'Force Gateway'},
        {key: 'mid_router', label: 'Mid Router - Extension'},
        {key: 'mid_router_201clicks', label: 'Mid Router - Almost20'}
    ];
//    $scope.mid_routing_profiles = [
//        {key: 'profile_name_1', label: 'Profile Name 1'},
//        {key: 'profile_name_2', label: 'Profile Name 2'}
//    ];
    $scope.crmsTypes = ['limelight', 'konnektive', 'responsecrm' , 'velox', 'emanage', 'infusionsoft', 'sixcrm','nmi','m1billing'];
    $scope.iuSupportedCrms = ['konnektive', 'velox', 'layer2'];
    $scope.config = {};
    $scope.config.upsell_preferred_method = 'newOrder';
    $scope.config.split_preferred_method = 'newOrder';
    // $scope.config.preserve_gateway = true;
    $scope.config.split_charge = false;
    $scope.config.split_force_parent_gateway = true;
    $scope.config.link_with_parent = true;
    $scope.config.accept_prepaid_cards = false;
    $scope.config.enable_downsells = false;
    $scope.crm_type = '';
    $scope.extensionUpdate = {};
    $scope.showExtensions = false;

    $scope.loadCampaignList = function(){
        $http.post('../' + REST_API_PATH + 'campaigns/all/').success(function(response) {
            $scope.campaign_lists = response.data;
            $scope.campaign_lists_copy = angular.copy($scope.campaign_lists);
            if($routeParams.id) {
                $scope.refreshCampaignList($scope.config.crm_id);
            }
        });
    }
    
    $http.post('../' + REST_API_PATH + 'crms/all/').success(function(response) {
        $scope.crm_lists = response.data;
    });
    $http.post('../' + REST_API_PATH + 'configurations/all/').success(function (response) {
        $scope.configurations = response.data;
    });
    $http.post('../' + REST_API_PATH + 'midrouting/all/').success(function(response) {
        $scope.mid_routing_profiles = response.data;
    });
    if ($routeParams.id) {
        $http.post('../' + REST_API_PATH + 'configurations/get/' + $routeParams.id).then(function(response) {
            if (response.data.success) {
                $scope.config = response.data.data;
                $scope.config.crm_type = response.data.data.crm_type;
                $scope.config.retry_preauth_amount = 
                $scope.config.retry_preauth_amount.length ? JSON.parse($scope.config.retry_preauth_amount) : [];
                $scope.config.retry_preauth_amount_split = 
                $scope.config.retry_preauth_amount_split.length ? JSON.parse($scope.config.retry_preauth_amount_split) : [];
                $scope.config.split_campaign_ids = 
                    $scope.config.split_campaign_ids.length ? JSON.parse($scope.config.split_campaign_ids)[0] : '';
                $scope.config.campaign_ids = 
                    $scope.config.campaign_ids.length ? JSON.parse($scope.config.campaign_ids)[0] : '';
                $scope.loadCampaignList();
                $scope.configLoadComplete = true;
            }
        });
    }
    else{
        $scope.loadCampaignList();
        $scope.configLoadComplete = true;
    }

    $scope.validationMethod = function(){
        var validation = true;
        angular.forEach($scope.validation, function (value, index) {
            if(!value.status){
                validation = false;
            }
        });
        return validation;
    }
    $scope.checkAsyncSplitExtension = function() {
        if (!$scope.config.split_charge) {
            return;
        }
        $scope.ConfigForm.$submitted = true;
        var url = '../' + REST_API_PATH + 'configurations/check-async-split-extension';
        $http.get(url).then(function(response) {
            if (response.data.hasOwnProperty('isAsyncSplitActive') && response.data.isAsyncSplitActive) {
                return;
            }
            $mdDialog.show($mdDialog.alert({
                textContent: 'To enable split activate "Asynchronous Split" extension.',
                ok: 'Ok'
            }));
            $scope.config.split_charge = false;
        }).finally(function() {
            $scope.ConfigForm.$submitted = false;
        });
    };
    $scope.CheckCrm = function() {
        var crmfilterObj = $scope.crm_lists.filter(function(e) {
            return e.id == $scope.config.crm_id;
        });
        $scope.config.crm_type = crmfilterObj[0].crm_type;

        $scope.refreshCampaignList($scope.config.crm_id);
    };
    $scope.SubmitForm = function() {
        var data = $scope.config;
        var campaign_ids = [];
        var split_campaign_ids = [];
        campaign_ids.push(data.campaign_ids);
        split_campaign_ids.push(data.split_campaign_ids);

        data.campaign_ids = JSON.stringify(campaign_ids);
        data.split_campaign_ids = JSON.stringify(split_campaign_ids);
        data.retry_preauth_amount = JSON.stringify(data.retry_preauth_amount);
        data.retry_preauth_amount_split = JSON.stringify(data.retry_preauth_amount_split);

        var url = '../' + REST_API_PATH + 'configurations/add';
        if ($routeParams.id) {
            url = '../' + REST_API_PATH + 'configurations/edit/' + $routeParams.id;
        }
        $http.post(url, data).then(function(response) {
            if (!response.data.success) {
                $scope.ConfigForm.$submitted = false;
                Toast.showToast(response.data.error_message);
            } else {

                if(angular.isDefined(response.data.data.id)){
                    $scope.updateExtensionConfigIds(response.data.data.id);
                }
                
                $location.path('/configurations');
                Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
            }
        });
    }

    $scope.refreshCampaignList = function(crmId){

        $scope.campaign_lists = angular.copy( $scope.campaign_lists_copy);
        $scope.campaign_lists = $scope.campaign_lists.filter((item) => { return item.crm_id == crmId });
        
        if(!$scope.campaign_lists.length) {
            $scope.config.campaign_ids = '';
            // var crm_label = $scope.crm_lists.filter((item) => item.id == $scope.config.crm_id)[0].crm_label;
            // $scope.campaign_lists = [
            //     {
            //         campaign_type: 1,
            //         campaign_label: "No Campaign exist for " + crm_label + '.',
            //     }
            // ];
        }
    }

    $scope.loadExtensions = function() {

        $http.get('../' + REST_API_PATH + 'dashboard/check-permission/extensions').then(function(response) {
            if(!response.data.success){
                return;
            }
            else {
                
                var url = '../' + REST_API_PATH + 'configurations/get-extension-list/';

                $http.get(url).then(function(response) {
                    if (response.data.success) {
                        $scope.extensionList = response.data.data;
                        if ($routeParams.id) {
                            angular.forEach($scope.extensionList, function(element, index){
                                $http.get('../' + REST_API_PATH + 'extensions/is-extension-activeIn-config/' + $routeParams.id + '/' + element.extension_slug).then(function(response) {
                                    if(response.data.success) {
                                        $scope.extensionUpdate[element.extension_slug] = true;
                                    }
                                    else {
                                        $scope.extensionUpdate[element.extension_slug] = false;
                                    }
                                });
                            });
                            $scope.showExtensions = true;
                        }
                        else {
                            $scope.showExtensions = true;
                        }
                        
                    }
                });
            }
        });
    }
    $scope.loadExtensions();

    $scope.updateExtensionConfigIds = function(currentConfigId){
        var url = '../' + REST_API_PATH + 'extensions/edit-config-ids/' + currentConfigId;
        $http.post(url, { data: JSON.stringify($scope.extensionUpdate) }).then(function(response) {
           //
        });
    }
    
    $scope.enablePopup = function() {
        $scope.config.exit_popup_enabled = $scope.config.enable_downsells;
    }
    
    $scope.decline_reason_validation = {
        decline_reasons : {status : true, message : '', required: true}
    };
    
    $http.post('../' + REST_API_PATH + 'affiliates/all/')
                    .success(function (response) {
                        $scope.affiliateList = response.data;
                    });
});
