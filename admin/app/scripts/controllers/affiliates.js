'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Affiliates
 * @description
 * # Affiliates
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('CheckExtensionsDialog', function($scope, $mdDialog){
    	$scope.closeDialog = function(){
    		$mdDialog.hide();
    	};
    })
	.controller('Affiliates', function ($scope, $templateCache, $compile, $http, $location, Dialog, Toast, $mdDialog, $filter) {

		$scope.dataLoadComplete = false;
		$scope.filterTypes = [{key : 'all', val : 'All'}];
		$scope.configTypes = [
			'default',
			'advanced'
		];
		$scope.listIds = [];
		$scope.filterActions = [{key : 'delete', val : 'Delete', method : 'deleteMultipleAffiliate'}];


		$scope.getAffiliateIds = function(){
			angular.forEach($scope.lists, function (item, index) {
				$scope.listIds.push(item.id);
			});
		}

		$scope.clear = function () {
			$scope.searchText = '';
		};

		$scope.loadTypes = function(){
			angular.forEach($scope.configTypes, function (type, index) {
				$scope.filterTypes.push({key: type, val : $filter('ucfirst')(type)});
			});
		}
		$scope.loadTypes();

		$http.post('../' + REST_API_PATH + 'affiliates/all/')
			.success(function (response) {
				$scope.lists = response.data;
				$scope.fullList = response.data;
				$scope.getAffiliateIds();
				$scope.dataLoadComplete = true;
			});

		$scope.editAffiliate = function (_id) {
			$location.path('/affiliate-manager/' + _id);
		}
		
		$scope.deleteMultipleAffiliate = function(ids) {
			var url = '';
			if(angular.isObject(ids)){
				url = '../' + REST_API_PATH + 'affiliates/delete/';
				Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(ids))});
			}
			else{
				url = '../' + REST_API_PATH + 'affiliates/delete/' + ids;
				Dialog.showDialog(url, '', '');
			}
		}
		
		$scope.copyToClip = function (content)
		{
			try {

				// create temp element
				var copyElement = document.createElement("span");
				copyElement.appendChild(document.createTextNode(content));
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

				Toast.showToast('URL copied to clipboard.');
			}
			catch(err) {

			}
		}

		$scope.checkExtensions = function(){
			$http.get('../' + REST_API_PATH + 'affiliates/check-extensions').then(function(response) {

				var message = '', isMultipleAlert = false;
				if(!response.data.extensionAffiliatesActive){
					message += 'Install <strong>Affiliates</strong> extension' +
							' and activate <strong>Affiliates</strong> from <strong>Settings</strong>';
					isMultipleAlert = true;
				}

				if(message !== ''){
					message = '<p style="padding: 15px; text-align: center;">' + message + '</p>';
					$mdDialog.show($mdDialog.alert({
		            	template: ' <md-dialog>' +
						            '	<md-dialog-content>' +
						            '	' + message +
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

		/** Multiple Checkbox work **/
		$scope.selectedRows = [];
		$scope.toggle = function (item) {
			var idx = $scope.selectedRows.indexOf(item);
			if (idx > -1) { $scope.selectedRows.splice(idx, 1);}
			else { $scope.selectedRows.push(item); }
		};
		
		$scope.exists = function (item) {
			return $scope.selectedRows.indexOf(item) > -1;
		};

	});
