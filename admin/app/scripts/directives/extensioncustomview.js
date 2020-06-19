'use strict';
/**
 * @ngdoc directive
 * @name codeBaseAdminApp.directive:inputField
 * @description
 * # extensionInputField
 */
angular.module('codeBaseAdminApp').directive('extensionCustomView', function($sce, $compile, $http) {
    return {
        templateUrl: 'views/directive-templates/extensions-custom-view.html',
        restrict: 'E',
        link: function postLink(scope, element, attrs) {
            
            $http.post('../' + REST_API_PATH + 'configurations/all/').success(function (response) {
                    scope.configurations = response.data;
            });
            
            $http.post('../' + REST_API_PATH + 'campaigns/all/').success(function(response) {
                scope.campaign_lists = response.data;
            });
            
            scope.countryListReady = false;
            $http.get('../../assets/storage/country_states.json')
			.then(function (response) {
				scope.countryList = [];
				angular.forEach(response.data, function (value, key) {
					if (value.FIELD1.length > 0 && value.FIELD2.length > 0) {
						scope.countryList.push({
							country: value.FIELD1,
							iso: value.FIELD2
                        });
                        scope.countryListReady = true;
					}
				});
			});
            
            scope.cardTypes = [
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
                    ['DIRECTDEBIT', 'DirectDebit']
            ];
            
            element.html(scope.$parent.customHtml).show();
            $compile(element.contents())(scope);
        }
    };
    
    
});

