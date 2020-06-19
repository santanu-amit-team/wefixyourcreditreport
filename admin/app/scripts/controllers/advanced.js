'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Advanced
 * @description
 * # Advanced
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('Advanced', function($scope, $templateCache, $compile, $http, $location, Toast, Dialog, $rootScope, $mdDialog) {
    $scope.advanced = {};
    $scope.showText = true;
    $scope.advancedPassword = '*****'
    $http.post('../' + REST_API_PATH + 'advanced/get/1').then(function(response) {
        if (response.data.success) {
            $scope.advanced = response.data.data;
            /**
             * Need to parse this value,
             * since it's getting stored in the db as string
             * @type {string}
             */
            $scope.advanced.scrapper = $scope.advanced.hasOwnProperty('scrapper') ? ($scope.advanced.scrapper.length ? JSON.parse($scope.advanced.scrapper) : '') : '';
            if ($rootScope.advanced_mode) {
                $scope.checkExtension(1);
                $scope.checkGatewaySwitcherId(1);
            }
        }
    });
    $scope.SubmitForm = function(alert_flag) {
        if (
            $scope.advanced.scrapper.percentage !== undefined &&
            $scope.advanced.scrapper.percentage[1] !== undefined &&
            $scope.advanced.scrapper.percentage[2] !== undefined &&
            (parseInt($scope.advanced.scrapper.percentage[1]) > parseInt($scope.advanced.scrapper.percentage[2]))
        ) {
            Toast.showToast('Step 1 percentage should be less than Step 2');
            return;
        }
        var data = JSON.parse(JSON.stringify($scope.advanced));
        data.scrapper = JSON.stringify($scope.advanced.scrapper);
        var url = '../' + REST_API_PATH + 'advanced/edit/1';
        $http.post(url, data).then(function(response) {
            if (!response.data.success) {
                Toast.showToast(response.data.error_message);
            } else {
                if (alert_flag) {
                    $scope.AdvancedForm.$invalid = false;
                    Toast.showToast("All changes are saved successfully");
                }
            }
        }).finally(function(){
            $scope.AdvancedForm.$submitted = false;
        });
    }
    $scope.checkExtension = function($updateStatus) {
        if ($scope.advanced.scrapper.enable == true) {
            $http.get('../' + REST_API_PATH + 'advanced/extension-exists').then(function(response) {
                if (!response.data.success) {
                    (!$updateStatus) ? Dialog.alertDialog(response.data.error_message): '';
                    $scope.advanced.scrapper.enable = false;
                    ($updateStatus) ? $scope.SubmitForm(0): '';
                }
            });
        }
    }
    $scope.checkGatewaySwitcherId = function($updateStatus) {
        if ($scope.advanced.scrapper.remote == true) {
            $http.get('../' + REST_API_PATH + 'advanced/check-gateway-switcher').then(function(response) {
                if (!response.data.success) {
                    (!$updateStatus) ? Dialog.alertDialog(response.data.error_message): '';
                    $scope.advanced.scrapper.remote = false;
                    ($updateStatus) ? $scope.SubmitForm(0): '';
                }
            });
        }
    }
    $scope.showPassword = function() {
        $scope.advancedPassword = $scope.advanced.advancedPassword;
        $scope.showText = false;
    }
    $scope.showResetSettings = false;
    $scope.closeDomainSwitchAlert = function() {
        $mdDialog.cancel();
    };
    $scope.resetSettings = function() {
        $mdDialog.cancel();
        $mdDialog.show($mdDialog.confirm({
            title: 'Are you sure?',
            textContent: 'Confirming "Yes" will cleanup all existing configurations (which might be there if this CodeBase is copied) and generate new passwords.',
            ok: 'Yes',
            cancel: 'No'
        })).then(function() {
            $http.post('../' + REST_API_PATH + 'advanced/reset-settings', {
                confirm: 'yes'
            }).then(function(response) {
                if (response.data.success) {
                    $scope.showResetSettings = false;
                }
                if (typeof response.data !== 'object' || !response.data.hasOwnProperty('message')) {
                    Toast.showToast('Action called');
                } else {
                    if (response.data.type === 'dialog') {
                        Dialog.alertCustomDialog(response.data.message);
                    } else {
                        Toast.showToast(response.data.message);
                    }
                }
            });
        });
    };
    $scope.registerDomain = function() {
        $mdDialog.cancel();
        $mdDialog.show($mdDialog.confirm({
            title: 'Are you sure?',
            textContent: 'Confirming "Yes" will hide the Installer option from "Advanced Settings" and clear all Installer alerts from the Dashboard.',
            ok: 'Yes',
            cancel: 'No'
        })).then(function() {
            $http.post('../' + REST_API_PATH + 'advanced/register-domain', {
                confirm: 'yes'
            }).then(function(response) {
                if (response.data.success) {
                    $scope.showResetSettings = false;
                }
                if (typeof response.data !== 'object' || !response.data.hasOwnProperty('message')) {
                    Toast.showToast('Action called');
                } else {
                    if (response.data.type === 'dialog') {
                        Dialog.alertCustomDialog(response.data.message);
                    } else {
                        Toast.showToast(response.data.message);
                    }
                }
            });
        });
    };
    $scope.checkDomainSwitch = function($event) {
        $http.get('../' + REST_API_PATH + 'advanced/check-domain-switch').then(function(data) {
            if (!data.data.switched) {
                return;
            }
            $scope.showResetSettings = true;
        });
    };
    $scope.checkDomainSwitch();
});