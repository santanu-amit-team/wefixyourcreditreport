'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:ExtensionCtrl
 * @description
 * # ExtensionCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
        .controller('ExtensionsCtrl', function ($scope, $mdToast, $http, $location, Toast, Dialog, $route, $filter, $mdDialog,
            extensionManager, Menu) {
            var flag, active_list, data, extensions, extension, active_url, downloading, download, status, edit_status, count = 0;
            var extensions_array = [];
            var installed_extension_list = [];
            $scope.loaded = false; 
            $scope.listIds = [];
            $scope.form = {
                button_disable: false
            };
            $scope.filterActions = [
                {key : 'update', val : 'Update', method : 'actionOnExtension'},
                {key : 'uninstall', val : 'Uninstall', method : 'actionOnExtension'}
            ];
            $scope.filterTypes = [
                {key : 'all', val : 'All', value : true, identifier : 'all', type : 'boolean'},
                {key : 'active', val : 'Active', value : true, identifier : 'active', type : 'boolean'},
                {key : 'inactive', val : 'Inactive', value : false, identifier : 'active', type : 'boolean'}
            ];

            $scope.clear = function () {
                $scope.searchText = '';
            };

            $scope.getListIds = function(){
                $scope.listIds = [];
                angular.forEach($scope.lists, function (item, index) {
                    $scope.listIds.push(item.id);
                });
            }
            
            $http.post('../' + REST_API_PATH + 'extensions/installed-extensions', {})
                .then(function (response) {
                    $scope.lists = response.data.data;
                    $scope.fullList = response.data.data;
                    $scope.getUpdatedVersion($scope.lists);
                    $scope.getListIds();
                    setTimeout(function(){
                        $scope.$apply(function(){
                            $scope.loaded = true;       
                        });
                    }, 1000);
                });

            //Any ajax response show in Dialog/Alert/Toast based on type
            $scope.popupMessageFilter = function (type, msg) {
                if (type == "dialog") {
                    Dialog.alertCustomDialog(msg);
                }
                else if (type == "alert") {
                    Dialog.alertDialog(msg);
                }
                else {
                    Toast.showToast(msg);
                }
            };

            $scope.getUpdatedVersion = function (extension_list) {
                $http.post('../' + REST_API_PATH + 'extensions/get-extensions-version', {})
                    .then(function (response) {
                        if(typeof response !== 'undefined' && response.data.success){
                            angular.forEach(response.data.data, function (repo, repo_key) {
                            angular.forEach(extension_list, function (extension, extension_key) {
                                if (typeof (extension.extension_slug) &&
                                        extension.extension_slug === repo.extension_slug)
                                    {
                                        if ($scope.isUpdatedVersion(extension.version, repo.version)) {
                                            extension_list[extension_key].updated_version = repo.version;
                                        } else {
                                            extension_list[extension_key].updated_version = false;
                                        }
                                    }
                                });
                            });
                        }
                    });
            };

            $scope.isUpdatedVersion = function (present_version, updated_version) {
                var present_version = present_version.replace(/\./g, '');
                var updated_version = updated_version.replace(/\./g, '');
                if(present_version.length > updated_version.length) {
                    updated_version = updated_version + '0';
                }
                if (parseInt(updated_version) > parseInt(present_version)) {
                    return true;
                } else {
                    return false;
                }
            };

            $scope.findIndex = function (extensionSlug) {
                var index;
                angular.forEach($scope.lists, function (value, key) {
                    if (value.extension_slug == extensionSlug) {
                        index = key;
                    }
                });
                return index;
            };

            //Extension manage with its settings inputs
            $scope.editExtension = function (extensionSlug) {
                if ($scope.form.button_disable === true) {
                    return false;
                }
                var index = $scope.findIndex(extensionSlug);
                if ($scope.lists[index].installed === true && ($scope.lists[index].edit_status == 1 || $scope.lists[index].edit_status == 0)) {
                    $location.path('/extensions/' + extensionSlug);
                } else if ($scope.lists[index].installed === true && $scope.lists[index].edit_status == 2) {
                    return false;
                }
            };

            //Extension active or deactive
            $scope.stateChanged = function (extensionSlug, button) {
                $scope.form = {
                    button_disable: true
                };
                var index = $scope.findIndex(extensionSlug);
                extension = $scope.lists[index];
                $scope['active_' + extension.extension_slug] = true;
                $scope['downloading_' + extension.extension_slug] = true;
                // $scope['more_' + extension.extension_slug] = true;

                if (extension.edit_status == 0) {
                    $location.path('/extensions/' + extensionSlug);
                }
                else if (extension.edit_status == 1 || extension.edit_status == 2) {

                    data = {
                        extension_slug: extension.extension_slug,
                        extension_name: extension.extension_name,
                        extension_des: extension.extension_des,
                        active: button === true ? false : extension.active,
                        installed: extension.installed,
                        version: extension.version,
                        edit_status: extension.edit_status,
                        protected: extension.protected,
                        category : extension.category,
                        author : extension.author
                    };
                    $http.post('../' + REST_API_PATH + 'extensions/edit/' + extension.id, data)
                            .then(function (response) {

                                if(response.hasOwnProperty('data') && response.data.hasOwnProperty('data') && response.data.data.hasOwnProperty('extension_slug') && response.data.data.hasOwnProperty('active')) {
                                    //remove menu if extension deactivate
                                    Menu.name(response.data.data.extension_slug, response.data.data.active);
                                }


                                $scope['downloading_' + extension.extension_slug] = false;
                                $scope['active_' + extension.extension_slug] = false;
                                $scope['more_' + extension.extension_slug] = false;
                                $scope.form = {
                                    button_disable: false
                                };
                                if (response.data.success == true) {
                                    if (response.data.data.active == true) {
                                        status = 'You have activated the extension.';
                                    } else {
                                        status = 'You have deactivated the extension.';
                                    }

                                    $scope.popupMessageFilter(response.data.type, status);

                                    $scope.lists[index]['options'] = response.data.data['options'];
                                    $scope.lists[index]['active'] = response.data.data['active'];
                                    $location.path('/extensions');
                                }
                                else {
                                    $scope.lists[index]['active'] = (button === true) ? true : false;
                                    $scope.popupMessageFilter(response.data.type, response.data.error_message);
                                }
                            });
                }
            };

            //Active extension's option ajax call 
            $scope.actionCall = function (link, extension_slug, options) {
                if (options.hasOwnProperty('confirm') && options.hasOwnProperty('confirm') == true) {
                    var confirm = $mdDialog.confirm()
                            .title('Confirm')
                            .textContent(options.confirm)
                            .ok('Yes')
                            .cancel('No');

                    $mdDialog
                            .show(confirm)
                            .then(function () {
                                $scope.actionCallConfirmed(link, extension_slug);
                            });
                } else {
                    $scope.actionCallConfirmed(link, extension_slug);
                }
            };

            $scope.actionCallConfirmed = function (link, extension_slug) {
                $scope.form = {
                    button_disable: true
                };

                $scope['downloading_' + extension_slug] = true;
                $scope['more_' + extension_slug] = true;
                $http.get('../../' + AJAX_PATH + 'extensions/' + angular.lowercase(extension_slug) + '/' + link)
                        .then(function (response) {
                            $scope['downloading_' + extension_slug] = false;
                            $scope['more_' + extension_slug] = false;
                            $scope.form = {
                                button_disable: false
                            };
                            if (!("message" in response.data)) {
                                Toast.showToast('Action called');
                            }
                            else {
                                $scope.popupMessageFilter(response.data.type, response.data.message);
                            }
                        }, function (response) {
                            $scope['downloading_' + extension_slug] = false;
                            $scope['more_' + extension_slug] = false;
                            Toast.showToast('Not found');
                        });
            }

            $scope.updateExtension = function (extensionSlug) {
                $scope.form = {
                    button_disable: true
                };
                var index = $scope.findIndex(extensionSlug);
                extension = $scope.lists[index];
                $scope['downloading_' + extension.extension_slug] = true;
                $scope['more_' + extension.extension_slug] = true;
                flag = {
                    flag: 'update'
                };
                $http.post('../' + REST_API_PATH + 'extensions/download-extension/' + extension.extension_slug, flag)
                    .then(function (response) {
                        $scope.form = {
                            button_disable: false
                        };
                        if (response.data.success == false) {
                            $scope['downloading_' + extension.extension_slug] = false;
                            $scope['more_' + extension.extension_slug] = false;
                            $scope.lists[index].active = extension.active;
                            $scope.popupMessageFilter(response.data.type, response.data.error_message);
                        }
                        else {
                            $scope['downloading_' + extension.extension_slug] = false;
                            $scope.lists[index] = response.data.data;
                            $scope.popupMessageFilter(response.data.type, response.data.message);
                        }
                    });
            };
            $scope.uninstall = function (extensionSlug) {
                var url = '../' + REST_API_PATH + 'extensions/uninstall-extension/' + extensionSlug;
                Dialog.showDialog(url, 'Uninstall');
            };


        /** Multiple Checkbox work **/
        $scope.selectedRows = [];
        $scope.selectedData = [];
        $scope.toggle = function (item, extension) {
            if($scope[`downloading_${extension.extension_slug}`] === true || $scope.form.button_disable === true){
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

        /**
         * Perform actions : update/uninstall 
         **/
        $scope.actionOnExtension = function ( ids, action ) {
            let args = {
                'extension_list' : $scope.selectedData,
                'post_data' : (action === 'update') ? { flag: 'update' } : {}
            };
            let actionManagerArr = {
                update : 'updater',
                uninstall : 'uninstaller'
            };

            extensionManager[actionManagerArr[action]](args).then(function(res){
                $scope.selectedRows = [];
                $scope.selectedData = [];
                if(res.success.ids.length < 1){
                    return false;
                }
                if(action === 'uninstall'){
                    let newList = [];
                    angular.forEach($scope.fullList, function (item, index) {
                        if(res.success.ids.indexOf(item.extension_slug) === -1){
                            newList.push(item);
                        }
                    });
                    $scope.$apply(function() {
                        $scope.lists = newList;
                        $scope.fullList = newList;
                        $scope.getListIds();
                    });
                }
                else if (action === 'update'){
                    angular.forEach(res.success.ids, function (item, index) {
                        var list_index = $scope.findIndex(item); 
                        var response = res.success.raw_responses[item];
                        $scope.$apply(function() {
                            if(response.data.success){
                                $scope.lists[list_index].active = response.data.data.active;
                                $scope.fullList[list_index].active = response.data.data.active;
                            }
                            else{
                                $scope.lists[list_index] = response.data.data;
                                $scope.fullList[list_index] = response.data.data;
                            }
                        });
                    });
                }
            });
        }

});
