'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:UserManager
 * @description
 * # UserManager
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')

        .controller('UserManager', function ($scope, $http, $routeParams, $location, Toast, $rootScope, taOptions, $mdDialog, $timeout) {

            $scope.userTypes = [
                {
                    key: '1',
                    val: 'Admin'
                },
                {
                    key: '2',
                    val: 'Standard'
                },
                {
                    key: '3',
                    val: 'Super Admin'
                }
            ];

            $scope.user = {};
            $scope.spinner = false;

            $scope.autoFillPermissions = [
                {
                    userType : '1', // User Type Admin
                    permissions: [ 
                        'change_access_permissions', 
                        'ecommerce',
                        'campaigns',
                        'funnel_configurations',
                        'coupons',
                        'system',
                        'crm',
                        'settings',
                        'extensions',
                        'tools',
                        'pixel_manager'
                    ]
                },
                {
                    userType : '2', // User Type Standard
                    permissions: [
                        'change_access_permissions',
                        'ecommerce',
                        'campaigns',
                        'funnel_configurations',
                        'coupons',
                        'system',
                        'crm',
                        'settings',
                        'cms',
                        'tools',
                        'pixel_manager',
                    ]
                },
                {
                    userType : '3', // User Type Super Admin
                    permissions: [
                        'change_access_permissions',
                        'ecommerce',
                        'campaigns',
                        'funnel_configurations',
                        'coupons',
                        'system',
                        'crm',
                        'users',
                        'settings',
                        'cms',
                        'tools',
                        'affiliate_manager',
                        'pixel_manager',
                        'rotators',
                        'mid_routing',
                        'traffic_monitor',
                        'auto_responder',
                        'auto_filters',
                        'scheduler',
                        'diagnosis',
                        'extensions',
                        'logs',
                        'systems_log',
                        'user_activity',
                        'change_log',
                        'upsell_manager',
                        'split_test',
                    ]
                }
            ];

            if ($routeParams.id) {
                $http.post('../' + REST_API_PATH + 'users/get/' + $routeParams.id)
                        .then(function (response) {
                            if (response.data.success) {
                                $scope.user = response.data.data;
                            }
                        });
            }


            //save the data
            $scope.SubmitForm = function(){

                var data = JSON.parse(JSON.stringify($scope.user));

                var url = '../' + REST_API_PATH + 'users/add';
                if ($routeParams.id) {
                    url = '../' + REST_API_PATH + 'users/edit/' + $routeParams.id;
                }

                $http.post(url, data)
                        .then(function (response) {
                            if (!response.data.success) {
                                $scope.UserForm.$submitted = false;
                                Toast.showToast(response.data.error_message);
                            } else {
                                $location.path('/users');
                                Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
                            }
                        });
            }
           

            //Auto Filler permissions
            $scope.modifyPermission = function (index) {
                //reset the value
                $scope.autoFillPermissions[2].permissions.forEach(element => {
                    $scope.user[element] = false;
                });

                //reassign the value
                $scope.autoFillPermissions[index].permissions.forEach(element => {
                    $scope.user[element] = true;
                });
            }

            $scope.autoFiller = function() {
                
                if($routeParams.id) return;
                
                switch($scope.user.user_type) {
                    case '1' : 
                        $scope.modifyPermission(0);
                        break;
                    case '2' :
                        $scope.modifyPermission(1);
                        break;
                    case '3': 
                        $scope.modifyPermission(2);
                        break;
                    default:
                        break;
                }
            }

            $scope.generatorPassword = function() {

                $scope.spinner = true;
                var length = 8,
                charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
                retVal = "";
                for (var i = 0, n = charset.length; i < length; ++i) {
                    retVal += charset.charAt(Math.floor(Math.random() * n));
                }

                $scope.user.password = retVal;
                $timeout(function(){ 
                    $scope.spinner = false;
                }, 2000);
            }
            
        }).directive('ngFiles', ['$parse', function ($parse) {
            
        }]);
