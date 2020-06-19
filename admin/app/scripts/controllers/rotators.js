'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Rotators
 * @description
 * # Rotators
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('CheckExtensionsDialog', function($scope, $mdDialog){
    	$scope.closeDialog = function(){
    		$mdDialog.hide();
    	};
    })
	.controller('Rotators', function ($scope, $templateCache, $compile, $http, $location, Dialog, $mdDialog) {

		$http.post('../' + REST_API_PATH + 'rotators/all/')
			.success(function (response) {
				$scope.lists = response.data;
			});

		$scope.editRotator = function (_id) {
			$location.path('/rotator-manager/' + _id);
		}

		$scope.deleteRotator = function (id) {
			Dialog.showDialog('../' + REST_API_PATH + 'rotators/delete/' + id);
		}

		$scope.checkExtensions = function(){
			$http.get('../' + REST_API_PATH + 'rotators/check-extensions').then(function(response) {
				if(!response.data.extensionRotarorsActive){
					var message = 'Install <strong>Rotators</strong> extension' +
						' and activate <strong>Rotators</strong> from <strong>Settings</strong>';
					$mdDialog.show($mdDialog.alert({
		            	template: ' <md-dialog>' +
						            '	<md-dialog-content>' +
						            '	' + '<p style="padding: 15px; padding-top: 30px; text-align: center;">' + message + '</p>' +
						            '  	</md-dialog-content>' +
						            '  	<md-dialog-actions>' +
						            '   	<md-button ng-click="closeDialog()" class="md-primary">' +
						            '      		Ok' +
						            '   	</md-button>' +
						            '  	</md-dialog-actions>' +
						            '</md-dialog>',
						    controller: 'CheckExtensionsDialog'
		            }));
				}

	        });
		}
		//$scope.checkExtensions();

	});
