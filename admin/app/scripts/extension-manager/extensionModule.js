/*!
 * @module angularjs-toast
 * @description A Simple toast notification service for AngularJS pages
 * @version v2.0.3
 * @link https://github.com/sibiraj-s/angularjs-toast#readme
 * @licence MIT License, https://opensource.org/licenses/MIT
 */
(function() {
    'use strict';
    angular.module('extensionManagerModule', ['ngAnimate', 'ngSanitize', 'ngMaterial'])
	.factory('extensionManager', ['$rootScope', '$http', '$templateCache', '$compile', '$timeout', '$mdDialog', '$location', 
		'$filter', '$interval', '$q', '$anchorScroll', function($rootScope, $http, $templateCache, compile, $timeout, $mdDialog, $location, $filter, 
			$interval, $q, $anchorScroll) {

		var scope, templateBase, managerTemplateBase, uninstallList, installList, actionUrl, actiontype;
		/*create a new scope which has the codeBaseAdminApp's rootscope as the parent scope*/
		scope = $rootScope.$new();
		scope.extensionList = [];
		scope.selectedExtensionsData = [];
		scope.selectedExtensions = [];
		scope.listIds = [];
		scope.confirmationStep = true;
		scope.actionStep = false;
		scope.removedExtension = [];
		//Change button text by steps
		scope.submitBtnText = {};
		scope.action = '';
		scope.actionStates = {type : 'uninstall', status : 'pending', extension : {}}; // status : pending/start/complete
		scope.actionResponse = {};
		const API_URLS = {
			'uninstall' : `../${REST_API_PATH}extensions/uninstall-extension/`,
			'install'	: `../${REST_API_PATH}extensions/download-extension/`,
			'update'	: `../${REST_API_PATH}extensions/download-extension/`
		};
		scope.postData = {};
		const INPROGRESS_BTN_TEXT = {
			'uninstall' : `Uninstalling`,
			'install'	: `Installing`,
			'update' 	: `Updating`
		};
		scope.managerText = {
			'uninstall' : `uninstaller`,
			'install'	: `installer`,
			'update' 	: `updater`
		};
		scope.inprogress_text = INPROGRESS_BTN_TEXT;


		managerTemplateBase = './scripts/extension-manager/templates/extensionManager.html';

		function managerWindow () {
			return new Promise((resolve) => {
				$mdDialog.show({
		            controller: managerWindowController,
		            templateUrl: managerTemplateBase,
		            parent: angular.element(document.body),
		            scope: scope,
		            clickOutsideToClose: false,
		            preserveScope: true
	        	}).finally(function() {
	        		resolve(scope.actionResponse);
		        });
			});
				
		}

		function managerWindowController (scope, $mdDialog) {
			scope.determinateValue = {};
			scope.progressStatus = {};
			scope.confirmationStep = true;
			scope.actionStep = false;
			scope.actionInProgress = false;
			var actionStart = false;
			var progressInterval = {};
			
			/*
			 * Cancle the action and close modal
			 */
			scope.cancel = function () {
				$mdDialog.hide();
			};

			/*
			 * select or deselect all
			 */
			scope.isChecked = function (){
				return (scope.listIds.length > 0) ?
					scope.selectedExtensions.length == scope.listIds.length :
					false;
			};

			scope.toggleAll = function (){
				if (scope.selectedExtensions.length == scope.listIds.length) {
					scope.removedExtension = angular.copy(scope.listIds);
			    	scope.selectedExtensions = [];
			    } else if (scope.selectedExtensions.length == 0 || 
			    	scope.selectedExtensions.length > 0) {
			   		scope.selectedExtensions = angular.copy(scope.listIds);
			   		scope.removedExtension = [];
			    }
			    
			};

			scope.exists = function (item) {
           		return scope.selectedExtensions.indexOf(item) > -1;
        	};

        	scope.toggle = function (item, extension) {
	            let idx = scope.selectedExtensions.indexOf(item);
	            if (idx > -1) { 
	                scope.selectedExtensions.splice(idx, 1);
	                scope.removedExtension.push(item);
	            }
	            else { 
	                if(scope.selectedExtensions.length == 0){
	                    scope.selectedData = [];
	                }
	                if(scope.removedExtension.indexOf(item) !== -1){
	                	scope.removedExtension.splice(scope.removedExtension.indexOf(item), 1);
	                }
	                scope.selectedExtensions.push(item);
	            }
	            
	        };

	        /*
			 * Step validation
			 */
	        scope.validattion = function () {
        		let validation = true;
	        	if(scope.confirmationStep && scope.selectedExtensions.length == 0){
	        		validation = false;
	        	}
	        	return validation;
	        };

	        /*
			 * Actions
			 */
	        scope.proceed = function () {
        		let updateSelectedData = [];
        		//remove unchecked extension
	        	for (let key in scope.selectedExtensionsData){
	        		if(scope.removedExtension.indexOf(scope.selectedExtensionsData[key].extension_slug) === -1){
	        			updateSelectedData.push(scope.selectedExtensionsData[key]);
	        			scope.determinateValue[scope.selectedExtensionsData[key].extension_slug] = 0;
	        			scope.progressStatus[scope.selectedExtensionsData[key].extension_slug] = 'Pending'
	        		}
	        	}
	        	scope.selectedExtensionsData = updateSelectedData;
	        	scope.confirmationStep = false;
	        	scope.actionStep = true;
	        };

	        /*progress bar iterval*/
	        scope.progressTimer = function (){
	        	let extension = {};
	        	progressInterval = $interval(function() {

	        		// console.log('******************** Running Interval *****************');

					if(actionStart === true){
						extension = scope.actionStates.extension; 

						// console.log('******************** Running Progress bar *****************');

						scope.determinateValue[extension.extension_slug] += 1;
						scope.progressStatus[extension.extension_slug] = angular.copy(scope.determinateValue[extension.extension_slug])+'%';
					    if(scope.determinateValue[extension.extension_slug] >= 90){
					    	scope.actionStates.status = 'complete';
					    	$interval.cancel(progressInterval);
					    	progressInterval = undefined;
			    		}
					}	    
			    },1);
	        }
	         /*action API call*/
	        scope.httpCall = function (extension_slug){
	        	return new Promise((resolve, reject) => {
        			setTimeout(function(){
        			 	$http.post(
        			 		API_URLS[scope.action] + extension_slug,
        			 		scope.postData
        			 	)
	        			.then(function(response) {
	        					if(response.status == 200 && response.data.success){
	        						resolve(response);
	        					}else{
	        						reject(response);	
	        					}
	        				},function(error){ reject(error); });
        			}, 500);
        		});
	        }

	        scope.httpResponse = function (extension, i){
	        	return new Promise((resolve, reject) => {
	        	scope.httpCall(extension.extension_slug).then(
		        		function(response){
		        			scope.$apply(function() {
			        			scope.determinateValue[extension.extension_slug] = 100;
			        			scope.progressStatus[extension.extension_slug] = 'Completed';
				        		scope.actionResponse.success.ids.push(extension.extension_slug);
				        		scope.actionResponse.success.raw_responses[extension.extension_slug] = response;
		        			});
			        		resolve(1);
		        		},
		        		function(error){
		        			$interval.cancel(progressInterval);
		        			angular.element(document.querySelector(`#progress_text_${extension.extension_slug}`))[0].
		        			classList.add('warn');
		        			scope.$apply(function() {
		        				scope.determinateValue[extension.extension_slug] = 0;
			        			scope.progressStatus[extension.extension_slug] = 'Error';
			        			scope.actionResponse.error.ids.push(extension.extension_slug);
			        			scope.actionResponse.error.raw_responses[extension.extension_slug] = error;
		        			});
		        			resolve(1);
		        		}
		        	);
	        	});
	        }

	        scope.performAction = async function (){
	        	scope.actionInProgress = true;
	        	scope.submitBtnText[2] = INPROGRESS_BTN_TEXT[scope.action] + '...';
	        	var headLiEl = document.getElementById('list_heading_li');
	        	var scrollH = headLiEl.offsetHeight;
	        	var i = 0;
	        	var scrolledH = 0;
	        	var ulH = document.getElementById('listul').offsetHeight;
	        	var scrollbarContentWrapper = document.querySelector('.simplebar-content-wrapper');
	        	scrollbarContentWrapper.scrollTop = 0;
	        	for (const extension of scope.selectedExtensionsData) {
	        		var li = document.getElementById(`extension_${extension.extension_slug}`);
					scope.progressTimer();
	        		scope.determinateValue[extension.extension_slug] = 0;
	        		actionStart = true;
	        		scope.actionStates.extension = extension;
	        		scope.actionStates.status = 'start';
	        		await scope.httpResponse(extension, i);
	        		/* Automatic scroll */
	        		scrollH += li.offsetHeight;
	        		if(scrollH > ulH){
						scrolledH += ulH;
						scrollbarContentWrapper.scrollTop = scrolledH - headLiEl.offsetHeight;
						scrollH = headLiEl.offsetHeight;
					}
	        		actionStart = false;
	        		scope.actionStates.extension = {};
	        		i++;
	        	}
	        	scope.$apply(function() {
		        	scope.actionStep = false;
		        	scope.actionInProgress = false;
	        	});
	        	if(angular.isDefined(progressInterval)){
	        		$interval.cancel(progressInterval);
	        	}
	        }
	        
		}
		/* controller end */

		function initialDataLoad (args, action) {
			scope.action = action;
			scope.actionStates.type = action;
			scope.submitBtnText = {
				1 : 'Proceed',
				2 :  $filter('ucfirst')(scope.action),
				3 : 'Done'
			};
			scope.postData = args.post_data;
			scope.extensionList = args.extension_list;
			scope.selectedExtensionsData = args.extension_list;
			scope.selectedExtensions = getExtensionIds();
			scope.listIds = angular.copy(scope.selectedExtensions);
			scope.removedExtension = [];
			scope.actionResponse = {success: {ids : [], raw_responses : {}}, error : {ids : [], raw_responses : {}}};

		}

		function getExtensionIds () {
			let listIds = [];
			for ( let key in scope.extensionList) {
				listIds.push(scope.extensionList[key].extension_slug);
			}
			return listIds;
		}
		
		return {

			/**
			 * Install one or multiple extension(s)
			 * Argument list : 
			 */
			installer: function ( args ) {
				return new Promise((resolve) => {
					initialDataLoad (args, 'install');
					managerWindow ().then(function(res){
						resolve(res);
					});
					
				});
			},

			/**
			 * Update one or multiple extension(s)
			 * Argument list : 
			 */
			updater: function ( args ) {
				return new Promise((resolve) => {
					initialDataLoad (args, 'update');
					managerWindow ().then(function(res){
						resolve(res);
					});
					
				});
			},

			/*
			 * Uninstall one or multiple extension(s)
			 * Argument list :
			 */
			uninstaller : function ( args ) {
				return new Promise((resolve) => {
					initialDataLoad (args, 'uninstall');
					managerWindow ().then(function(res){
						resolve(res);
					});
					
				});
			}
		}	

	}]);
}).call(this);