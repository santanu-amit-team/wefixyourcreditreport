angular.module('codeBaseAdminApp')
        .controller('unifyUtility', function ($scope, $templateCache, $compile, Toast, $http, $location, $mdDialog, Dialog, $rootScope, Toast) {

            $scope.$parent.validation = {
                custom_scripts: {
                    status: true,
                    message: ''
                }
            };


            $scope.defaultStepWiseMethods = function () {
                return {
                    'crm_msg': '',
                    'custom_msg': ''
                }
            };
            //$scope.affiliateList = ['AFFID', 'AFID', 'SID', 'C1', 'C2', 'C3', 'C4'];

            $http.post('../' + REST_API_PATH + 'affiliates/all/')
                    .success(function (response) {
                        $scope.affiliateList = response.data;
                    });

            if (typeof $scope.extension.custom_decline_msg == "undefined" ||
                    !$scope.extension.custom_decline_msg.length > 0) {
                $scope.extension.custom_decline_msg = [];
                $scope.extension.custom_decline_msg.push($scope.defaultStepWiseMethods());
                console.log($scope.extension.custom_decline_msg);
            }
            $scope.add_custom_decline_msg = '';


            $scope.addMethod = function () {
                $scope.extension.custom_decline_msg.push($scope.defaultStepWiseMethods());
            };
            $scope.removeMsg = function (index) {
                $scope.extension.custom_decline_msg.splice(index, 1);
            }

            $scope.edit = function (index) {
                console.log($scope.extension.custom_decline_msg);
                if (!$scope.extension.custom_decline_msg[index]['crm_msg']) {
                    $scope.extension.custom_decline_msg[index]['crm_msg'] = "";
                    Toast.showToast('Add CRM message');
                    return false;
                }
                if (!$scope.extension.custom_decline_msg[index]['custom_msg']) {
                    $scope.extension.custom_decline_msg[index]['custom_msg'] = "";
                    Toast.showToast('Add custom message');
                    return false;
                }

            }

            $scope.defaultKeys = {
                'preserve_gateway': function () {
                    return   {
                        'source_step': '',
                        'destination_step': ''
                    }
                },
                'decline_redirection': function () {
                    return   {
                        'redirection_step': '',
                        'desktop_redirection': '',
                        'mobile_redirection': ''
                    }
                }
            };

            for (var key in $scope.defaultKeys) {
                if (!$scope.extension.hasOwnProperty(key) || !$scope.extension[key].length > 0) {
                    $scope.extension[key] = [];
                    $scope.extension[key].push($scope.defaultKeys[key]());
                }
            }


            $scope.add = function (multiKeyIndex) {
                $scope.extension[multiKeyIndex].push($scope.defaultKeys[multiKeyIndex]());
            };
            
            $scope.remove = function (index, multiKeyIndex) {
                $scope.extension[multiKeyIndex].splice(index, 1);
            };
        });