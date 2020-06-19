angular.module('codeBaseAdminApp')
    .controller('jsminifier', function ($scope, $templateCache, $compile, $http, $location, $mdDialog, Dialog, $rootScope, Toast) {

        $scope.$parent.validation = {
            custom_scripts: {
                status: true,
                message: ''
            }
        };
       
        $scope.allowed_test_cards_separator = '\n';
    });