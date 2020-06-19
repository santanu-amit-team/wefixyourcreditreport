'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:MidRoutingManager
 * @description
 * # MidRoutingManager
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('CheckRoutingExtensionsDialog', function ($scope, $mdDialog, $location) {
        // $scope.closeDialog = function () {
        //     $mdDialog.hide();
        //     $location.path('/extensions');

        // };
}).controller('MidRoutingManager', function($scope, Toast, Dialog, $http, $location, $window, $routeParams, $filter, $rootScope, $mdDialog, $route) {
        
        
    $http.post('../restapi.php/configurations/all/').success(function(response) {
        $scope.configList = response.data;
    });

    var file, config_id, source, destination, ext, size, urldata, split, exclude;
    $scope.columnshow = false;
    
    $scope.downloadFileName = 'sampleMidRouting.csv';
    $scope.csvPath = "../../extensions/MidsRouting/" + $scope.downloadFileName;

    $scope.uploadBtn = {
        upload: 'Upload',
    };

    $scope.saveBtn = {
        save: 'Save',
    };

    $scope.midRoute = {};

    $scope.geo_types = [ 'state' ];

    $scope.urlPatternTypes = [
        {
            key: '1',
            val: 'Legacy type with sub directories (domain.com/subfolder1/subfolder2/?AFFID=affiliateID)'
        },
        {
            key: '2',
            val: 'Legacy type with no sub directories (domain.com?AFFID=affiliateID)'
        },
        {
            key: '3',
            val: 'Custom type with sub directories (domain.com/subfolder1/subfolder2/affiliateID)'
        },
        {
            key: '4',
            val: 'Custom type with no sub directories (domain.com/affiliateID)'
        }
    ];
    
    $scope.split = [
                {
                    key: 'yes',
                    val: 'Yes'
                },
                {
                    key: 'no',
                    val: 'No'
                }
            ];
    

    $scope.lists = [];
    $scope.addFile = function() {
        file = $scope.file;
        config_id = $scope.config_id;
        source = $scope.source;
        destination = $scope.destination;
        urldata = $scope.urldata;
        split = $scope.split;
        exclude = $scope.exclude;
        
        if (typeof(file) == 'undefined') {
            return false;
        }
        // if (typeof(config_id) == 'undefined' || config_id == '') {
        //     Toast.showToast("Please select configuration first");
        //     return false;
        // }
        ext = (file[0].name).split('.').pop();
        if (ext !== 'csv') {
            Toast.showToast("Please select csv file only");
            return false;
        }
        
        if (!$scope.columnshow) {
            $scope.uploadBtn = {
                upload: 'Uploading...',
            }
            $scope.disabledUpload = {
                upload: true,
            };
            $scope.coloumMenuTriggered = true;
            var url = '../' + REST_API_PATH + 'midrouting/csv-col-count/';
            var fd = new FormData();
            fd.append("file", file[0]);
            $http.post(url, fd, {
                withCredentials: false,
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: angular.identity
            }).then(function(response) {
                if (response.data.success == false) {
                    $scope.disabledUpload = {
                        upload: false,
                    };
                    Toast.showToast(response.data.error_message);
                    return false;
                }
                $scope.uploadBtn = {
                    upload: 'Uploaded'
                }
                $scope.col = response.data.data;
                $scope.columnshow = true;
                $scope.csvUploadBtn = true;
                // console.log($scope.col);
            }, function(response) {
                console.log(response);
            });
        } 
        else {
            
            var url = '../' + REST_API_PATH + 'midrouting/csv-upload/';
            if ($routeParams.id) {
                url += $routeParams.id;
            }
            if (typeof(source) === 'undefined') {
                Toast.showToast("Please select source column");
                return false;
            }
            if (typeof(destination) === 'undefined') {
                Toast.showToast("Please select destination column");
                return false;
            }
            $scope.saveBtn = {
                save: 'Saving...'
            };
            $scope.disabledSave = {
                save: true
            };
            var fd = new FormData();
            fd.append("file", file[0]);
            fd.append("config_id", config_id);
            fd.append("source", source);
            fd.append("destination", destination);
            fd.append("urldata", urldata);
            fd.append("split", split);
            fd.append("exclude", exclude);
            $http.post(url, fd, {
                withCredentials: false,
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: angular.identity
            }).then(function(response) {
                $scope.gateway = response;
                $scope.columnshow = false;
                $scope.coloumMenuTriggered = false;
                if ($scope.gateway == '') {
                    $scope.norecord = true;
                }
                //$route.reload();
                Toast.showToast("Gateway List uploaded successfully");
                $scope.profile_id = response.data.data.data[0].profile_id;
                $scope.getData($scope.profile_id );
            }, function(response) {
                console.log(response);
            });
            $scope.coloumMenuTriggered = false;
        }
    }

    $scope.changedValue = function(element, type) {
        if (type == 'source') {

            $scope.source = element;
        }
        else if (type == 'urldata') {

            $scope.urldata = element;
        }
        else if (type == 'split') {

            $scope.split = element;
        }
        else if(type == 'exclude') {

            $scope.exclude = element;
        } 
        else {
            $scope.destination = element;
        }
    };

    
    $scope.checkExtensions = function () {
        $http.get('../' + REST_API_PATH + 'midrouting/check-extensions').then(function (response) {
            if (!response.data.extensionMidRoutingActive) {
                var message = 'Download <strong>Mid Routing</strong> extension' +
                        ' and activate it.';
                $mdDialog.show($mdDialog.alert({
                    template: ' <md-dialog>' +
                            '	<md-dialog-content>' +
                            '	' + '<p style="padding: 15px; padding-top: 30px; text-align: center;">' + message + '</p>' +
                            '  	</md-dialog-content>' +
                            '  	<md-dialog-actions>' +
                            '   	<md-button ng-click="closeDialog()" class="md-primary">' +
                            '      		Ok' +
                            '   	</md-button>' +
                            '  	</md-dialog-actions>' +
                            '</md-dialog>',
                    controller: 'CheckRoutingExtensionsDialog'
                }));
            }

        });
    };
    
   // $scope.checkExtensions();

    if ($routeParams.id) {
            $http.post('../' + REST_API_PATH + 'midrouting/get-mid/' + $routeParams.id)
                    .then(function (response) {
                            if (response.data.success) {
            $scope.routing = response.data.data;
            $scope.routing.geoLocation = $scope.routing.geoLocation.length ? JSON.parse($scope.routing.geoLocation) : $scope.defaultKeys.geoLocation();
            $scope.midRoute = angular.copy($scope.routing);
            $scope.getData($routeParams.id);
                            }
                    });
    }
                
    $scope.midRoute.geoLocation = [];            

    $scope.SubmitForm = function () {
        var url = '../' + REST_API_PATH + 'midrouting/save-mid/';
        
        if ($routeParams.id) {
            url = '../' + REST_API_PATH + 'midrouting/edit-mid/' + $routeParams.id;
        }
        var data = angular.copy($scope.midRoute);
        data.geoLocation = JSON.stringify($scope.midRoute.geoLocation);
        
    	$http.post(url, data)
    	.then(function (response) {
    		if(response.data.success === true){
                Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
                $location.path('/routing/');
            } 
            else {
                Toast.showToast(response.data.error_message);
                $location.path('/routing/');
            }
    	});

    };

    $scope.updateRow = function(obj) {

        var url = '../' + REST_API_PATH + 'midrouting/create-or-edit/';

        if($routeParams.id) url += $routeParams.id;

        $http.post(url, obj).then(function(response) {

            if(response.data.success) {
                
                Toast.showToast('New Gateway has been added successfully');
                $scope.getData( response.data.profile_id);
            }
            else {

                Toast.showToast(response.data.error_message);
            }
            //$scope.getData( 17, 'UpdateRow'); 
        });
    }

    $scope.getData = function(id) {
        id = id || $routeParams.id;

        // var params = {
        //     limit: $scope.limit,
        //     offset: ($scope.currentPage - 1) * $scope.limit
        // };
        // , params
        if (id) {
            $http.post('../' + REST_API_PATH + 'midrouting/get-data/' + id).then(function(response) {
                if (response.data.success === true) {
                    $scope.totalItems = response.data.totalData;
                    if($scope.totalItems > 0){
                        $scope.paginationShow = true;
                    }
                    else{
                        $scope.paginationShow = false;
                    }
                    $scope.pages = Math.ceil($scope.totalItems / $scope.limit);
                    angular.copy(response.data.data, $scope.lists);
                } else {
                    Toast.showToast(response.data.error_message);
                }
            });
        }
        
    }

    $scope.addGateway = function() {
        
        $scope.last_element = {
            config_id: "",
            created_at: "",
            destination: "",
            exclude: "",
            id: 0,
            ip: "",
            modified_at: "",
            profile_id: "",
            source: "",
            split: "",
            urldata: ""
        };

        if($routeParams.id) {
            $scope.last_element.profile_id = $routeParams.id;
        }

        $http.get('../' + REST_API_PATH + 'midrouting/max-mid/').then(function(response) {

            if(response.data.success) {
                $scope.last_element.id = Number(response.data.data) + 1;
            }
            
        });
        
        $scope.lists.unshift($scope.last_element);
    }

    $scope.clearAll = function() {

        if($routeParams.id || !$scope.lists.length) {

            $location.path('/routing/');
            return;
        }

        var confirm = $mdDialog.confirm()
					.title('Warning')
					.textContent('Added gateway record will be removed. Are you sure to go back without saving MID Profile.')
					.ok('Yes')
                    .cancel('No');
        
        $mdDialog.show(confirm).then(function () {

            $http.post('../' + REST_API_PATH + 'midrouting/delete-all/' + $scope.profile_id)
            .then(function (response) {
                
                $location.path('/routing/');

            });
            
        });

    }
    
    $scope.deleteRouting = function(_id) {
        Dialog.showDialog('../' + REST_API_PATH + 'midrouting/delete-data/' + _id);
    }

    $scope.clearAndGoback = function() {
        
        $scope.clearAll();
    }
    
    $scope.defaultKeys = {
        'geoLocation': function () {
            return   {
                'state': '',
                'geo_gateways': '',
                'step_id':'',
                'bin_category':''
            }
        }
    };

    for (var key in $scope.defaultKeys) {
        if (!$scope.midRoute.hasOwnProperty(key) || !$scope.midRoute[key].length > 0) {
            $scope.midRoute[key] = [];
            $scope.midRoute[key].push($scope.defaultKeys[key]());
        }
    }


    $scope.add = function (multiKeyIndex) {

        $scope.midRoute[multiKeyIndex].push($scope.defaultKeys[multiKeyIndex]());

    }
    
    $scope.remove = function (index, multiKeyIndex) {

        $scope.midRoute[multiKeyIndex].splice(index, 1);

    };
    

}).directive('inlineEditor', function () {
   
    return {
      restrict: 'E',
      scope: {
        changeFn: '&change',
        valueText: '@value',
        row_id: "=row",
        info: "=info",
        mode: "=mode",
        deleteFn: '&delete'
      },
      controller: function ($scope, Toast) {

        $scope.editing = false;

       
        $scope.switchToEditMode = function () {
          $scope.newValue = $scope.info;
          $scope.editing = true;
        };

        if($scope.mode) {
            $scope.switchToEditMode();
        }
        
        $scope.switchToNormalMode = function () {

          if(angular.isUndefined($scope.newValue.source) || $scope.newValue.source === '') {
              Toast.showToast('Source gateway is required.');
              return;
          }

          if(angular.isUndefined($scope.newValue.destination) || $scope.newValue.destination === '' ) {
                Toast.showToast('Destination gateway is required.');
                return;
          }

          if(angular.isUndefined($scope.newValue.urldata) || $scope.newValue.urldata === '') {
                Toast.showToast('Url is required.');
                return;
          }

          if(angular.isUndefined($scope.newValue.exclude) || $scope.newValue.exclude === '') {
                $scope.newValue.exclude = 'NA';
                return;
          }

          $scope.sendNewValue();
          $scope.newValue = '';
          $scope.editing = false;
          
        };
        
        $scope.sendNewValue = function () {
          $scope.editing = false;
          $scope.changeFn({ $obj: $scope.newValue });
        }

        $scope.callDelete = function(){
            $scope.deleteFn({ $id: $scope.info.id });
        }

      },
      templateUrl: 'views/directive-templates/row-inline-edit.html',
      
    }
});