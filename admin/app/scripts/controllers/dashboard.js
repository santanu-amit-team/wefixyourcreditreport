'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:DashboardCtrl
 * @description
 * # DashboardCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
.run(function($rootScope, Toast){

    if(typeof GoogleAdManagerId != 'undefined' && GoogleAdManagerId !== ''){
        $rootScope.GoogleAdManagerId = GoogleAdManagerId;
    }
    
    if(typeof AdImgLink != 'undefined' && AdImgLink !== ''){
        $rootScope.AdImgLink = AdImgLink;
    }
    
    if(typeof AdImgSrc != 'undefined' && AdImgSrc !== ''){
        $rootScope.AdImgSrc = AdImgSrc;
    }
    
    $rootScope.loadAdManager = false;
    $rootScope.removeIframe = false;
    $rootScope.isIframeLoaded = false;
    window.addEventListener('message',function(message){
        if(message.data.type=="mail.send"){

          if (message.data.data.success) {
              $rootScope.removeIframe = true;
            //   Toast.showToast(message.data.data.message);
          }
          else {
            Toast.showToast(message.data.data.message);
          }
        }
    });

    if(typeof AdImgLink != 'undefined' && AdImgLink !== '' && typeof AdImgSrc != 'undefined' && AdImgSrc !== '' ){
        $rootScope.loadAdManager = true;
//        window.loadAdManager(function() {
//            window.googletag = window.googletag || {cmd: []};
//            googletag.cmd.push(function() {
//                googletag.defineSlot(GoogleAdManagerSlot, [389, 250], GoogleAdManagerId).addService(googletag.pubads());
//                googletag.pubads().enableSingleRequest();
//                googletag.enableServices();
//            });
//    
//            googletag.cmd.push(function() { 
//                googletag.display(GoogleAdManagerId); 
//                
//                setTimeout(function(){
//                    var domelement = document.getElementsByClassName("googleAdmanagerWrapper");
//                    var googleQueryId = angular.element(domelement).data('google-query-id');
//                    if( typeof googleQueryId !== 'undefined' && googleQueryId.length) {
//                         $rootScope.loadAdManager = true;
//                    }
//                }, 1000);
//                
//             });
//        });
    }
})
.controller('DashboardCtrl', function(
    $rootScope, $scope, Toast, $http, $location, Dialog, $mdDialog, $interval, $timeout, Resolver
) {
    /**
     * Load dependency check result
     */
    $scope.caching = {};
    $scope.configLoader = false;
    $scope.pageSpeed = {};
    $scope.pageSpeedLoader = false;
    $scope.pageSpeedLoaderOnInit = false;
    var data;
    $scope.invalidConf = false;
    $scope.checkingDone = false;
    $scope.pleaseWait = '';
    $scope.isJsMinifierExist = false;
    $scope.purgeLoader = false;
    $scope.spinner = false;
    $scope.issueSections = [];
    $scope.highlightSections = [];
    $scope.showSetttingsRelatedThing = false;
    $scope.version = {};

    $http.post('../' + REST_API_PATH + 'dashboard/check-extensions', { extention : 'JsMinifier' }).then(function (response) {
        if (!response.data.extensionCouponsActive) {
            $scope.isJsMinifierExist = false;
        }
        else {
            $scope.isJsMinifierExist = true;
        }
    });
    
    $http.post('../backend/resource.php?method=dependency').then(function(response) {
        $scope.extensions = response.data;
    });
    $http.post('../backend/resource.php?method=fs').then(function(response) {
        $scope.fileSystem = response.data;
    });

    var resourceChecker;
    resourceChecker = $interval( function() {
        $http.post('../backend/resource.php?method=disk').then(function(response) {
            $scope.diskSystem = response.data;
        });

    },5000);

    $scope.purgeCache = function(){

        $scope.purgeLoader = true;

        if ($scope.caching.js) {
            // javascript cache purge
        }
        else if($scope.caching.all) {
            // everything cache purge
        }
        else {
            // Both option cache purge
        }

        $http.get('../' + REST_API_PATH + 'dashboard/purge')
        .then(function(response) {
            
            if(response.data.success) {
                Toast.showToast(response.data.message);
                $scope.caching.js = $scope.caching.all = false;
            }
            else {
                Toast.showToast(response.data.error_message);
            }
            $scope.purgeLoader = false;
        });
    }
    

    $http.post('../backend/resource.php?method=googlepagespeedapiexist').then(function(response) {
        $scope.googlePageSpeedApi = response.data;
        $scope.checkingDone = true;
    });
    

    $scope.getGooglePageSpeed = function(isInit){
        
        if(typeof isInit === 'undefined' || !isInit) {
            $scope.pageSpeedLoader = true; 
        }

        if(isInit) var data = { loadChache : true };

        $http.post('../backend/resource.php?method=googlepagespeed', data ).then(function(response) {
            
            if(!response.data.status) {
                $scope.pageSpeedLoader = false;
                return;
            }
            var data = response.data.speed;
            var temp = angular.copy(data);

            if(typeof data[0] !== 'undefined' && typeof data[0].value !== 'undefined')
                data[0].value = 0;

            if(typeof data[1] !== 'undefined' && typeof data[1].value !== 'undefined')
                data[1].value = 0;

            $scope.pageSpeed = data;

            if(typeof data[0] !== 'undefined' && typeof data[0].value !== 'undefined') {
                var desktop = $interval(function(){

                    if($scope.pageSpeed[0].value < temp[0].value) {
                        $scope.pageSpeed[0].value = ($scope.pageSpeed[0].value == 100)? 100 :$scope.pageSpeed[0].value + 1;
                    }
                    else {
                        $interval.cancel(desktop);
                    }
    
                }, 20);
            }
            

            if(typeof data[1] !== 'undefined' && typeof data[1].value !== 'undefined' ){
                var mobile = $interval(function(){

                    if($scope.pageSpeed[1].value < temp[1].value) {
                        $scope.pageSpeed[1].value = ($scope.pageSpeed[1].value == 100)? 100 :$scope.pageSpeed[1].value + 1;
                    }
                    else {
                        $interval.cancel(mobile);
                    }
    
                }, 20);
            }
           
            $scope.pageSpeedLoader = false;
        });
    }

    $scope.getConfigurationDetails = function(){
        $scope.configLoader = true;
        $http.post('../backend/resource.php?method=configurations').then(function(response) {
            $scope.configs = response.data;
            angular.forEach($scope.configs, function(value, key){
                $scope.highlightSections.push(value.highlight);
                if(!$scope.issueSections.includes(value.section)) 
                    $scope.issueSections.push(value.section);       
            });
            setTimeout(function() {
                if (angular.element(document.querySelector('.list-config-errors p[ng-class="danger"]')).length) {
                    $scope.invalidConf = true;
                    $scope.$apply('invalidConf');
                }
                $scope.configLoader = false;
            }, 1000);
            
        });
    }

    $scope.resolveErrors = function() {

        if($scope.issueSections.length) {
            
            $rootScope.issueSections = {
                needAction : 'true',
                list: $scope.issueSections,
                highlight: $scope.highlightSections
            }
        }
        $location.path('/settings/');
    }

    $scope.getConfigurationDetails();
   
    $http.get('../' + REST_API_PATH + 'dashboard/get-cron-running-status')
        .then(function(response) {
            return;
        });
    $http.get('../' + REST_API_PATH + 'dashboard/check-dev-mode-status')
        .then(function(response) {
            return;
        });
    $http.get('../' + REST_API_PATH + 'dashboard/update-tracking-id')
        .then(function(response) {
            return;
        });
    $scope.settings = {};
    var devModeResetTimer = null;
    $scope.startTimer = function(pendingSeconds) {
        if (devModeResetTimer !== null) {
            clearInterval(devModeResetTimer);
        }
        devModeResetTimer = setInterval(function() {
            pendingSeconds--;
            if (pendingSeconds <= 0) {
                pendingSeconds = 0;
                clearInterval(devModeResetTimer);
                devModeResetTimer = null;
            }
            var date = new Date(null);
            date.setSeconds(pendingSeconds);
            $scope.$apply(function() {
                $scope.settings['counter'] = date.toISOString().substr(11, 8);
            });
        }, 1000);
    }
    $http.post('../' + REST_API_PATH + 'settings/get-dev-mode/1').then(function(response) {

        if(response.data.success) {
            $scope.settings = response.data.data;
            $scope.startTimer($scope.settings.timer_pending_seconds);
        }
    });
    $http.post('../backend/resource.php?method=modulesStatus').then(function(response) {
        $scope.extended_configs = response.data;
        setTimeout(function() {
            if (angular.element(document.querySelector('.list-config-errors p.danger')).length) {
                $scope.invalidConf = true;
                $scope.$apply('invalidConf');
            }
        }, 1000);
    });
    $scope.$watch('invalidConf', function() {});
    $scope.generateRewriteFile = function() {
        $scope.spinner = true;
        $http.post('../backend/resource.php?method=generateRewriteFile')
            //			$http.post('../backend/resource.php/generate-rewrite-file')
            .then(function(response) {
                if (response.data) {
                    Toast.showToast('Htaccess has been generated successfully');
                } else {
                    // $scope.htaccessCodeModal();
                    Toast.showToast('Htaccess has not been generated successfully');
                }

                $timeout(function(){ 
                    $scope.spinner = false;
                }, 2000);
                
            });
    };


    $scope.openShareFeedBack = function(){

        var content = '';   
        var url = '../' + REST_API_PATH + 'dashboard/feedback';
        var model = 'field';    
        var formName = 'ShareFeedBackForm'; 
        var url = '../' + REST_API_PATH + 'dashboard/get-feedback-details';

        $http.get(url).then(function(response) {
            content = response.data.message;
            if( response.data.success) {
                // call the form
                $rootScope.removeIframe = false;
                Dialog.dynamicTemplate('../app/views/modals/shareFeedback.html', content, url, model, formName);
            }
            else {
                //toast error message
                Toast.showToast('Please try again later.');
            }
        });
    }


    $scope.updateDevMode = function() {
        var devMode = $scope.settings.development_mode ? 1 : 0;
        var url = '../' + REST_API_PATH + 'settings/update-dev-mode/1/' + devMode;
        $http.get(url).then(function(response) {
            if (!response.data.success) {
                Toast.showToast(response.data.error_message);
                $scope.settings.development_mode = false;
            } else {
                var status = $scope.settings.development_mode ? 'enabled' : 'disabled';
                Toast.showToast("Development mode is " + status);
                $scope.startTimer(response.data.data.timer_pending_seconds);
            }
        });
    }
    $http.post('../' + REST_API_PATH + 'dashboard/all-alerts').then(function(response) {
        $scope.alert_list = response.data.data;
    });
    $scope.read = function(alert_id) {
        data = {
            id: alert_id
        };
        $http.post('../' + REST_API_PATH + 'dashboard/update-alert', data).then(function(response) {
            $scope.alert_list = response.data.data.data;
        });
    };
    $scope.alertHandler = function(url) {
        $location.path('/' + url);
    };
    /**
     * Load quick launchers
     */
    $http.get('../' + REST_API_PATH + 'dashboard/quick-launchers', data).then(function(response) {
        $scope.quickLaunchers = response.data;
    });
    $scope.launchShortcut = function(handler, _this) {
        $http.get(handler, data).then(function(response) {
            _this.loader = false;
            if (!("message" in response.data)) {
                Toast.showToast('Action called');
            } else {
                if (response.data.type == "dialog") {
                    Dialog.alertCustomDialog(response.data.message);
                } else {
                    Toast.showToast(response.data.message);
                }
            }
        });
    };

    $scope.checkDomainSwitch = function($event) {
        $http.get('../' + REST_API_PATH + 'dashboard/check-domain-switch').then(function(data) {
            if (!data.data.switched) {
                return;
            }
        });
    };
    
    $scope.checkPHPUserPermission = function($event) {
        $http.get('../' + REST_API_PATH + 'dashboard/check-phpuser-permission').then(function(response) {
            Toast.showToast(response.data.message);
        });
    };
    $scope.checkDomainSwitch();

    $scope.htaccessCodeModal = function(){

        $http.post('../backend/resource.php?method=getrewritefile')
            .then(function(response) {
                
                if (response.data.status) {
                    var content = response.data.details;
                    Dialog.dynamicTemplate('../app/views/modals/htaccessCopy.html', content);
        
                } else {

                    Toast.showToast(response.data.error_message);
                }
            });
    }

    $http.get('../' + REST_API_PATH + 'dashboard/check-permission/settings').then(function(response) {
        
        if(response.data.success){
            $scope.showSetttingsRelatedThing = true;
        }

    });
    
    $scope.$on('$destroy',function(){
       
        //Close the resource monitorr ajax call
        if(resourceChecker)
            $interval.cancel(resourceChecker);
    });

    $http.get('../' + REST_API_PATH + 'dashboard/get-framework-version').then(function(response) {
        
        if(response.data.success){
            $scope.version = response.data.data;
        }
        else {
            Toast.showToast(response.data.error_message);
        }

    });

});
