'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:ExtensionCatalogueCtrl
 * @description
 * # ExtensionCatalogueCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp').controller('ExtensionCatalogueCtrl', function($scope, $mdToast, $http, 
    $location, Toast, Dialog, $mdDialog, $timeout, extensionManager) {
    var extension, edit_status, data, downloading, download, flag, msg;
    $scope.dataLoadComplete = false;
    $scope.listIds = [];
    $scope.clear = function () {
			$scope.searchText = '';
		};

    $scope.filterTypes = [
                {key : 'all', val : 'Category'},
                {key : 'security', val : 'Security'},
                {key : 'utility', val : 'Utility'},
                {key : 'crm', val : 'CRM'},
                {key : 'automation', val : 'Automation'},
                {key : 'validation', val : 'Validation'},
                {key : 'payment', val : 'Payment'},

            ];
    $scope.filterActions = [
        {key : 'install', val : 'Install', method : 'actionOnExtension'},
        {key : 'uninstall', val : 'Uninstall', method : 'actionOnExtension'},
    ];

    //dummy data
    var dummy_list = [{
        "extension_slug": "extension_slug_1",
        "extension_name": "Extension name 1",
        "extension_des": "Extension description",
        "active": null,
        "id": null,
        "installed": false,
        "version": "1.0.0",
        "edit_status": null,
        "cover": "https://framework.201clicks.com/covers/new/filler.jpg",
        "protected": false
    }, {
        "extension_slug": "extension_slug_2",
        "extension_name": "Extension name 1",
        "extension_des": "Extension description",
        "active": null,
        "id": null,
        "installed": false,
        "version": "1.0.0",
        "edit_status": null,
        "cover": "https://framework.201clicks.com/covers/new/filler.jpg",
        "protected": false
    }, {
        "extension_slug": "extension_slug_3",
        "extension_name": "Extension name 1",
        "extension_des": "Extension description",
        "active": null,
        "id": null,
        "installed": false,
        "version": "1.0.0",
        "edit_status": null,
        "cover": "https://framework.201clicks.com/covers/new/filler.jpg",
        "protected": false
    }, {
        "extension_slug": "extension_slug_4",
        "extension_name": "Extension name 1",
        "extension_des": "Extension description",
        "active": null,
        "id": null,
        "installed": false,
        "version": "1.0.0",
        "edit_status": null,
        "cover": "https://framework.201clicks.com/covers/new/filler.jpg",
        "protected": false
    }];

    $scope.extension_list = dummy_list;

    $scope.descriptionPopup = function(extension_name, content){
        Dialog.dynamicTemplate('../app/views/modals/extensionDescriptionView.html', {extension_name : extension_name, content : content});
    }

    $scope.changelogPopup = function(extension, $this){
        $this.changeLogLoading = true;
         $http.post('../' + REST_API_PATH + 'extensions/changelog/' + extension.extension_slug)
            .then(function(response) {
                $this.changeLogLoading = false;
                Dialog.dynamicTemplate('../app/views/modals/extensionChangelogView.html', 
                    {extension_name : extension.extension_name, content : response.data.data});
            },function(error){ 
                $this.changeLogLoading = false; 
                console.log(error); 
            });
    }

    $scope.getListIds = function(){
        $scope.listIds = [];
        angular.forEach($scope.extension_list, function (item, index) {
            $scope.listIds.push(item.extension_slug);
        });
    }

    $http.post('../' + REST_API_PATH + 'extensions/all-extensions', {}).then(function(response) {
        $scope.exlist = response.data.data;
        $scope.extension_list = response.data.data;
        $scope.fullList = response.data.data;
        $scope.getListIds();
        $scope.dataLoadComplete = true;
    });
    $scope.findIndex = function(extensionSlug) {
        var index;
        angular.forEach($scope.extension_list, function(value, key) {
            if (value.extension_slug == extensionSlug) {
                index = key;
            }
        });
        return index;
    };
    //extension download
    $scope.downloadExtension = function(extensionSlug) {
         $scope.form = {
                button_disable:true
            };
    	var index = $scope.findIndex(extensionSlug);
        extension = $scope.extension_list[index];
        downloading = 'downloading_' + extension.extension_slug;
        download = 'download_' + extension.extension_slug;
        $scope[download] = true;
        $scope[downloading] = true;
        flag = {
            flag: 'download'
        };
        $http.post('../' + REST_API_PATH + 'extensions/download-extension/' + extensionSlug, flag)
            .then(function(response) {
            $scope.form = {
                button_disable:false
            };
            if (response.data.success == false) {
                $scope[download] = false;
                $scope[downloading] = false;
                if(!angular.isUndefined(response.data.type) && response.data.type == 'warning-popup') {
                    Dialog.dynamicTemplate('../app/views/modals/licenseFailurePopup.html', '');
                }
                else {
                    Toast.showToast(response.data.error_message);
                }

            } else if (response.data.success == true) {
                $scope[download] = false;
                $scope[downloading] = false;
                msg = response.data.message;
                $scope.extension_list[index].active = response.data.data.active;
                $scope.extension_list[index].active_list_id = response.data.data.id;
                $scope.extension_list[index].installed = response.data.data.installed;
                $scope.extension_list[index].edit_status = response.data.data.edit_status;
                Toast.showToast(msg);
            }
        });
    };
    $scope.uninstall = function(extensionSlug) {
        var url = '../' + REST_API_PATH + 'extensions/uninstall-extension/' + extensionSlug;
        var confirm = $mdDialog.confirm()
                    .title('Uninstall')
                    .textContent('Are you sure? This action cannot be undone')
                    .ok('Yes')
                    .cancel('No');
        $mdDialog.show(confirm).then(function () {
             $scope.form = {
                button_disable:true
            };
            var deleting = `deleting_${extensionSlug}`;
            $scope[deleting] = true;
            $http.post(url, data)
                .then(function (response) {
                    if (response.data.success)
                    {
                        Toast.showToast(response.data.message ? response.data.message : 'Deleted successfully');
                        var index = $scope.findIndex(extensionSlug); 
                        $scope.extension_list[index] = response.data.data;
                    }
                    else
                    {
                        Toast.showToast(response.data.error_message);
                    }
                    $timeout(function(){
                        $scope.form = {
                        button_disable:false
                        };
                        $scope[deleting] = false;

                    }, 2000);
                });
        });
    }

    /** Multiple Checkbox work **/
    $scope.selectedRows = [];
    $scope.selectedData = [];
    $scope.toggle = function (item, extension) {
        if($scope.dataLoadComplete === false){
            return false;
        }
        var idx = $scope.selectedRows.indexOf(item);
        if (idx > -1) { 
            $scope.selectedRows.splice(idx, 1);
            $scope.selectedData.splice(idx, 1);
        }
        else { 
            if($scope.selectedRows.length == 0){
                $scope.selectedData = [];
            }
            $scope.selectedRows.push(item);
            $scope.selectedData.push(extension);
        }
    };
    $scope.exists = function (item) {
        return $scope.selectedRows.indexOf(item) > -1;
    };

    /** Perform action **/
    $scope.actionOnExtension = async function ( ids, action ) {
        
        var stopAction = false;
        var data = [];
        $scope.securityCheck = true;


        if(action == 'install'){
           //check license key validation if true then proceed or show pop up error message.
           await $http.post('../' + REST_API_PATH + 'extensions/security-check', {}).then(function(response) {
                if(!response.data.success){
                    $scope.securityCheck =  false;
                }
           });
        }

        if(!$scope.securityCheck) {
            Dialog.dynamicTemplate('../app/views/modals/licenseFailurePopup.html', '');
            return false;
        }

        for (let extension in $scope.selectedData){
            if((action === 'install' && $scope.selectedData[extension].installed === true) ||
                action === 'uninstall' && $scope.selectedData[extension].installed !== true){
                stopAction = true;
                break;
            }

            data.push({
                extension_name : $scope.selectedData[extension].extension_name, 
                extension_slug : $scope.selectedData[extension].extension_slug
            });
        }
        if(stopAction === true){
            Dialog.alertDialog(
                action == 'install' ? 
                'Some of the extensions are already installed' :
                'Some of the extensions are not installed'
                );
            return false;
        }

        let args = {
            'extension_list' : data,
            'post_data' : {}
        };

        if(action == 'install'){
            args.post_data = {flag: 'download'};
        }

        let actionManagerArr = {
            install : 'installer',
            uninstall : 'uninstaller'
        };
        extensionManager[actionManagerArr[action]](args).then(function(res){
            $scope.selectedRows = [];
            $scope.selectedData = [];
            if(res.success.ids.length < 1){
                return false;
            }
            angular.forEach(res.success.ids, function (item, index) {
                var item_index = $scope.findIndex(item); 
                var response = res.success.raw_responses[item];
                $scope.$apply(function() {
                    $scope.extension_list[item_index] = response.data.data;
                });
            });
        });
    }
});