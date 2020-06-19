'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:CronManager
 * @description
 * # CronManager
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('CronManager', function ($scope, $http, $routeParams, $location, Toast) {
        $scope.job = {};
        
        $scope.presets_list = [
            {
                'value' : '* * * * *',
                'title' : 'Once Per Minute (*****)'
            },
            {
                'value' : '*/5 * * * *',
                'title' : 'Once Per Five Minutes (*/5 * * * *)'
            },
            {
                'value' : '0,30 * * * *',
                'title' : 'Twice Per Hour (0,30 * * * *)'
            },
            {
                'value' : '0 * * * *',
                'title' : 'Once Per Hour (0 * * * *)'
            },
            {
                'value' : '0 0,12 * * *',
                'title' : 'Twice Per Day (0 0,12 * * *)'
            },
            {
                'value' : '0 0 * * *',
                'title' : 'Once Per Day (0 0 * * *)'
            },
            {
                'value' : '0 0 * * 0',
                'title' : 'Once Per Week (0 0 * * 0)'
            },
            {
                'value' : '0 0 1,15 * *',
                'title' : 'On the 1st and 15th of the Month (0 0 1,15 * *)'
            },
            {
                'value' : '0 0 1 * *',
                'title' : 'Once Per Month (0 0 1 * *)'
            },
            {
                'value' : '0 0 1 1 *',
                'title' : 'Once Per Year (0 0 1 1 *)'
            }
        ]
        
        $scope.fillPresetValue = function(){

            var cron_values = $scope.job.preset.split(' ');

            // assign the value in proper model,
            $scope.job.every = cron_values;
        }
        
        $scope.SubmitForm = function () {
           
            $http.post('../' + REST_API_PATH + 'cron/validate-handler/' + $scope.job.handler)
            .then(function (response) {
                if (!response.data.success) {
                    $scope.CronForm.$submitted = false;
                    $scope.shouldProceed = false;
                    Toast.showToast(response.data.error_message);
                }
                else {
                    var data = {
                        'handler' : $scope.job.handler,
                        'every' : $scope.job.every.join(' '),
                    }
                    
                    var url = '../' + REST_API_PATH + 'cron/add';
                    if ($routeParams.id) {
                        url = '../' + REST_API_PATH + 'cron/edit/' + $routeParams.id;
                    }
                    $http.post(url, data)
                        .then(function (response) {
                            if (!response.data.success) {
                                $scope.CronForm.$submitted = false;
                                Toast.showToast(response.data.error_message);
                            } else {
                                $location.path('/crons');
                                Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
                            }
                        });
                }
            });
		}

        if ($routeParams.id) {

			$http.post('../' + REST_API_PATH + 'cron/get/' + $routeParams.id)
				.then(function (response) {
					if (response.data.success) {
						$scope.job = response.data.data;
						$scope.job.every = $scope.job.every.split(' ');
					}

				});
		}

        
	});
