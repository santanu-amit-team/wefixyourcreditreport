'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:BasicConfigurationsCtrl
 * @description
 * # BasicConfigurationsCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('SettingsExtra', function ($scope, $mdDialog, $location) {
		$scope.closeDialog = function () {
			$mdDialog.hide();
		};
	})
	.controller('Settings', function ($scope, $rootScope, $http, Settings, Toast, $location, $route, $sce, Resolver, $anchorScroll, $mdDialog) {
		
		$scope.validation = {
			country_lang_mapping : {status : true, message : ''}, 
			allowed_test_cards : {status : true, message : ''}
			}; 
		$scope.country_lang_mapping_separator = '\n';
		$scope.allowed_test_cards_separator = '\n';
		$scope.settingsLoadComplete = false;
		$scope.LicenseKeyReadOnly = true;
		$scope.verify_status = false;
        $scope.loaderVerifyLK = false;
		$scope.loaderVerify = false;
		$scope.loaderSmtpVerify = false;
		$scope.dbEngineList = [{ value : 'mysql', label : 'mySQL'}, {value: 'sqlite', label : 'SQLite'}];
		$scope.cardTypes = [
			['visa', 'Visa'],
			['master', 'Master Card'],
			['amex', 'American Express'],
			['discover', 'Discover'],
			['jcb', 'JCB'],
			['maestro', 'Maestro'],
			['solo', 'Solo'],
			['laser', 'Laser'],
			['diners', 'Diners'],
			['paypal', 'PayPal'],
			['sepa', 'Sepa'],
                        ['COD', 'Cash On Delivery'],
                        ['payu', 'PayU'],
                        ['DIRECTDEBIT', 'DirectDebit'],
                        ['square', 'Square'],
		];
		$scope.validation_errors = ['modal', 'inline', 'hide'];
		$scope.allowed_test_cards_arr = [];
		$scope.allowed_test_card_input = [];
		$scope.settings = {};
		$scope.settings.db_verify = false;
		$scope.settings.smtp_verify = false;
		$scope.settings.lk_verify = false;
		$scope.showNextPrevBtn = false;
		$scope.prevSection = -1;
		$scope.currentSection = 0;
		$scope.nextSection = 0;
		$scope.highlight = [];

		$http.post('../' + REST_API_PATH + 'settings/get/1')
			.success(function (response) {
				$scope.settings = response.data;
				if (Object.keys(response.data).length)
				{
					/**
					 * Need to parse this value,
					 * since it's getting stored in the db as string
					 * @type {string}
					 */
					$scope.settings.allowed_country_codes = $scope.settings.allowed_country_codes.length ? JSON.parse($scope.settings.allowed_country_codes) : [];
					$scope.settings.allowed_card_types = $scope.settings.allowed_card_types.length ? JSON.parse($scope.settings.allowed_card_types) : [];
				}
				$scope.settingsLoadComplete = true;
                if($scope.settings.lk_verify)
                {
                    $scope.verify_status_text = $sce.trustAsHtml('<i class="success material-icons">done</i> Verified');
                    $scope.color_code = '#47D04C';
                    $scope.verify_status = true;
                }
			});

		$http.post('../' + REST_API_PATH + 'settings/timezone_identifiers_list')
			.success(function (response) {
				$scope.timezone_identifiers_list = JSON.parse(response.data);
			});
		$scope.validationMethod = function(){
			var validation = true;
			angular.forEach($scope.validation, function (value, index) {
				if(!value.status){
					validation = false;
				}
			});
			return validation;
		}

		$scope.SubmitForm = function () {
			var data = JSON.parse(JSON.stringify($scope.settings));
			data.allowed_country_codes = JSON.stringify($scope.settings.allowed_country_codes);
			data.allowed_card_types = JSON.stringify($scope.settings.allowed_card_types);

			var url = '../' + REST_API_PATH + 'settings/edit/1';
			$scope.SettingsForm.submitted = true;
			$http.post(url, data)
				.then(function (response) {
					if (!response.data.success) {
						$scope.SettingsForm.$invalid = false;
						Toast.showToast(response.data.error_message);
					} else {
						$scope.SettingsForm.$invalid = false;
						Toast.showToast("All changes are saved successfully");
					}
				}).finally(function(){
					$scope.SettingsForm.$submitted = false;
				});
		}

		//Populate Countries
		$http.get('../../assets/storage/country_states.json')
			.then(function (response) {
				$scope.countryList = [];
				angular.forEach(response.data, function (value, key) {
					if (value.FIELD1.length > 0 && value.FIELD2.length > 0) {
						$scope.countryList.push({
							country: value.FIELD1,
							iso: value.FIELD2
						});
					}
				});
			});

		$scope.checkDatabaseConnection = function ()
		{
			$scope.loaderVerify = true;
			var data = {};
			data.db_host = $scope.settings.db_host;
			data.db_username = $scope.settings.db_username;
			data.db_password = $scope.settings.db_password;
			data.db_name = $scope.settings.db_name;

			var url = '../' + REST_API_PATH + 'settings/check-database-connection';
			$http.post(url, data)
				.then(function (response) {
					$scope.settings.db_verify = response.data.data.db_verify;
					$scope.loaderVerify = false;
					if(!response.data.success) {
						Toast.showToast("Database Connection failed.");
					}

					if(!response.data.data.db_verify) {
						Toast.showToast("Database Connection failed.");
					}
				});

		}

		$scope.checkSMTP = function ()
		{
			$scope.loaderSmtpVerify = true;
			var data = {};
			data.from_email = $scope.settings.from_email;
			data.from_name = $scope.settings.from_name;
			data.smtp_name = $scope.settings.smtp_name;
			data.smtp_host = $scope.settings.smtp_host;
			data.smtp_port = $scope.settings.smtp_port;
			data.smtp_username = $scope.settings.smtp_username;
			data.smtp_password = $scope.settings.smtp_password;
			data.smtp_mode = $scope.settings.smtp_mode;

			var url = '../' + REST_API_PATH + 'settings/check-smtp';

			$scope.settings.smtp_verify = false;

			$http.post(url, data).then(function (response) {
				
				if(response.data.success) {
					$scope.settings.smtp_verify = true;
					$scope.loaderSmtpVerify = false;
					Toast.showToast(response.data.success_message);
				}
				else {

					$scope.settings.smtp_verify = false;
					$scope.loaderSmtpVerify = false;
					Toast.showToast(response.data.error_message);
				}
			});
		}
                
        $scope.verifyLicense = function(){
                        
			if(angular.isUndefined($scope.settings.unify_authentication_key) || 
                                $scope.settings.unify_authentication_key == '') {

				var message = "Please provide the Unify Authentication Key below then try to verify.";
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
                    controller: 'SettingsExtra'
                }));
				return;
			}

            $scope.loaderVerifyLK = true;
            $scope.verify_status = false;
            var data = {};
			data.license_key = $scope.settings.license_key;
            data.unify_authentication_key = $scope.settings.unify_authentication_key;
            $http.post('../' + REST_API_PATH + 'settings/validate-license', data)
				.success(function (response) {
                    $scope.loaderVerifyLK = false;
                    $scope.verify_status = true;
					if(response){
                        $scope.verify_status_text = $sce.trustAsHtml('<i class="success material-icons">done</i> Verified');
                        $scope.color_code = '#47D04C';
                        $scope.settings.lk_verify = true;
                        $scope.SettingsForm.$invalid = false;
                    }
                    else{
                        $scope.verify_status_text = $sce.trustAsHtml('<i class="error material-icons">warning</i> The given license key seems to be wrong, please verify it again.');
                        $scope.color_code = '#ff2500';
                        $scope.SettingsForm.$invalid = true;
                    }
				});
        }

		$scope.goNextSection = function(section) {
			
			$scope.section = section;
			$scope.$evalAsync(function() {
				var elem =document.getElementById(section);
				elem.scrollIntoView();
				$rootScope.issueSections = false;
			} );
		}

		$scope.prevIssue = function() {

			if ( !angular.isUndefined(Resolver.issueSections) && Resolver.issueSections.needAction) {
				if($scope.prevSection < $scope.currentSection) {
					$scope.nextSection = $scope.currentSection;
					$scope.currentSection = $scope.prevSection;
				} 
				$scope.prevSection = $scope.prevSection - 1;
				$scope.goNextSection(Resolver.issueSections.list[$scope.currentSection]);
			}
			
		}

		$scope.nextIssue = function() {

			if ( !angular.isUndefined(Resolver.issueSections) && Resolver.issueSections.needAction) {
				if($scope.nextSection > $scope.currentSection) {
					$scope.prevSection = $scope.currentSection;
					$scope.currentSection = $scope.nextSection;
				} 
	
				if(Resolver.issueSections.list.length > ( $scope.nextSection + 1 )) {
					$scope.nextSection = $scope.nextSection + 1;
				}
				else {
					$scope.nextSection = 0;
				}
	
				$scope.goNextSection(Resolver.issueSections.list[$scope.currentSection]);
			}
		}

		$scope.clearIssues = function() {
			$scope.prevSection = -1;
			$scope.currentSection = 0;
			$scope.nextSection = 0;
			$scope.showNextPrevBtn = false;
			Resolver.issueSections = null;
			$scope.section = '';
		}

		if ( !angular.isUndefined($rootScope.issueSections) && $rootScope.issueSections.needAction) {

			$scope.showNextPrevBtn = true;
			$scope.highlight = $rootScope.issueSections.highlight;
			$scope.goNextSection($rootScope.issueSections.list[$scope.currentSection]);

			if($rootScope.issueSections.list.length > 1) {
				$scope.nextSection = 1;
			}
		}
		else {
			$scope.showNextPrevBtn = false;
		}

		$scope.$on('$destroy',function(){
			
			$location.hash('');
		});
		
	})
	.service('Resolver', function() {

		this.scrollTo = function(eID) {

			// This scrolling function 
			// is from http://www.itnewb.com/tutorial/Creating-the-Smooth-Scroll-Effect-with-JavaScript
			
			var startY = currentYPosition();
			var stopY = elmYPosition(eID);
			var distance = stopY > startY ? stopY - startY : startY - stopY;
			if (distance < 100) {
				scrollTo(0, stopY); return;
			}
			var speed = Math.round(distance / 100);
			if (speed >= 20) speed = 20;
			var step = Math.round(distance / 25);
			var leapY = stopY > startY ? startY + step : startY - step;
			var timer = 0;
			if (stopY > startY) {
				for ( var i=startY; i<stopY; i+=step ) {
					setTimeout("window.scrollTo(0, "+leapY+")", timer * speed);
					leapY += step; if (leapY > stopY) leapY = stopY; timer++;
				} return;
			}
			for ( var i=startY; i>stopY; i-=step ) {
				setTimeout("window.scrollTo(0, "+leapY+")", timer * speed);
				leapY -= step; if (leapY < stopY) leapY = stopY; timer++;
			}
			
			function currentYPosition() {
				// Firefox, Chrome, Opera, Safari
				if (self.pageYOffset) return self.pageYOffset;
				// Internet Explorer 6 - standards mode
				if (document.documentElement && document.documentElement.scrollTop)
					return document.documentElement.scrollTop;
				// Internet Explorer 6, 7 and 8
				if (document.body.scrollTop) return document.body.scrollTop;
				return 0;
			}
			
			function elmYPosition(eID) {
				var elm = document.getElementById(eID);
				var y = elm.offsetTop;
				var node = elm;
				while (node.offsetParent && node.offsetParent != document.body) {
					node = node.offsetParent;
					y += node.offsetTop;
				} return y;
			}
	
		};
	});