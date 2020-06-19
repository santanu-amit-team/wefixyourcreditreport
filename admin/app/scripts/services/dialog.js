'use strict';
/**
 * @ngdoc service
 * @name codeBaseAdminApp.Dialog
 * @description
 * # Dialog
 * Factory in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp').controller('CheckCouponExtensionsDialog', function ($scope, $mdDialog, $location) {
    $scope.closeDialog = function () {
			$mdDialog.hide();
			$location.path('/extensions');

		};
	}).controller('CamapaignSync', function ($scope, $mdDialog, $location,$http,$rootScope,Toast) {
            
          $scope.productDetails = {};
          $scope.shippingDetails = {};
             $scope.crmCampaignData = [];
            $scope.getCRMCampaignData = function(){
                
                $http.post(
                        '../' + REST_API_PATH + 'campaigns/get-crm-campaign-data',
                        $rootScope.CamapaignSyncData
                    )
                    .then(function (response) {
                        if(!response.data || !response.data.success){
                            $scope.cancel();
                            Toast.showToast(
                                (!response.data || !response.data.error_message) ? 
                                "Unable to import." : response.data.error_message 
                            );
                            return;
                        }
                        $scope.crmCampaignData = response.data.data;
                        for(var i=0;i<$scope.crmCampaignData['productDetails'].length;i++){
                            $scope.crmCampaignData['productDetails'][i]['itemSelected'] = false;
                        }
                        for(var i=0;i<$scope.crmCampaignData['shippingDetails'].length;i++){
                            $scope.crmCampaignData['shippingDetails'][i]['itemSelected'] = false;
                        }
                });
            }
            
            $scope.toggleAll = function(type) {
                var toggleStatus = !$scope[type].isAllSelected;
                angular.forEach($scope.crmCampaignData[type], function(itm){
                    itm['itemSelected'] = !toggleStatus; 
                });
   
            }
  
            $scope.optionToggled = function(type){
            
              $scope[type].isAllSelected = $scope.crmCampaignData[type].every(function(itm){
                  return  itm['itemSelected']; 
              })
                 
            }
            
            $scope.cancel = function () {
				$mdDialog.cancel();
			};
            $scope.importSelected = function(){
                $rootScope.crmCampaignData = $scope.crmCampaignData;
                $scope.cancel();
            };
            
            
	}).controller('addNetwork', function ($scope, $mdDialog, $location,$http,$rootScope,Toast) {
            
            $scope.submitNetworkForm = function(){
                console.log($scope.network);
                $http.post(
                        '../' + REST_API_PATH + 'affiliates/save-network-details',
                        $scope.network
                    )
                    .then(function (response) {
                        console.log(response);
                        if(!response.data || !response.data.success){
                            $scope.cancel();
                            Toast.showToast(
                                (!response.data || !response.data.error_message) ? 
                                "Unable to import." : response.data.error_message 
                            );
                            return;
                        } else {
                            $scope.cancel();
                            Toast.showToast(
                                "Added Successfully"
                            );
                            $http.post('../' + REST_API_PATH + 'affiliates/get-network-details')
                                .success(function (response) {
                                    $rootScope.networkLists = response.data;
                                });
                            return;
                        }
                });
            }
            
            $scope.cancel = function () {
				$mdDialog.cancel();
			};
            
            
	})
	.factory('Dialog', function ($mdDialog, $http, $route, Toast, $location) {

		function ShowHelpController($scope, $mdDialog) {
			$scope.cancel = function () {
				$mdDialog.cancel();
			};
		}
		function ShowAlertController($scope, $mdDialog, content) {
			$scope.cancel = function () {
				$mdDialog.cancel();
			};
			$scope.content = content;
		}

		function dynamicTempController($rootScope, $scope, $mdDialog, content, url, model, formName){

	        $scope.data = content;
	        $scope.cancel = function () {
				$mdDialog.cancel();
			};

			$scope.removeIframe = $rootScope.removeIframe ;
			$scope.isIframeLoaded = false;

			$rootScope.$watch('removeIframe', function(newValue) {
				$scope.removeIframe = newValue;
			});

			window.fireloadEvent = function(element) {

				element.style.visibility = 'visible';
				$scope.isIframeLoaded = true;
				$scope.data.url = window.location.href;
				try {
					element.contentWindow.postMessage($scope.data , '*');
				}
				catch(err) {

					console.log(err);
				}
				
			}

			$scope.SubmitForm = function(){

				var data = JSON.parse(JSON.stringify($scope[model]));
		
				$http.post(url, data).then(function (response) {
					if (!response.data.success) {
		
						$scope[formName].$submitted = false;
						Toast.showToast(response.data.error_message);
					} 
					else {

						$mdDialog.cancel();
						Toast.showToast(response.data.success_message);
					}
				});
			};
                        
           
			
			$scope.copyToClipBoard = function(){	
				
				try {

					// create temp element
					var copyElement = document.createElement("textarea");
					copyElement.appendChild(document.createTextNode($scope.data));
					copyElement.id = 'tempCopyToClipboard';
					angular.element(document.body.append(copyElement));

					// select the text
					var range = document.createRange();
					range.selectNode(copyElement);
					window.getSelection().removeAllRanges();
					window.getSelection().addRange(range);

					// copy & cleanup
					document.execCommand('copy');
					window.getSelection().removeAllRanges();
					copyElement.remove();

					Toast.showToast('Content of .htaccess copied to clipboard.');
				}
				catch(err) {
					console.log(err.message);
				}
			}
    	}

    	function alertDialogFn(content, okBtnMessage){
    		okBtnMessage = (typeof okBtnMessage !== 'undefined' && okBtnMessage !== '') ? okBtnMessage : 'Got it!';
				return $mdDialog.show(
					$mdDialog.alert()
					.parent(angular.element(document.querySelector('#popupContainer')))
					.clickOutsideToClose(true)
					.textContent(content)
					.ariaLabel('Alert')
					.ok(okBtnMessage)
					);
    	}

		return {
			alertDialog: function (content, okBtnMessage) {
				return alertDialogFn(content, okBtnMessage);
			},
			showDialog: function ($url, title, data) {
				var confirm = $mdDialog.confirm()
					.title(title ? title : 'Delete')
					.textContent('Are you sure? This action cannot be undone')
					.ok('Yes')
					.cancel('No');
				return $mdDialog.show(confirm).then(function () {
					if(data != ''){
						$http.post($url, data)
						.then(function (response) {
							if (response.data.success)
							{
								$route.reload();
								Toast.showToast(response.data.message ? response.data.message : 'Deleted successfully');
							}
							else
							{
								alertDialogFn(response.data.error_message);
							}
						});
					}
					else{
						$http.post($url)
						.then(function (response) {
							if (response.data.success)
							{
								$route.reload();
								Toast.showToast(response.data.message ? response.data.message : 'Deleted successfully');
							}
							else
							{
								alertDialogFn(response.data.error_message);
							}
						});
					}
					
				});
			},
            clearDialog: function ($url, title) {
				var confirm = $mdDialog.confirm()
					.title(title ? title : 'Clear All')
					.textContent('Are you sure? This action cannot be undone')
					.ok('Yes')
					.cancel('No');
				return $mdDialog.show(confirm).then(function () {
					$http.post($url)
						.then(function (response) {
							if (response.data.success)
							{
								$route.reload();
								Toast.showToast(response.data.message ? response.data.message : 'Deleted successfully');
							}
							else
							{
								Toast.showToast(response.data.error_message);
							}
						});
				});
			},
			showCopyDialog: function ($url, $data, $redirect_url) {
				var confirm = $mdDialog.confirm()
					.title('Copy')
					.textContent('Are you sure?')
					.ok('Yes')
					.cancel('No');

				return $mdDialog.show(confirm).then(function () {
					$http.post($url, $data)
						.then(function (response) {
							if (response.data.success)
							{
								$location.path($redirect_url + response.data.data.id);
								Toast.showToast('Copied successfully');
							}
							else
							{
								Toast.showToast(response.data.error_message);

							}
						});

				});
			},
			showCustomDialog: function ($page) {
				return $mdDialog.show({
					controller: ShowHelpController,
					templateUrl: '../help.php?page=' + $page,
					parent: angular.element(document.body),
					clickOutsideToClose: true,
					//fullscreen: $scope.customFullscreen // Only for -xs, -sm breakpoints.
				});
			},
			versionConfirmDialog: function (url, data) {
				var confirm = $mdDialog.confirm()
					.title('Restore')
					.textContent('Are you sure? This action cannot be undone')
					.ok('Yes')
					.cancel('No');
				return $mdDialog.show(confirm).then(function () {
					$http.post(url, data)
						.then(function (response) {
							if (response.data.success == true)
							{
								$route.reload();
							}
						});
				});
			},
			alertCustomDialog: function (content) {

				return $mdDialog.show({
					locals: {
						content: content
					},
					controller: ShowAlertController,
					templateUrl: 'views/alertDialog.html',
					parent: angular.element(document.body),
					clickOutsideToClose: true
				});
			},
			dynamicTemplate: function(templateUrl, content , url, model, formName, reqController){

				var reqController = (typeof reqController === "undefined" || typeof reqController === null) ? dynamicTempController : reqController;
				var url = (typeof url === "undefined" || typeof url === null) ? '' : url;
				var model = (typeof model === "undefined" || typeof model === null) ? '' : model;
				var formName = (typeof formName === "undefined" || typeof formName === null) ? '' : formName;

				return $mdDialog.show({
		            controller: reqController,
		            templateUrl: templateUrl,
		            locals: {
						content: content,
						url: url,
						model: model,
						formName: formName
					},
		            parent: angular.element(document.body),
		            clickOutsideToClose: true
	        	});
			}
		};
	});
