'use strict';

/**
 * @ngdoc service
 * @name codeBaseAdminApp.crms
 * @description
 * # crms
 * Factory in the codeBaseAdminApp.
 */
angular.module('codeBaseAdminApp')
	.factory('crms', function ($rootScope, $timeout) {
		var service = {};
		var list = [
	        { key : 'limelight', label: 'Limelight'},
                { key : 'limelightv2', label: 'LimelightV2'},
	        { key : 'konnektive', label: 'Konnektive'},
	        { key : 'responsecrm', label: 'Response CRM'},
	        { key : 'velox', label: 'Velox'},
	        { key : 'emanage', label: 'Emanage'},
	        { key : 'infusionsoft', label: 'Infusionsoft'},
	        { key : 'sixcrm', label: 'Six CRM'},
	        { key : 'nmi', label: 'NMI'},
	        { key : 'm1billing', label: 'M1billing'},
	        { key : 'layer2', label: 'Layer2'},
                { key : 'vrio', label: 'VRIO'},
    	];

		service.crmsTypeList = function() {
		    return list;
		};

		service.getLabel = function(key) {
			var label = '';
			angular.forEach(list, function (crmData, index) {
				if(crmData.key === key){
					label = crmData.label;
					return false;
				}
			});
			return label;
		};
		return service;
	});