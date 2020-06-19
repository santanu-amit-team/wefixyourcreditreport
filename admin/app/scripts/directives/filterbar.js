'use strict';
/**
 * @ngdoc directive
 * @name codeBaseAdminApp.directive:filterBar
 * @description
 * # filterBar
 */
angular.module('codeBaseAdminApp')
	.directive('filterBar', function ($http, $routeParams, Dialog, Toast, $filter, $timeout) {
		return {
			templateUrl: 'views/directive-templates/filter-template.html',
			restrict: 'E',
			link: function (scope, element, attrs) {
				//Check Dynamic List change
				scope.$on('statusChanged', function(event, args){
					scope.typeSelect();
				});

				scope.$watch(function(newValue, oldValue) {
					if(!scope[attrs.list]){
						scope = scope.$parent;
					}
				});

				if(angular.isUndefined(scope[attrs.list])){
					scope[attrs.listKeys] = [];
					scope[attrs.list] = []; 
					scope[attrs.fullList] = [];
				}

				scope.filter = {};
				scope.actionOff = attrs.hasOwnProperty('actionOff') ? attrs.hasOwnProperty('actionOff') : false;
				scope.showCount = attrs.hasOwnProperty('showCount') ? attrs.showCount : true;
				scope.searchPlaceholder = attrs.hasOwnProperty('searchPlaceholder') ? attrs.searchPlaceholder : 'Search';
				scope.filter.type = 'all';
				scope.filter.search = '';
				scope.selectedType = '';

				if(!scope.filterActions){
					scope.filterActions = [{key : 'delete', val : 'Delete'}];
				}
				if(!scope.filterTypes){
					scope.filterTypes = [{key : 'all', val : 'All'}];
				}
				
				scope.$watch(`${attrs.list}`, function(newValue, oldValue) {
					if(angular.isUndefined(newValue)){
						return false;
					}
					if(scope.showCount === true){
			        	scope.filterTypes[0].val = (newValue.length > 0) ? `All (${newValue.length})` : `All`;
			        }
				});

				// scope.$watch(`${attrs.fullList}`, function(newValue, oldValue) {
				// 	console.log(newValue);
				// });

				/*
				 *select all
				 */
				scope.isChecked = function(){
					return (scope[attrs.listKeys].length > 0) ?
						scope[attrs.selectedRowsKey].length == scope[attrs.listKeys].length :
						false;
				}
					
				scope.toggleAll = function(){
					if (scope[attrs.selectedRowsKey].length == scope[attrs.listKeys].length) {
				    	scope[attrs.selectedRowsKey] = [];
				    	if(attrs.hasOwnProperty('selectedRowData')){
				    		scope[attrs.selectedRowData] = [];
				    	}
				    } else if (scope[attrs.selectedRowsKey].length == 0 || 
				    	scope[attrs.selectedRowsKey].length > 0) {
				   		scope[attrs.selectedRowsKey] = scope[attrs.listKeys].slice(0);
				   		if(attrs.hasOwnProperty('selectedRowData')){
				    		scope[attrs.selectedRowData] = angular.copy(scope[attrs.fullList]);
				    	}
				    }
				}

				/*
				 *Actions 
				 */
				scope.actionMethodCall = function(action){
					var details = scope.getActionDetails(action);
					if(scope[attrs.selectedRowsKey].length == 0){
						var msg = attrs.hasOwnProperty('noRowSelected') ? attrs.noRowSelected : 'Please select at least one row.';
						Toast.showToast(msg);
						scope.filter.action = '';
						return false;
					}
					scope[details.method](scope[attrs.selectedRowsKey], action);
					scope.filter.action = '';
					scope[attrs.selectedRowsKey] = [];
				}

				scope.getActionDetails = function(key){
					var actionD = {};
					angular.forEach(scope.filterActions, function (action, index) {
						if(action.key == key){
							actionD = action;
						}
					});
					return actionD;
				}

				/*
				 * Type
				 */
				scope.typeSelect = function(ev, value){
					let updateList = [];
					let fullList = scope[attrs.fullList];
					let type = 'string';
					let typeVal = scope.filter.type;
					let typeBy = attrs.typeBy;
					let serachVal = scope.filter.search;
					let typeArr = scope.searchItem('key', scope.filter.type, scope.filterTypes);
					let filterColumns = [];
					if(typeArr.hasOwnProperty('type')){
						type = typeArr.type;
						typeVal = typeArr.value;
						typeBy = typeArr.identifier;
					}

					filterColumns.push({key : typeBy, val : typeVal, type : type});

					if(serachVal){
						filterColumns.push({key : attrs.searchBy, val : serachVal, type : 'string'});
					}
					if(scope.filter.type == 'all'){
						updateList = fullList;	
					}
					else{
						updateList = $filter('filterByColumn')(fullList, filterColumns);
					}
					scope[attrs.list] = updateList;
				}

				scope.searchItem = function(key, value, list){
					var item = {};
					for (let k in list){
						if(list[k][key] == value){
							item = list[k];
						}
					}
					return item;
				}

				/*
				 * Search 
				 */
				scope.searchRow = function(ev){
					var updateList = [];
					var fullList = scope[attrs.fullList];
					var type = 'string';
					var typeVal = scope.filter.type;
					var typeBy = attrs.typeBy;
					var serachVal = scope.filter.search;
					var typeArr = scope.searchItem('key', scope.filter.type, scope.filterTypes);
					if(typeArr.hasOwnProperty('type')){
						type = typeArr.type;
						typeVal = typeArr.value;
						typeBy = typeArr.identifier;
					}
					var filterColumns = [
						{key : typeBy, val : (scope.filter.type == 'all' ? "" : typeVal), type : type}, 
						{key : attrs.searchBy, val : serachVal, type : 'string'}
					];
					if(scope.actionOff === true){
						var filterColumns = [ 
							{key : attrs.searchBy, val : serachVal, type : 'string'}
						];
					}
					updateList = $filter('filterByColumn')(fullList, filterColumns);
					scope[attrs.list] = updateList;
				}
			}
		};
	});
