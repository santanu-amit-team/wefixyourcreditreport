'use strict';
/**
 * @ngdoc directive
 * @name codeBaseAdminApp.directive:addRemove
 * @description
 * # addRemove
 */
angular.module('codeBaseAdminApp')
	.directive('addRemoveInput', function ($http, $routeParams, Dialog, Toast, $filter) {
		return {
			templateUrl: 'views/directive-templates/add-remove-input.html',
			restrict: 'E',
			link: function postLink(scope, element, attrs) {
				scope.inputPlaceholder = attrs.inputPlaceholder;
				scope.addedArr = [];
				scope.selectedArr = [];
				scope.theme = (!attrs.hasOwnProperty('theme') ? 1 : attrs.theme);
				scope.classes = (!attrs.hasOwnProperty('classes') ? '' : attrs.classes);
				scope.inputType = (!attrs.hasOwnProperty('inputType') ? 'text' : attrs.inputType);
				scope.inputData = (!attrs.hasOwnProperty('inputData') ? {} : JSON.parse(attrs.inputData));

				if(scope.theme == 2){
					scope.addInputLabel = attrs.addInputLabel;
					scope.addedInputLabel = attrs.addedInputLabel;
				}

				scope.splitString = function(string, by){
					var array = [];

					if(string){
						if(angular.isNumber(string)){
							string = string.toString();
						}
						array = string.split(by);
					}
					return array;
				}

				scope.prepareAddedList = function(){
					if(scope.inputType == 'select'){
						angular.forEach(scope.addedArr, function (value, index) {
							angular.forEach(scope.inputData.options, function (option, key) {
								if(option[scope.inputData.key] == value){
									var data = {id: option[scope.inputData.key], label: option[scope.inputData.label]};
									scope.selectedArr[index] = data; 
								}
							});
						});

					}
					// console.log(scope.selectedArr);
				}
				var scopeObjMod = attrs.scopeObj + '.' + attrs.scopeUpdateKey;
			
				scope.$watch(scopeObjMod, function(newValue) {
				    scope.addedArr = scope.splitString(scope[attrs.scopeObj][attrs.scopeUpdateKey], attrs.separator);
					scope.addedInput = scope.addedArr;
					scope.prepareAddedList();
					// scope.validationUpdate(true, '', attrs.scopeUpdateKey);
				});

				// if(scope[attrs.scopeObj][attrs.scopeUpdateKey] && scope[attrs.scopeObj][attrs.scopeUpdateKey] != ''){
				// 	scope.addedArr = scope.splitString(scope[attrs.scopeObj][attrs.scopeUpdateKey], attrs.separator);
				// 	scope.addedInput = scope.addedArr;
				// 	// console.log(scope.addedInput);
				// 	scope.prepareAddedList();
				// }

				/*
				 * Validation methods 
				 */
				scope.validationUpdate = function(validationStatus, msg, key){
					if ( typeof scope.$parent[attrs.validationKey] !== 'undefined' ) {
						scope.$parent[attrs.validationKey][key].status = validationStatus;
						scope.$parent[attrs.validationKey][key].message = msg;
						scope.$parent[attrs.validationKey][key].field = key;
					}
					
					return true;
					
				}
				scope.duplicateValue = function(value, skipIndex, event){
					var arrayToCheck = [];
					arrayToCheck = angular.copy(scope.addedArr);

					if(skipIndex !== ''){
						delete arrayToCheck[skipIndex];
					}
					if(Object.keys(arrayToCheck).length > 0){
						if(arrayToCheck.indexOf(value) != -1){
							Toast.showToast(attrs.duplicateMsg);
							if(event != 'add'){
								scope.validationUpdate(false, attrs.duplicateMsg, attrs.scopeUpdateKey);
							}
							return true;
						}
					}

					scope.validationUpdate(true, '', attrs.scopeUpdateKey);

					return false;
				}
				scope.emptyValue = function(value){
					var emptyMsg = '', validate = true;

					if(!value){
						emptyMsg = 'Please check empty value';
						validate = false;
						Toast.showToast(emptyMsg);
					}
					scope.validationUpdate(validate, emptyMsg, attrs.scopeUpdateKey);
				}


				/*
				 * New data add and update to parent scope
				 */
				scope.addMethod = function(value){
					if(!value){
						Toast.showToast(attrs.emptyMsg);
						return false;
					}
					if(scope.duplicateValue(value, '', 'add') === true){
						return false;
					}
					if(scope.inputType == 'select' && value == 'all'){
						scope.addedArr = [value];
					}
					else{
						scope.addedArr.push(value);
					}
					scope.prepareAddedList();
					scope.addedInput = scope.addedArr;
					scope.addInput = '';
					scope.joinAndUpdate(scope.addedArr);
				}

				scope.updateSelectedList = function(index){
					var key = scope.selectedArr[index].id;
					scope.selectedArr.splice(index, 1);
					var addedArrIndex = '';
					angular.forEach(scope.addedArr, function (value, i) {
						if(value == key){
							addedArrIndex = i;
						}
					});
					scope.addedArr.splice(addedArrIndex, 1);
					scope.joinAndUpdate(scope.addedArr);
				}
				
				/*
				 *  Remove data and update to parent scope
				 */
				scope.remove = function(index){
					if(scope.inputType == 'select'){
						scope.updateSelectedList(index);
					}
					else{
						scope.addedArr.splice(index, 1);
						scope.joinAndUpdate(scope.addedArr);
					}
				}

				/*
				 * Edit existing data and update to parent scope
				 */
				scope.edit = function(index){
					if(scope.duplicateValue(scope.addedInput[index], index, 'edit') === true){
						return false;
					}
					scope.emptyValue(scope.addedInput[index]);
					scope.joinAndUpdate(scope.addedInput);
				}

				/*
				 * Update to parent scope
				 */
				scope.joinAndUpdate = function(array){
					scope[attrs.scopeObj][attrs.scopeUpdateKey] = array.join(attrs.separator);
					console.log(scope[attrs.scopeObj][attrs.scopeUpdateKey]);
				}
			}
		};
	});
