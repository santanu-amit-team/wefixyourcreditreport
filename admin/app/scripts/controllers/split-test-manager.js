'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:Campaigns
 * @description
 * # Campaigns
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
    .controller('SplitTestManager', function ($scope, $templateCache, $compile, $routeParams, $http, $location, $mdDialog, Dialog, $rootScope,
        Toast, $filter, crms) {

        $scope.dataLoadComplete = true;
        $scope.splitTestTypes = [
            {
                'label': 'URL - A/B Testing',
                'value': 'url-ab-testing'
            },
            {
                'label': 'Template - A/B Testing',
                'value': 'template-ab-testing'
            },
            {
                'label': 'Configuration - A/B Testing',
                'value': 'configuration-ab-testing'
            },
            {
                'label': 'Content - A/B Testing',
                'value': 'content-ab-testing'
            },
        ];
//        $scope.splitTestType = {};
        $scope.defaultConfig = function () {
            return {
                'label': '',
                'url': '',
                'percentage': '',
                'template': '',
                'configuration': '',
                'step': '',
                'company_name': '',
                'image_title': '',
                'fileName': '',
                'email': '',
                'address': '',
                'phone': '',
                'encodedFile': ''
            };
        };
        $scope.splitTestData = {};
        $scope.splitTestData.splitTypesData = [];
        $scope.splitTestData.splitTypesData.push($scope.defaultConfig());
        $scope.filesArr = [];
        $scope.noOfExistingFile = 0;
        $scope.exisTingFiles = {};

        $scope.pageTypes = [
            'leadPage', 
            'checkoutPage', 
            'upsellPage1',
            'upsellPage2',
            'upsellPage3',
            'upsellPage4',
            'upsellPage5',
            'upsellPage6',
            'upsellPage7',
            'upsellPage8',
            'upsellPage9',
            'upsellPage10',
            'thankyouPage'
        ];

        /*add product*/

        $scope.add = function () {
            $scope.splitTestData.splitTypesData.push($scope.defaultConfig());
        }
        $scope.remove = function (index) {
            $scope.splitTestData.splitTypesData.splice(index, 1);
        }
        $http.post('../restapi.php/configurations/all/').success(function (response) {
            $scope.configList = response.data;

        });

        $scope.steps = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $scope.addFile = function () {
            var file = $scope.file;
            console.log(file);
        }

        $http.post('../' + REST_API_PATH + 'split-test/all/')
            .success(function (response) {
                $scope.splitTestList = response.data;
                if ($routeParams.id) {
                    for (var i = 0; i < $scope.splitTestList.length; i++) {
                        if ($scope.splitTestList[i].id == $routeParams.id) {
                            $scope.splitTestData = {};
                            $scope.splitTestData = $scope.splitTestList[i];
                            $scope.noOfExistingFile = JSON.parse($scope.splitTestData.splitTypesData).length;
                            $scope.splitTestData.splitTypesData = $scope.splitTestData.splitTypesData.length ? JSON.parse($scope.splitTestData.splitTypesData) : $scope.defaultConfig();
                            for (var fileData of $scope.splitTestData.splitTypesData) {
                                $scope.exisTingFiles[fileData.fileName] = true;
                            }
                            break;
                            
                        }
                    }
                }
            });

        $scope.SubmitForm = function () {

            $scope.totalPercentage = 0;

            $scope.splitTestData.splitTypesData.forEach(element => {
                $scope.totalPercentage += Number(element.percentage);
            });

            if( $scope.totalPercentage != 100) {
                var msg = $scope.totalPercentage > 100 ? 'Traffic Split (%) is greater than 100%.' : 'Traffic Split (%) is less than 100%.';
                Toast.showToast(msg + " Should be 100% in total.");
                $scope.SpllitForm.$submitted = false;
                return;
            }


            if ($scope.splitTestData.selectedSplitTestType == "content-ab-testing") {
                return $scope.addContentAbTesting();
            }

            var url = '../' + REST_API_PATH + 'split-test/add';
            if ($routeParams.id) {
                url = '../' + REST_API_PATH + 'split-test/edit/' + $routeParams.id;
            }
            var requestData = $scope.splitTestData;
            var data = JSON.parse(JSON.stringify(requestData));
            data.splitTypesData = JSON.stringify(requestData.splitTypesData);
            $http.post(url, data)
                .then(function (response) {
                    if (!response.data.success) {
                        $scope.SpllitForm.$submitted = false;
                        Toast.showToast(response.data.error_message);
                    } else {

                                $location.path('/split-test');
                                Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
                    }
                });

        };

        $scope.addContentAbTesting = function () {

            if ($scope.splitTestData.splitTypesData.length > $scope.filesArr.length + parseInt($scope.noOfExistingFile)) {

                Toast.showToast('Image required for all variants!');
                $scope.SpllitForm.$submitted = false;
                return;
            }

            var fd = new FormData();
            for (var key in  $scope.filesArr) {
                fd.append("file[]", $scope.filesArr[key]);
            }

            var requestData = $scope.splitTestData;
            var data = JSON.parse(JSON.stringify(requestData));
            data.splitTypesData = JSON.stringify(requestData.splitTypesData);

            var url = '../' + REST_API_PATH + 'split-test/upload-image';
            console.log($scope.splitTestData.selectedSplitTestType);
            data['base64files'] = $scope.filesArr;

            var url = '../' + REST_API_PATH + 'split-test/add';
            if ($routeParams.id) {
                url = '../' + REST_API_PATH + 'split-test/edit/' + $routeParams.id;
            }
            $http.post(url, data)
                .then(function (response) {
                    if (!response.data.success) {
                        $scope.SpllitForm.$submitted = false;
                        Toast.showToast(response.data.error_message);
                    } else {
                        $location.path('/split-test');
                        Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
                    }
                });

        };
        $scope.doFileMap = function (mapped_files) {

        };
        $scope.uploadFile = function (files, index) {

            var reader = new FileReader();
            reader.onload = (function (theFile) {
                return function (e) {
                    var binaryData = e.target.result;
                    //Converting Binary Data to base 64
                    var base64String = window.btoa(binaryData);
                    //showing file converted to base64
                    $scope.splitTestData.splitTypesData[index].encodedFile = base64String;
                    $scope.filesArr[index] = base64String;
                    console.log(base64String);
                };
            })(files[0]);
            reader.readAsBinaryString(files[0]);
            var textInput = $(angular.element('#textInput' + index));
//            console.log(textInput.val());
//            if ($scope.exisTingFiles.hasOwnProperty(textInput.val())) {
//                $scope.exisTingFiles[textInput.val()] = files[0].name;
//            }

            textInput.val(files[0].name);
            $scope.splitTestData.splitTypesData[index].fileName = files[0].name;
            //  $scope.filesArr[index] = files[0];
            console.log($scope.filesArr);

        };
        $scope.fileLoad = function (index) {
            var input = $(angular.element('#fileInput' + index));
            var textInput = $(angular.element('#textInput' + index));

            input.click();
            textInput.click();
        };

        
        $scope.selectSplitTestType = function () {
            console.log($scope.selectedSplitTestType);
        };
    })
    .filter('splitCamelCase', [function () {
        return function (input) {
      
          if (typeof input !== "string") {
            return input;
          }
      
          return input.split(/(?=[A-Z])/).join(' ');
      
        };
    }]);