'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Diagnosis
 * @description
 * # Diagnosis
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
        .controller('Diagnosis', function ($scope, $templateCache, $compile, $http) {

            $scope.diagnosisSection = ['framework-directory', 'backup-directory', 'debug-contents', 'tag-checker', 'debug-cache', 'extensions', 'settings', 'configurations', 'pixels', 'api-endpoint'];
            $scope.lists = {};
            $scope.listObjKeys = [];
            $scope.loader_circle = false;
            $scope.startDiagnosis = function ()
            {

                angular.forEach($scope.diagnosisSection, function (value, index) {
                    $scope.loader_circle = true;
                    $http.post('../' + REST_API_PATH + 'diagnosis/' + value + '/')
                            .then(function (response) {
                                if (response.data.success)
                                {
                                    $scope.loader_circle = false;
                                    $scope.lists[value] = response.data.data;
                                    $scope.listObjKeys = Object.keys($scope.lists);
                                }
                            });
                });
            }

        });
