<?php

require_once '../library/bootstrap.php';
Bootstrap::initialize();

use Admin\Library\Auth;
use Application\Request;
use Application\Session;
use Application\Registry;
use Admin\Controller\UsersController;

if (Auth::isLoginAuthorized()) {

	header('location: ./app/');
	exit;
}

if ( Request::query()->get('token') ) {
	$url    = Registry::system('systemConstants.REMOTE_URL')
				. 'google/token_new.php?id='
				. Request::query()->get('token');

	$auth 	= \Application\Http::get($url);
	$auth 	= json_decode($auth);

	if ( !empty($auth->data->email) && $auth->user_type == 'developer') {
		Session::update( array(
			'googleEmail'  => $auth->data->email,
			'access_token' => Request::query()->get('token'),
			'userType'     => $auth->user_type,
		) );
		header('location: ./app/');
		exit;
	}
        
	if ( !empty($auth->data->email) && $auth->user_type == 'others') {

                $data = UsersController::getUserByEmailID($auth->data->email);
                $data = reset($data);
                if(!empty($data))
                {
                    $userType = $data['user_type'];
                    Session::update( array(
                            'googleEmail'  => $auth->data->email,
                            'access_token' => Request::query()->get('token'),
                            'userType'     => $data['user_type'],
                            'access_urls'  => array_slice($data, 4),
                    ) );
                    header('location: ./app/');
                    exit;
                }
	}

}

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Admin</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">
		<!-- Angular Material style sheet -->
		<link rel="stylesheet" href="app/bower_components/angular-material/angular-material.min.css">
		<link rel='stylesheet' href='app/bower_components/font-awesome/css/font-awesome.min.css'>
		<!-- endbower -->
		<!-- endbuild -->
		<!-- build:css(.tmp) styles/main.css -->
		<link rel="stylesheet" href="app/styles/material-icon.css">
		<link rel="stylesheet" href="app/styles/custom-style.css">
		<link rel="stylesheet" href="app/styles/new-style.css">

		<!--Font-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
			  rel="stylesheet">
		<style type="text/css">
			[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
				display: none !important;
			}
			
			.zoom-body {
				zoom: 90%;
				/*background: red;*/
			}
			.f1f1f1 {
				background: #f1f1f1;
			}
		</style>
		<script type="text/javascript">

			var loginObject = <?=
				json_encode(array(
				    'googleLoginUrl'   => Registry::system('systemConstants.REMOTE_URL')
				    	. 'google/login.php?redirect='
				    	. Request::getBaseUrl()
				    	. '/',

				    'loginpasswordurl' => Request::getBaseUrl() . '/app',
				))?>
		</script>
		<!-- endbuild -->
	</head>
	<body ng-app="codeBaseAdminLoginApp" ng-controller="LoginIndexCtrl" class="login-bg" ng-cloak>
		<!--[if lte IE 8]>
		  <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
		<![endif]-->

		<!---->
		<div layout="column" layout-align="center center" style="height: 100%;">

				
			<h1 class="md-headline welcome-heading">
				<span class="unify-icon-login">
					<img src="../assets/images/unify-icon-login.svg" alt="" />
				</span> Welcome to Unify Framework!
			</h1>

			<md-content class="f1f1f1" style="width: 450px; background: white" md-whiteframe="3">
				<div class="row-form-login">

					<form name="LoginForm" ng-submit="login()" novalidate="">

						<div class="strock-input full-width form-row-login">
								<input type="text" name="username" class="input-global" ng-model="username" placeholder="Username" required>
								<div ng-message="username" ng-show="error.username" class="error-msg">
										{{ msg }}
								</div>
						</div>
						<div class="strock-input full-width form-row-login">
								<input type="password" name="password" class="input-global" placeholder="Password" ng-model="password" required>
								<div ng-message="password" ng-show="error.password" class="error-msg">
										{{ msg }}
								</div>
						</div>
						
						<div flex="100" layout="row">
						<!-- ng-disabled="LoginForm.$invalid" -->
								<md-button type="submit"  class="md-raised md-primary login-btn">
									LOGIN
								</md-button>
						</div>

					</form>


				</div>
				<div class="row-form-login gray-cell">
						<button type="button" ng-click="loginWithGoogle()" class="g-sign-btn">
								<img src="../assets/images/google-icon.svg" alt="" />
								<span>SIGN IN WITH GOOGLE</span>
						</button>
				</div>
				<!-- <div layout="column" layout-align="center center"> -->

				<!-- <md-button ng-href="/app" aria-label="Login">Basic Login</md-button> -->

				<!-- <md-button ng-href="" aria-label="Login">Google Login</md-button> -->

				<!-- </div> -->
			</md-content>


		</div>

		<!-- build:js(.) scripts/vendor.js -->
		<!-- bower:js -->
		<script src="app/bower_components/angular/angular.min.js"></script>
		<script src="app/bower_components/angular-animate/angular-animate.min.js"></script>
		<!--angularjs-material-->
		<script src="app/bower_components/angular-material/angular-material.min.js"></script>
		<script src="app/bower_components/angular-aria/angular-aria.min.js"></script>
		<!-- endbower -->
		<script>
			angular.module('codeBaseAdminLoginApp', ['ngMaterial'])
				.config(function ($mdThemingProvider, $provide) {
				
					$mdThemingProvider.generateThemesOnDemand(true);
					$provide.value('themeProvider', $mdThemingProvider);
		

				}).run(function($rootScope, $location, themeProvider, $mdTheming) {

					$rootScope.theme = function (color, text_color) {
							if(typeof color == 'undefined'){
											return false;
							}
											var rgb = color;
											var text_rgb = text_color;
											rgb = rgb.replace(/[^\d,]/g, '').split(',');
											rgb = rgb.map(Number);
											text_rgb = text_rgb.replace(/[^\d,]/g, '').split(',');
											text_rgb = text_rgb.map(Number);
											//document.getElementById('custom-styles').innerHTML = '.md-date-range-picker__calendar__selected{ background:' + color + ' !important;}';
											var workingPrimary = {
									"50": {contrast: [0, 0, 0, 0.87], value: rgb},
									"100": {contrast: [0, 0, 0, 0.87], value: rgb},
									"200": {contrast: [0, 0, 0, 0.87], value: rgb},
									"300": {contrast: [0, 0, 0, 0.87], value: rgb},
									"400": {contrast: [0, 0, 0, 0.87], value: rgb},
									"500": {contrast: [255, 255, 255, 0.87], value: rgb},
									"600": {contrast: [255, 255, 255, 0.87], value: rgb},
									"700": {contrast: [255, 255, 255, 0.87], value: rgb},
									"800": {contrast: [255, 255, 255, 0.87], value: rgb},
									"900": {contrast: [255, 255, 255, 0.87], value: rgb},
									"A100": {contrast: [255, 255, 255, 0.87], value: rgb},
									"A200": {contrast: [255, 255, 255, 0.87], value: rgb},
									"A400": {contrast: [255, 255, 255, 0.87], value: rgb},
									"A700": {contrast: [255, 255, 255, 0.87], value: rgb}
											};
											var TextPrimary = {
									"50": {contrast: [0, 0, 0, 0.87], value: text_rgb},
									"100": {contrast: [0, 0, 0, 0.87], value: text_rgb},
									"200": {contrast: [0, 0, 0, 0.87], value: text_rgb},
									"300": {contrast: [0, 0, 0, 0.87], value: text_rgb},
									"400": {contrast: [0, 0, 0, 0.87], value: text_rgb},
									"500": {contrast: [255, 255, 255, 0.87], value: text_rgb},
									"600": {contrast: [255, 255, 255, 0.87], value: text_rgb},
									"700": {contrast: [255, 255, 255, 0.87], value: text_rgb},
									"800": {contrast: [255, 255, 255, 0.87], value: text_rgb},
									"900": {contrast: [255, 255, 255, 0.87], value: text_rgb},
									"A100": {contrast: [255, 255, 255, 0.87], value: text_rgb},
									"A200": {contrast: [255, 255, 255, 0.87], value: text_rgb},
									"A400": {contrast: [255, 255, 255, 0.87], value: text_rgb},
									"A700": {contrast: [255, 255, 255, 0.87], value: text_rgb}
											};
									themeProvider.definePalette('workingPrimary', workingPrimary);
									themeProvider.definePalette('TextPrimary', TextPrimary);
									themeProvider.theme('default')
									.primaryPalette('workingPrimary', {"default": "500"})
									.accentPalette('TextPrimary', {"default": "500"});
									$mdTheming.generateTheme('default');
					}
					$rootScope.theme('92,121,255', '92,121,255');

				}).controller('LoginIndexCtrl', function ($scope, $mdDialog, $http, $location) {
					$scope.pageTitle = 'Login';
					$scope.isChecking = false;
					$scope.loginWithGoogle = function () {
						location.href = loginObject.googleLoginUrl;
					}
					$scope.login = function() {
							$scope.isChecking = true;
							$scope.error = {
									username: false,
									password: false
							};
							var loginCredential = {
									username: $scope.username,
									password: $scope.password
							};
							$http.post('login.php', loginCredential)
									.then(function(response) {
											if (response.data.success === false) {
													$scope.error = {
															[response.data.field]: true
													};
													$scope.msg = response.data.message;
											} else if (response.data.success === true) {
													location.href = loginObject.loginpasswordurl;
											}
											$scope.isChecking = false;
									});
					}

					$scope.loginWithPassword = function() {

							$scope.showDialog = function($event) {
									var parentEl = angular.element(document.body);
									$mdDialog.show({
											title: 'Authentication Require',
											parent: parentEl,
											targetEvent: $event,
											templateUrl: 'app/views/login-dialog.html',
											locals: {
													username: $scope.username,
													password: $scope.password
											},
											controller: DialogController
									});

									function DialogController($scope, $mdDialog, $http, $location) {

											$scope.login = function() {
													$scope.error = {
															username: false,
															password: false
													};
													var loginCredential = {
															username: $scope.username,
															password: $scope.password
													};
													$http.post('login.php', loginCredential)
															.then(function(response) {
																	if (response.data.success === false) {
																			$scope.error = {
																					[response.data.field]: true
																			};
																			$scope.msg = response.data.message;
																	} else if (response.data.success === true) {
																			location.href = loginObject.loginpasswordurl;
																	}
															});
											}
											$scope.closeDialog = function() {
													$mdDialog.hide();
											}
									}
							}
							$scope.showDialog();
							// location.href = loginObject.loginpasswordurl;
					};

				});
		</script>
	</body>
</html>