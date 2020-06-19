'use strict';

/**
 * @ngdoc service
 * @name codeBaseAdminApp.menu
 * @description
 * # menu
 * Factory in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp')
	.factory('Menu', function ($rootScope, $timeout) {
        var service = {};
        var service= {
            path: [],
            found: false
        }

        service.getPath = function(obj, value) {
        
            function search(haystack) {
                
                if(typeof haystack == 'string') {
                    
                    if( haystack == value) {
                        service.found = true;
                    }
                    else {
                        service.path.pop();
                    }
                    
                }

                if(typeof haystack == 'boolean') {
                    service.path.pop();

                }

                if(typeof haystack == 'object') {
                    angular.forEach(haystack, function(element, index){
                        
                        if(!service.found){
                            service.path.push(index);
                            search(element);
                        }

                        if(service.found) return;

                        if(Number.isInteger(index)) {
                            service.path.pop();
                        }
                    });   
                }
            }
            search(obj);
            //return service.path;
            return service.found ? service.path : false;
            /*
            Or alternately if you want to keep mixed return
            return found ? path : false;
            */
        }
		service.name = function(value, status){
           var path = service.getPath($rootScope.submenu, value);
           if(path) {
                $rootScope.submenu[path[path.length - 4]][path[path.length - 3]][path[path.length - 2]].show = status;
           }
           service.path = [];
           service.found = false;

        };
		return service;
	});