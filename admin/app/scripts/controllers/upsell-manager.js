angular.module('codeBaseAdminApp').controller('CheckExtensionsDialog', function($scope, $mdDialog){
    	$scope.closeDialog = function(){
    		$mdDialog.hide();
    	};
    }).controller('UpsellManager', function ($scope, $templateCache, $compile, $route,$http, $location, $mdDialog, Dialog, $rootScope, Toast) {

    $scope.checkExtensions = function(){
        $http.get('../' + REST_API_PATH + 'upsell-manager/check-extensions').
            then(function(response) {

                var message = '', isMultipleAlert = false;
                if(!response.data.extensionAffiliatesActive){
                        message += 'Install <strong>Upsell Manager</strong> extension' +
                                        ' and activate <strong>Affiliates</strong> from <strong>Settings</strong>';
                        isMultipleAlert = true;
                }

                if(message !== ''){
                        message = '<p style="padding: 15px; text-align: center;">' + message + '</p>';
                        $mdDialog.show($mdDialog.alert({
                            template: ' <md-dialog>' +
                                            '	<md-dialog-content>' +
                                            '	' + message +
                                            '  	</md-dialog-content>' +
                                            '  	<md-dialog-actions>' +
                                            '   	<md-button ng-click="closeDialog()" class="md-primary">' +
                                            '      		Ok' +
                                            '   	</md-button>' +
                                            '  	</md-dialog-actions>' +
                                            '</md-dialog>',
                            controller: 'CheckExtensionsDialog'
                        }));
                }
				
            });
        }
        $scope.filterActions = [{key : 'delete', val : 'Delete', method : 'deleteUpsell'}];
	    $scope.filterTypes = [{key : 'all', val : 'All'}];
	    $scope.selectedType = 'all';
         $scope.listIds = [];
         $scope.getListIds = function(){
            angular.forEach($scope.lists, function (item, index) {
                $scope.listIds.push(item.id);
            });
        }
        $scope.lists = [];
        $scope.getAll = function(){

                 $http.post('../' + REST_API_PATH + 'upsell-manager/all/')
                     .success(function (response) {
                        $scope.lists = response.data;
                        $scope.fullList = response.data;
                        $scope.getListIds();
                     });
        }
        $scope.getAll();
        $scope.dataLoadComplete = true;
        $scope.editUpsellManager = function (id){
            $location.path('/upsell-manager-configuration/' + id);
        }
   
   		/** Multiple Checkbox work **/
        $scope.selectedRows = [];
        $scope.toggle = function (item) {
            var idx = $scope.selectedRows.indexOf(item);
            if (idx > -1) { $scope.selectedRows.splice(idx, 1);}
            else { $scope.selectedRows.push(item); }
        };
        $scope.exists = function (item) {
            return $scope.selectedRows.indexOf(item) > -1;
        };
        $scope.changeStatus = function(reqstatus,id,deviceType){
            var status="";
            if(reqstatus == "active"){
                status = "inactive";
            }else{
                status = "active";
            }
            var url = '../' + REST_API_PATH + 'upsell-manager/change-status';
            var title = (status == "active") ? "Only one profile can be activated at a time, so all other profiles with same device type will be deactivated automatically!" : 'Deactivate upsell profile';
           // Dialog.showDialog(url,title, {status : status,id : id});
          
            var confirm = $mdDialog.confirm()
					.title('Upsell Manager')
					.textContent(title)
					.ok('Yes')
					.cancel('No');
            $mdDialog.show(confirm).then(function () {
                $http.post(url, {status : status,id : id, deviceType:deviceType})
                    .then(function (response) {
                            if (response.data.success)
                            {
                                    $route.reload();
                                    Toast.showToast('Status changed successfully');
                            }
                            else
                            {
                                    $scope.alertDialogFn(response.data.error_message);
                            }
                    });		
					
            });
            
            
        }
        
        $scope.alertDialogFn = function(content, okBtnMessage){
    		okBtnMessage = (typeof okBtnMessage !== 'undefined' && okBtnMessage !== '') ? okBtnMessage : 'Got it!';
				return $mdDialog.show(
					$mdDialog.alert()
					.parent(angular.element(document.querySelector('#popupContainer')))
					.clickOutsideToClose(true)
					.textContent(content)
					.ariaLabel('Alert')
					.ok(okBtnMessage)
					);
    	}
        
        $scope.deleteUpsell = function (id) {
            var url = '';
            if(angular.isObject(id)){
                url = '../' + REST_API_PATH + 'upsell-manager/delete';
                Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(id))});
            }
            else{
                url = '../' + REST_API_PATH + 'upsell-manager/delete/' + id;
                Dialog.showDialog(url, '', '');
            }
        }
        
        $scope.copyUpsellManage = function (index) {
              var copy = $scope.lists[index];
              copy['copy'] = true;
              Dialog.showCopyDialog('../' + REST_API_PATH + 'upsell-manager/add', copy, '/upsell-manager-configuration/');
          };
       
});