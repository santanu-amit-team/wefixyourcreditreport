/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


angular.module('codeBaseAdminApp')
.controller('konnektiveUtil', function ($scope, $templateCache, $compile, Toast, $http, $location, $mdDialog, Dialog, $rootScope, Toast) {

    var defaultOptions = {
        note_for_order : true
    };

    $scope.extension.extra_source_keys = [ 'AFFID', 'AFID', 'SID' ];

    $scope.extension.custom_source_variable = [ 'C1', 'C2', 'C3', 'C4', 'C5' ];

    angular.forEach(defaultOptions, function(v, k) {
        if(typeof $scope.extension[k] === 'undefined')
        {
            $scope.$parent.extension[k] = v;
        }
    });
    
    
    
    $scope.getDefaultParams = function(){
        return {
            'default_param_key' : '',
            'default_param_value' : ''
        }
    }
    if (typeof $scope.extension.default_params == "undefined" || 
        !$scope.extension.default_params.length > 0) {
    
        $scope.extension.default_params = [];
        $scope.extension.default_params.push($scope.getDefaultParams());
    }
        $scope.addMethod = function(){
                $scope.extension.default_params.push($scope.getDefaultParams());
        }
        $scope.remove = function (index) {
            $scope.extension.default_params.splice(index, 1);
        }
});