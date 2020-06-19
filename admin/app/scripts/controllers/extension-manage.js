'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:ExtensionCtrl
 * @description
 * # ExtensionCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('ExtensionsManageCtrl',
		function ($scope, Toast, Dialog, $http, $location, $window, $routeParams, $filter, $rootScope, $sce, $compile) {
			var id, extensions, extension_inputs, extension, extension_data, save_url, data, activeData;
			$scope.showView = false;
			$scope.customView = false;
                        $scope.validation = {};
                        
			$scope.inputsLimit = [];
			$scope.v_name = $routeParams.file_name;
			$scope.btn={
				save : 'Save'
			};
                        
			$http.post('../' + REST_API_PATH + 'extensions/get-extension/' + $routeParams.file_name, {
			}).then(function (response) {
				$scope.$parent.$parent.page_title = 'Extensions » ' + response.data.extension.extension_name;
				$scope.showView = true;
                                if(response.data.custom_html)
                                {
                                    $scope.customView = true;
                                    $scope.showView = false;
                                    $scope.customHtml = response.data.custom_html;
                                    $scope.parentData = $scope.crm;
                                }
				if (response.data.success == true) {
					extension = response.data.extension;
					$scope.extensionTitle = extension.extension_name;

					//set extension schema
					extension_inputs = response.data.schema;
					$scope.extension_inputs = extension_inputs;
					$scope.setFlag = response.data.flag;

					//set extension datas
					if (response.data.flag === "Not edited") {
						extension_data = response.data.datas;
						save_url = '../' + REST_API_PATH + 'extensions/add-manage-data/' + $routeParams.file_name;
					} else {
						extension_data = response.data.datas;
						save_url = '../' + REST_API_PATH + 'extensions/edit-manage-data/' + $routeParams.file_name;
					}
					$scope.extension = extension_data;
					$scope.saveExtension = function () {
						$scope.btn={
									save : 'Saving...'
								};
						$scope.disabled={
							save : true
						};
						$http.post(save_url, $scope.extension)
							.then(function (response) {
								$scope.editResponse = response.data;
								$scope.$broadcast('extensionEdited', $scope.editResponse);

								if (!response.data.success) {
									Dialog.alertDialog(response.data.error_message);
									$scope.btn={
										save : 'Save'
									};
									$scope.disabled={
										save : false
									};
								} else {
									Toast.showToast(response.data.message);
									$location.path('/extensions');
								}

							});
					}
				}
			}, function (response) {
					$scope.error_msg = 'Extension Not Found';
			});
		});
	