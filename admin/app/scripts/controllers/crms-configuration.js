'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:CrmsConfigCtrl
 * @description
 * # CrmsConfigCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('CrmsConfigCtrl', function ($scope, $http, $routeParams, $location, Toast, $rootScope, crms) {

		// $scope.crmsTypes = ['limelight', 'konnektive', 'responsecrm' , 'velox', 'emanage', 'infusionsoft', 'sixcrm','nmi','m1billing'];
		$scope.crmsTypes = crms.crmsTypeList();
		$scope.crm = {};

		$scope.crm_id = $routeParams.id ? $routeParams.id : '';

		if ($routeParams.id) {
			$http.post('../' + REST_API_PATH + 'crms/get/' + $routeParams.id)
				.then(function (response) {
					if (response.data.success) {
						$scope.crm = response.data.data;
                                            if ($scope.crm.crm_type == 'responsecrm') {
                                                $scope.responsecrm = true;      
                                            } else if($scope.crm.crm_type == 'emanage') {
                                                $scope.emanage = true;
                                            } else if($scope.crm.crm_type == 'sixcrm') {
                                                $scope.sixcrm = true;
                                            } else if($scope.crm.crm_type == 'layer2') {
                                                $scope.layer2 = true;
                                            }
					}
				});
		}

		$scope.SubmitForm = function () {
			var url = '../' + REST_API_PATH + 'crms/add';
			if ($routeParams.id) {
				url = '../' + REST_API_PATH + 'crms/edit/' + $routeParams.id;
			}
			$http.post(url, $scope.crm)
				.then(function (response) {
					if (!response.data.success) {
						$scope.CrmForm.$submitted = false;
						Toast.showToast(response.data.error_message);
					} else {
						$location.path('/crms');
						Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
					}
				});
		}

		$scope.selectApiEndpoint = function ()
		{
			if ($routeParams.id) {
			} else {
				if ($scope.crm.crm_type == 'konnektive') {
					$scope.crm.endpoint = 'https://api.konnektive.com';
				}else if ($scope.crm.crm_type == 'responsecrm') {
					$scope.crm.endpoint = 'https://openapi.responsecrm.com/api/v2/open';
				}else if ($scope.crm.crm_type == 'sixcrm') {
					$scope.crm.endpoint = 'https://api.sixcrm.com';
				}else if ($scope.crm.crm_type == 'layer2') {
					$scope.crm.endpoint = 'https://api.lyr2.com/v1/legacy_konnektive';
				}else {
					$scope.crm.endpoint = '';
				}

			}
		}
		$scope.updateFields = function ()
		{
                    $scope.responsecrm = false;
                    $scope.emanage = false;
                    $scope.sixcrm = false;
                    $scope.layer2 = false;
                    if ($scope.crm.crm_type == 'responsecrm') {
                        $scope.responsecrm = true;      
                    }else if($scope.crm.crm_type == 'emanage') {
                        $scope.emanage = true;
                    }else if($scope.crm.crm_type == 'sixcrm') {
                        $scope.sixcrm = true;
                    }else if($scope.crm.crm_type == 'layer2') {
                        $scope.layer2 = true;
                    }
		}
	});
