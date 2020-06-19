'use strict';
/**
 * @ngdoc directive
 * @name codeBaseAdminApp.directive:inputField
 * @description
 * # extensionInputField
 */
angular.module('codeBaseAdminApp').directive('extensionInputField', function() {
    return {
        templateUrl: 'views/extension-input-field.html',
        restrict: 'E',
        link: function postLink(scope, element, attrs) {
            scope.attrType = attrs.type;
            scope.fieldName = attrs.key;
            scope.required = false;
            scope.textarea = false;
            scope.required = (attrs.optional != '') ? false : true;
            scope.textarea = (attrs.textarea != '') ? true : false;
            //new extension data default value set except input type 'enum' & 'multi_select'
            if (attrs.flag != 'Edited') {
                if (attrs.type != 'enum' && attrs.type != 'multi_select' && attrs.value !== null) {
                        scope.extension[attrs.key] = JSON.parse(attrs.value);
                }
            }
            //set select options for 'emun' & 'multi_select'
            if (attrs.type == 'enum' || attrs.type == 'multi_select') {
                scope.fieldValue = JSON.parse(attrs.value);
            }
            scope.label = attrs.label;
            scope.hint = attrs.hint;
        }
    };
});