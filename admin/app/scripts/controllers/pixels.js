'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:PixelManagerCtrl
 * @description
 * # PixelManagerCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('Pixels', function ($scope, $http, $routeParams, $location, Toast, Dialog, $filter) {

		/**
		 * Pixel Types
		 */
		$scope.pixelTypes = [
			'HTML',
			'Postback',
			'Postback + 3rd Party Pixels',
			'Cake Postback',
			'Cake Postback + 3rd Party Pixels',
			'HasOffers Postback',
			'HasOffers Postback + 3rd Party Pixels',
			'HitPath Postback',
			'HitPath Postback + 3rd Party Pixels',
            'General',
            'Decline',
            'Submission'
		];

		/*filterbar setup*/
		$scope.listIds = [];
		$scope.filterActions = [
                    {key : 'delete', val : 'Delete', method : 'deletePixel'},
                    {key : 'copy', val : 'Copy', method : 'copyPixel'}
                ];
		$scope.filterTypes = [{key : 'all', val : 'All'}];
		$scope.selectedType = 'all';
		$scope.loadTypes = function(){
			angular.forEach($scope.pixelTypes, function (type, index) {
				$scope.filterTypes.push({key: type, val : $filter('ucfirst')(type)});
			});
		}
		$scope.loadTypes();
		$scope.getListIds = function(){
			angular.forEach($scope.lists, function (item, index) {
				$scope.listIds.push(item.id);
			});
		}
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
		/*filterbar setup*/

		$scope.affiliates = {
			affId: 'AFFID',
			afId: 'AFID',
			sId: 'SID',
			c1: 'C1',
			c2: 'C2',
			c3: 'C3',
			c4: 'C4',
			c5: 'C5',
			c6: 'C6',
			aId: 'AID',
			opt: 'OPT',
			clickId: 'click_id'
		};

		$scope.devices = [
			{id: 'mobile_android', label: 'Android Mobile'},
			{id: 'iphone', label: 'iphone'},
			{id: 'tablet', label: 'Tablet'},
			{id: 'ipad', label: 'ipad'},
			{id: 'desktop', label: 'Desktop'},
		];

		$scope.editPixel = function (id) {
			$location.path('/pixel-setup/' + id);
		}

		$scope.deletePixel = function (id) {
			// Dialog.showDialog('../' + REST_API_PATH + 'pixels/delete/' + id);
			var url = '';
			if(angular.isObject(id)){
				url = '../' + REST_API_PATH + 'pixels/delete/';
				Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(id))});
			}
			else{
				url = '../' + REST_API_PATH + 'pixels/delete/' + id;
				Dialog.showDialog(url, '', '');
			}
		}

		$scope.copyPixel = function (index) {
                    
                        ($scope.lists).forEach(function(v,i){
                            if(v.id == index)
                            {
                                index = i;
                                return;
                            }
                        });
                        
			var copy = $scope.lists[index];
			Dialog.showCopyDialog('../' + REST_API_PATH + 'pixels/add/', copy, '/pixel-setup/');
		};

		$scope.getPixel = function (p) {

			if (p.pixel_type == 'HTML') {
				return p.html_pixel;
			}

			else if (p.pixel_type == 'Postback' || p.pixel_type == 'Postback + 3rd Party Pixels') {
				return p.postback_url;
			}

			else {
				return p.convert_pixel;
			}
		};

		$http.post('../' + REST_API_PATH + 'pixels/all/')
			.success(function (response) {
				$scope.lists = response.data;
				/*filter bar setup*/
				$scope.fullList = response.data;
	        	$scope.getListIds();
				$scope.dataLoadComplete = true;
				/*filter bar setup*/
			});

			$scope.getDevices = function(list){
				var arr = list.split(','); 
				var returnArr = [];
				for (let k in arr){
					for (let dev in $scope.devices){
						if($scope.devices[dev].id == arr[k]){
							returnArr.push($scope.devices[dev].label);
						}
					}
				} 
				return  returnArr.join(',');
			}

	});
