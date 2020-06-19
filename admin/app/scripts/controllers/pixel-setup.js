'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:PixelSetupCtrl
 * @description
 * # PixelSetupCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('PixelSetupCtrl', function ($scope, $http, $routeParams, $location, Toast, $timeout, $filter) {

		$scope.pixel = {};
		$scope.settings = {};
		$scope.pixel.pixel_type = 'Conversion Pixel (HTML)';
		$scope.pixel.enable_funnel = true;
		$scope.pixel.start_date = new Date();
		$scope.pixel.end_date = new Date();
		$scope.page_trigger_separator = ',';
		$scope.page_exceptions_separator = ',';
		$scope.aff_separator  = ',';
		$scope.sub_aff_separator  = ',';
		$scope.device_separator = ',';
		$scope.configuration_id_separator = ',';
		$scope.pixel.pixel_placement = 'head';
		$scope.pixel.postback_url = 'https://domain.com';
		$scope.pixel.pixel_firing_option = 'once_per_session';

		$scope.firingOptions = [
			{key : 'once_per_session', label : 'Once per session'},
			{key : 'always', label : 'Always'}
		];

		$scope.validation = {
			affiliate_id_value : {status : true, message : '', required: true},
			page : {status : true, message : '', required: true},
			exceptions : {status : true, message : '', required: true},
			device : {status : true, message : '', required: true},
			configuration_id : {status : true, message : '', required: true}
			};

		/**
		 * Pixel Placements
		 */
		$scope.pixelPlacements = [
			{key : 'head', label : 'HTML <head>'},
			{key : 'top', label : 'BODY - OPEN <body>'},
			{key : 'bottom', label : 'BODY - CLOSE </body>'}
		];

		/**
		 * Pixel Types
		 */
		$scope.pixelTypes = [
			'Conversion Pixel (HTML)',
			'Conversion Pixel (Server to Server)',
			'Conversion Pixel (HTML+Postback)',
//			'Cake Postback',
//			'Cake Postback + 3rd Party Pixels',
//			'HasOffers Postback',
//			'HasOffers Postback + 3rd Party Pixels',
//			'HitPath Postback',
//			'HitPath Postback + 3rd Party Pixels',
                        'General',
                        'On Decline',
                        'On Form Submission'
		];

		$scope.affiliates = {
                        nid: 'NID',
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
                
                $scope.sub_affiliates = {
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

		// $scope.oss = [
		// 	"All",
		// 	"AndroidOS",
		// 	"iOS"
		// ];

		$scope.inputData = {
			affiliate_id : {
				options : {},
				default_option : 'Select affiliate ID',
				key : 'id',
				label : 'label'
			},
			device : {
				options : $scope.devices,
				default_option : 'Select devices...',
				key : 'id',
				label : 'label'
			},
			configurations : {
				options : {},
				default_option : 'Select funnel configurations…',
				key : 'id',
				label : 'configuration_label'
			}
		};
		$scope.timezone_identifiers_list = {};

		$http.post('../' + REST_API_PATH + 'configurations/all/')
			.success(function (response) {
				$scope.configuration_lists = response.data;
				$scope.inputData.configurations.options = $scope.configuration_lists;
			});

		$http.post('../' + REST_API_PATH + 'settings/timezone_identifiers_list')
			.success(function (response) {
				$scope.timezone_identifiers_list = JSON.parse(response.data);
			});


		/*
		 * Pull settings data to override pixel default settings
		 *
		 */
		$http.post('../' + REST_API_PATH + 'settings/get/1')
			.success(function (response) {
				$scope.settings = response.data;
				if(!$routeParams.id){
					$scope.pixel.time_zone = $scope.settings.app_timezone;
				}
			});

		$scope.showClickConvert = function () {
			var b = [
				'Cake Postback',
				'Cake Postback + 3rd Party Pixels',
				'HasOffers Postback',
				'HasOffers Postback + 3rd Party Pixels',
				'HitPath Postback',
				'HitPath Postback + 3rd Party Pixels'
			];
			return b.indexOf($scope.pixel.pixel_type) + 1;
		};

		$scope.show3rdpartyPixel = function () {
			var a = [
                                'Conversion Pixel (HTML+Postback)',
                                'Postback + 3rd Party Pixels',
				'Cake Postback + 3rd Party Pixels',
				'HasOffers Postback + 3rd Party Pixels',
				'HitPath Postback + 3rd Party Pixels'
			];
			return a.indexOf($scope.pixel.pixel_type) + 1;
		}

		// $scope.showOs = function () {
		// 	var isMobile = false;
		// 	var devices = [];
		// 	if(angular.isDefined($scope.pixel.device) && angular.isString($scope.pixel.device)){
		// 		devices = $scope.pixel.device.split(',');
		// 	}
		// 	if(devices.indexOf('mobile') !== -1 || devices.indexOf('tablet') !== -1) {
		// 	  isMobile = true;
		// 	}
		// 	else{
		// 		isMobile = false;
		// 	  	$scope.pixel.os = '';
		// 	}
		// 	// if(devices.indexOf('desktop') !== -1) {
		// 	//   isMobile = false;
		// 	//   $scope.pixel.os = '';
		// 	// }
		// 	return isMobile;
		// };

		// $scope.osAll = function () {
		// 	if($scope.pixel.os.indexOf("All") !== -1){
		// 		$scope.pixel.os = ["All"];
		// 	}
		// }

		$scope.validationMethod = function(){
	        var validation = true;
	        angular.forEach($scope.validation, function (value, index) {
	            if(!value.status){
	                validation = false;
	            }
	        });
	        // console.log()
	        if(!$scope.pixel.configuration_id || 
	        	($scope.pixel.enable_device == true && !$scope.pixel.device) ||
	        	// ($scope.pixel.enable_page == true && 
	        	// 	(!$scope.pixel.page || !$scope.pixel.exceptions)
	        	// ) ||
	        	($scope.pixel.enable_affiliate_parameters == true && 
	        		(!$scope.pixel.affiliate_id_key || !$scope.pixel.affiliate_id_value )
        		)
	        	) {
	        	return false;
	        }
	        return validation;
	    }

		$scope.savePixel = function () {
			// $scope.pixel.affiliate_id = JSON.stringify($scope.pixel.affiliate_id);
			// $scope.pixel.sub_id = JSON.stringify($scope.pixel.sub_id);
			// $scope.pixel.os = JSON.stringify($scope.pixel.os);
			// $scope.pixel.page_trigger = JSON.stringify($scope.pixel.page_trigger);
			// $scope.pixel.exceptions = JSON.stringify($scope.pixel.exceptions);

			var data = angular.copy($scope.pixel);
            // data.start_date = new Date(  data.start_date.getTime() -   data.start_date.getTimezoneOffset() * 60000).toISOString();
            // data.end_date = new Date(  data.end_date.getTime() -   data.end_date.getTimezoneOffset() * 60000).toISOString();

            data.start_date = $filter('date')(data.start_date, 'yyyy-MM-dd');     
            data.end_date = $filter('date')(data.end_date, 'yyyy-MM-dd');    

			data.last_edited = new Date();
			var url = '../' + REST_API_PATH + 'pixels/add';
			if ($routeParams.id) {
				url = '../' + REST_API_PATH + 'pixels/edit/' + $routeParams.id;
			}

			$http.post(url, data)
				.then(function (response) {
					if (!response.data.success) {
						Toast.showToast(response.data.error_message);
					} else {
						$location.path('/pixels');
						Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
					}
				});
		};

		if ($routeParams.id) {

			$http.post('../' + REST_API_PATH + 'pixels/get/' + $routeParams.id)
				.then(function (response) {
					if (response.data.success) {
						$scope.pixel = response.data.data;
						$scope.pixel.affiliate_id = ($scope.pixel.affiliate_id) ? JSON.parse($scope.pixel.affiliate_id) : '';
						$scope.pixel.sub_id = ($scope.pixel.sub_id) ? JSON.parse($scope.pixel.sub_id) : '';
						$scope.pixel.os = ($scope.pixel.os) ? JSON.parse($scope.pixel.os) : '';
						$scope.pixel.start_date = $scope.pixel.start_date ? new Date($scope.pixel.start_date): '';
						$scope.pixel.end_date = $scope.pixel.start_date ? new Date($scope.pixel.end_date):'';
                                                if($scope.pixel.pixel_type == 'HTML')
                                                {
                                                    $scope.pixel.pixel_type = 'Conversion Pixel (HTML)';
                                                }
                                                else if($scope.pixel.pixel_type == 'Postback')
                                                {
                                                    $scope.pixel.pixel_type = 'Conversion Pixel (Server to Server)';
                                                }
                                                else if($scope.pixel.pixel_type == 'Postback + 3rd Party Pixels')
                                                {
                                                    $scope.pixel.pixel_type = 'Conversion Pixel (HTML+Postback)';
                                                }
                                                else if($scope.pixel.pixel_type == 'Decline')
                                                {
                                                    $scope.pixel.pixel_type = 'On Decline';
                                                }
                                                else if($scope.pixel.pixel_type == 'Submission')
                                                {
                                                    $scope.pixel.pixel_type = 'On Form Submission';
                                                }
                                                
					}
				});
		}
	});
