'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:AffiliateManager
 * @description
 * # AffiliateManager
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('AffiliateManager', function ($scope, $rootScope, $http, $routeParams, $location, Toast, $mdDialog) {
		$scope.configTypes = [
			'default',
			'advanced'
		];
		$scope.affiliateLists = [
			'affid',
			'afid',
			'sid',
			'c1',
			'c2',
			'c3',
			'c4',
			'c5',
			'aid',
			'opt',
			'click_id'
		];
		$scope.affiliate = {};
		$scope.affiliate_references = [];
		$scope.redirect_traffic = [];
		$scope.affiliate.config_type = 'default';
		$scope.affiliate = {
			affid: '',
			afid: '',
			sid: '',
			c1: '',
			c2: '',
			c3: '',
			c4: '',
			c5: '',
			aid: '',
			opt: '',
			click_id: ''
		};
		$scope.affiliate.affiliates = [{
				affiliate_id: '',
				affiliate_value: ''
			}
		];


		$scope.addRow = function () {
			$scope.affiliate.affiliates.push({
				affiliate_id: '',
				affiliate_value: ''
			});
		}

		$scope.deleteRow = function (index) {
			$scope.affiliate.affiliates.splice(index, 1);
		}

		$scope.affiliate.configuration_mapping = [['','']];
		$scope.addRowConfig = function () {
			$scope.affiliate.configuration_mapping.push(['','']);
		}

		$scope.deleteRowConfig = function (index) {
			$scope.affiliate.configuration_mapping.splice(index, 1);
		}
                
                $scope.affiliate.campaign_mapping = [['','']];
		$scope.addRowCampaign = function () {
			$scope.affiliate.campaign_mapping.push(['','']);
		}

		$scope.deleteRowCampaign = function (index) {
			$scope.affiliate.campaign_mapping.splice(index, 1);
		}

		$http.post('../' + REST_API_PATH + 'configurations/all/')
			.success(function (response) {
				$scope.configurations = response.data;
			});
                $http.post('../' + REST_API_PATH + 'campaigns/all/')
			.success(function (response) {
				$scope.campaigns = response.data;
			});
		if ($routeParams.id) {
			$http.post('../' + REST_API_PATH + 'affiliates/get/' + $routeParams.id)
				.then(function (response) {
					if (response.data.success) {
						$scope.affiliate = response.data.data;
						$scope.affiliate.affiliates = [];
						angular.forEach($scope.affiliateLists, function (value, index) {
							if ($scope.affiliate[value].length)
							{
								$scope.affiliate.affiliates.push({
									affiliate_id: value,
									affiliate_value: $scope.affiliate[value]
								});
							}
						});

						if (!$scope.affiliate.affiliates.length)
						{
							$scope.affiliate.affiliates = [{
									affiliate_id: '',
									affiliate_value: ''
								}
							];
						}

						if (!$scope.affiliate.configuration_mapping.length)
						{
							$scope.affiliate.configuration_mapping = [['','']];
						}
						//$scope.changeConfigurationType();
                                                if (!$scope.affiliate.campaign_mapping.length)
						{
							$scope.affiliate.campaign_mapping = [['','']];
						}
					}

				});
		}

		$scope.SubmitForm = function () {
//                        if(($scope.affiliate.enable_campaign_configuration || $scope.affiliate.enable_funnel_configuration ) && $scope.affiliate.config_type != 'advanced') {
//                            Toast.showToast('Configuration type should be advanced');
//                            $scope.AffiliateForm.$submitted = false;
//                            return;
//                        }
			if (parseInt($scope.affiliate.scrap_step_1) > parseInt($scope.affiliate.scrap_step_2))
			{
				Toast.showToast('Step 1 percentage should be less than Step 2');
				$scope.AffiliateForm.$submitted = false;
				return;
			}

			$scope.affiliate.configuration_mapping = ($scope.affiliate.configuration_mapping.length) ? JSON.stringify($scope.affiliate.configuration_mapping) : '';
                        $scope.affiliate.campaign_mapping = ($scope.affiliate.campaign_mapping.length) ? JSON.stringify($scope.affiliate.campaign_mapping) : '';
			if ($scope.affiliate.affiliates.length)
			{
				angular.forEach($scope.affiliateLists, function (value, index) {
					var index = $scope.affiliate.affiliates.findIndex(x=> x.affiliate_id == value);
					$scope.affiliate[value] = (index > -1) ? $scope.affiliate.affiliates[index].affiliate_value : '';
				});
			}
			var url = '../' + REST_API_PATH + 'affiliates/add';
			if ($routeParams.id) {
				url = '../' + REST_API_PATH + 'affiliates/edit/' + $routeParams.id;
			}
			$http.post(url, $scope.affiliate)
				.then(function (response) {
					if (!response.data.success) {
						$scope.AffiliateForm.$submitted = false;
						Toast.showToast(response.data.error_message);
					} else {
						$location.path('/affiliates');
						Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
					}
				});
		}
                
                $rootScope.networkLists = [];
                $http.post('../' + REST_API_PATH + 'affiliates/get-network-details')
			.success(function (response) {
                                $rootScope.networkLists = response.data;
			});
                        
                $scope.openPopup = function () {
                    $mdDialog.show({
                        controller: "addNetwork",
                        templateUrl: '../app/views/modals/addNetwork.html',
                        clickOutsideToClose: true
                    });
                }

	});
