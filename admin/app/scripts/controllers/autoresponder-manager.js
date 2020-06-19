'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:CampaignManager
 * @description
 * # CampaignManager
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
.controller('AutoresponderManager', function ($scope, $http, $routeParams, $location, Toast, $rootScope, taOptions, $mdDialog) {

    $scope.autoresponder = {};
    $scope.autoresponder.enable_ssl = false;
    $scope.adminPath = '../../';
    taOptions.toolbar = [
        ['h1', 'h2', 'h3'],
        ['bold', 'italics', 'underline', 'html']
    ];

    $scope.trigger_types = [ 
        {
            'key' : '1',
            'value' : 'Order Confirmation' 
        }, 
        {
            'key' : '2',
            'value': 'Custom'
         }
    ];

    $scope.smtpTypes  = [ 'default', 'custom'];

    $scope.bcc_separator = "\n";
    $scope.validation = {};
    $scope.validation = {
        notified_email : {status : true, message : '', required: true }
    };
    $scope.autoresponder.smtp_verify = false;

 
    if ($routeParams.id) {
        $http.post('../' + REST_API_PATH + 'autoresponder/get/' + $routeParams.id)
                .then(function (response) {
                    if (response.data.success) {
                        $scope.autoresponder = response.data.data;
                    }
                });
    }

    $scope.SubmitForm = function () {

        var url = '../' + REST_API_PATH + 'autoresponder/add';
        if ($routeParams.id) {
            url = '../' + REST_API_PATH + 'autoresponder/edit/' + $routeParams.id;
        }
        $http.post(url, $scope.autoresponder)
                .then(function (response) {
                    if (!response.data.success) {
                        $scope.AutoresponderForm.$submitted = false;
                        Toast.showToast(response.data.error_message);
                    } else {
                        $location.path('/auto-responder');
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
    

    $scope.checkSMTP = function ()
    {
        console.log('Called');
        $scope.loaderSmtpVerify = true;
        var data = {};
        data.from_email = $scope.autoresponder.smtp_senders_email;
        data.from_name = $scope.autoresponder.smtp_sender_name;
        data.smtp_host = $scope.autoresponder.smtp_host;
        data.smtp_port = $scope.autoresponder.smtp_port;
        data.smtp_username = $scope.autoresponder.smtp_username;
        data.smtp_password = $scope.autoresponder.smtp_password;
        data.smtp_mode = $scope.autoresponder.smtp_mode;

        var url = '../' + REST_API_PATH + 'settings/check-smtp';

        $scope.autoresponder.smtp_verify = false;

        $http.post(url, data).then(function (response) {
            
            if(response.data.success) {
                $scope.autoresponder.smtp_verify = true;
                $scope.loaderSmtpVerify = false;
                Toast.showToast(response.data.success_message);
            }
            else {

                $scope.autoresponder.smtp_verify = false;
                $scope.loaderSmtpVerify = false;
                Toast.showToast(response.data.error_message);
            }
        });
    }


});
