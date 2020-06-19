angular.module('codeBaseAdminApp').controller('CheckExtensionsDialog', function($scope, $mdDialog){
    	$scope.closeDialog = function(){
    		$mdDialog.hide();
    	};
    }).controller('UpsellManagerConfiguration', function ($scope,$routeParams, $templateCache, $compile, $http, $location, $mdDialog, Dialog, $rootScope, Toast) {

    $scope.checkExtensions = function(){
        $http.get('../' + REST_API_PATH + 'upsell-manager/check-extensions').
            then(function(response) {

                var message = '', isMultipleAlert = false;
                if(!response.data.extensionUpsellManagerActive){
                        message += 'Install <strong>Upsell Manager</strong> extension' +
                                        ' and activate';
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
        $scope.checkExtensions();
        
        $scope.devices = [
            {
                label:"Mobile",
                value:"mobile"
            },
            {
                label:"Desktop",
                value:"desktop"
            },
            {
                label:"All",
                value:"all"
            }
        ];
        $scope.upsellManage = {};
        if(!$routeParams.id){
            $scope.upsellManage.upsellData = [];
        }
        $scope.defaultUpsellFileds = function(){
            return {
                'defaultVariationFileds' : [],
                'nextUpsell':'',
                'nextDownsell':'',
                'configuration':'',
                'upsellPage':''
            }
        }
        $scope.defaultVAriationFileds = function(){
            return {
                'campaignId':'',
                'content':'',
                'variation':'',
               // 'configId' : '',
                'currentVariationPercenatge':''
            }
        }
        $scope.htmlContentValid = true;
        $scope.totalVaration = 0;
        if(!$routeParams.id){
            $scope.upsellManage.upsellData.push($scope.defaultUpsellFileds());
            //console.log($scope.upsellManage.upsellData);
            $scope.upsellManage.upsellData[0]['defaultVariationFileds'].push($scope.defaultVAriationFileds());
        }
        
        $scope.getCampaigns = function(){
            $http.get('../' + REST_API_PATH + 'upsell-manager/get-campaigns').
            then(function(response) {
                $scope.campaignLists = response.data;
//                console.log($scope.campaignLists);
            });
        }
        $scope.getConfig = function(){
            $http.get('../' + REST_API_PATH + 'upsell-manager/get-config').
            then(function(response) {
                $scope.configLists = response.data;
//                console.log($scope.configLists);
            });
        }
        $scope.getCampaigns();
        $scope.getConfig();
//        $scope.expandEditor = function(index){
//            $scope.upsellManage.upsellData[index]['expand'] = !$scope.upsellManage.upsellData[index]['expand'];
//            if($scope.upsellManage.upsellData[index]['expand'])
//                $scope.upsellManage.upsellData[index]['expandText'] = "Minimize Codeground -";
//            else
//                $scope.upsellManage.upsellData[index]['expandText'] = "Expand Codeground +";
//        }
        
        /*add product*/

        $scope.add = function(arr){
            $scope.upsellManage.upsellData.push($scope.defaultUpsellFileds());
            var currrentIndex = $scope.upsellManage.upsellData.length - 1;
            $scope.upsellManage.upsellData[currrentIndex]['defaultVariationFileds'].push($scope.defaultVAriationFileds());
        }
        $scope.remove = function(index){
            $scope.upsellManage.upsellData.splice(index, 1);
        }
        $rootScope.upsellPopup = [];
        $scope.openEditor = function(parent,index){
//            console.log(parent+""+index);
           $scope.updateModelWithRootscopeData();
//            $scope.upsellManage.upsellPopupIndex = index;
            if(!$rootScope.upsellPopup.hasOwnProperty(parent)){
                $rootScope.upsellPopup[parent] = [];
            }
            $rootScope.content = $scope.upsellManage.upsellData[parent]['defaultVariationFileds'][index]['content'];
            $rootScope.upsellPopupParentIndex = parent;
            $rootScope.upsellPopupIndex = index;
           // $rootScope.upsellPopup['indexKey'] = index;
            //$rootScope.upsellPopup[index]= {'content' : ''};
            
                $mdDialog.show({
                    controller: "UpsellManagerPopup",
                    templateUrl: '../app/views/modals/upsellManagerEditor.html',
                    clickOutsideToClose: false
                });
        }
//            $scope.$watch(function (scope) {
//                return $rootScope.upsellPopup
//                },
//                function () {
//                     console.log($rootScope.upsellPopup);
//                        for(var key in $rootScope.upsellPopup){
//                            console.log(key);
//                        }
//                });
         $scope.closeModal = function(){
        $mdDialog.hide();
    }
    
    $scope.addVariation = function(index){
        $scope.upsellManage.upsellData[index]['defaultVariationFileds'].push($scope.defaultVAriationFileds());
    }
    $scope.removeVariation = function(parentUpsellIndex,childIndex){
        $scope.upsellManage.upsellData[parentUpsellIndex]['defaultVariationFileds'].splice(childIndex, 1);
    }
    $scope.updateModelWithRootscopeData = function(){
       
        if(!$rootScope.upsellPopup.length > 0)
            return;
         console.log($rootScope.upsellPopup);
        $scope.totalVaration = 0;
        for(var key in $rootScope.upsellPopup){
            if($scope.upsellManage.upsellData.hasOwnProperty(key) && 
                $scope.upsellManage.upsellData[key].hasOwnProperty('defaultVariationFileds'))
            {
               
                for(var childKey in $scope.upsellManage.upsellData[key]['defaultVariationFileds']){
                    if($scope.upsellManage.upsellData[key]['defaultVariationFileds'][childKey].
                            hasOwnProperty('content') && typeof $rootScope.upsellPopup[key][childKey] !== "undefined"){
                        console.log(childKey);
                        console.log($rootScope.upsellPopup[key][childKey]);
                        $scope.upsellManage.upsellData[key]['defaultVariationFileds'][childKey]['content'] =
                                $rootScope.upsellPopup[key][childKey];
                    }

                }
                
            }   
        }
      //  console.log($scope.upsellManage.upsellData);
    }
   
   $scope.validateFields = function(){
       
        for(var key in $scope.upsellManage.upsellData){
            $scope.totalVaration = 0;
            $scope.htmlContentValid=true;
            for(var child of $scope.upsellManage.upsellData[key]['defaultVariationFileds']){
                
                if($scope.totalVaration > 100){
                    Toast.showToast('Variation percentage should be within 100');
                    break;
                }
                
                $scope.totalVaration = parseInt(child['variation']) + parseInt($scope.totalVaration);
                if(!child.hasOwnProperty('content')){
                    $scope.htmlContentValid = false;
                    break;
                }
                if(child.hasOwnProperty('content') && (child['content'] == "" || 
                        typeof child['content'] == "undefined")){
                    $scope.htmlContentValid = false;
                    break;
                }
            }
           
            if(!$scope.htmlContentValid){
                Toast.showToast('Html content cannot be blank!');
                break;
            }
            if($scope.totalVaration != 100){
                Toast.showToast('Variation percentage should be equal to 100');
                break;
            }
        }
        
        
        if(!$scope.htmlContentValid || $scope.totalVaration != 100){
            return false;
        }
        else{
            return true
        }
   }
   $scope.lists = [];
   $scope.getAll = function(){
       
            $http.post('../' + REST_API_PATH + 'upsell-manager/all/')
                .success(function (response) {
                    $scope.UpsellLists = response.data;
                    if ($routeParams.id) {
                        for (var i = 0; i < $scope.UpsellLists.length; i++) {
                            if ($scope.UpsellLists[i].id == $routeParams.id) {
                                $scope.upsellManage = $scope.UpsellLists[i];
                                $scope.upsellManage.upsellData =  JSON.parse($scope.upsellManage.upsellData);
                               // $scope.lists = $scope.upsell;
                                break;
                            }
                        }
                    }
                });
   }
   if($routeParams.id)
   $scope.getAll();
    
    $scope.save = function(){
        $scope.updateModelWithRootscopeData();
        if(!$scope.validateFields()){
            
            $scope.UpsellManageForm.$submitted = false;;
            return;
        }
       
        $scope.UpsellManageForm.$submitted = false;
        var url = '../' + REST_API_PATH + 'upsell-manager/add';
        if ($routeParams.id) {
            url = '../' + REST_API_PATH + 'upsell-manager/edit/' + $routeParams.id;
        }else{
            $scope.upsellManage.status = "active";
        }
        
         var confirm = $mdDialog.confirm()
					.title("Upsell Manager")
					.textContent("Only one profile can be activated at a time, so all other profiles with same device type will be deactivated automatically!")
					.ok('Yes')
					.cancel('No');
    $mdDialog.show(confirm).then(function () {
        var data = JSON.parse(JSON.stringify($scope.upsellManage)); 
        data.upsellData = JSON.stringify($scope.upsellManage.upsellData);
        $http.post(url, data)
                    .then(function (response) {
                        if (!response.data.success) {
                            $scope.UpsellManageForm.$submitted = false;
                            Toast.showToast(response.data.error_message);
                        } else {
                            $location.path('/upsell-manager');
                            Toast.showToast($routeParams.id ? 'Saved successfully' : 'Added successfully');
                        }
                    });		
        });
        
        //console.log($scope.totalVaration);

       
        //console.log($scope.upsellManage.upsellData);
        
    }
    
    $scope.upsellManage.campaignProductLists = [];
    $scope.getProducts = function(parentUpsell,index,event){
        
        if(parentUpsell !== null){
            var campaignId = $scope.upsellManage.upsellData[parentUpsell]
            ['defaultVariationFileds'][index]['campaignId'];
            if(!campaignId){
                return;
            }
            $scope.upsellManage.upsellData[parentUpsell]
        ['defaultVariationFileds'][index]['campaignProductLists'] = [];
//            if(event = "change"){console.log(event);
//                $scope.upsellManage.upsellData[parentUpsell]['defaultVariationFileds'][index]['productId'] = 0;
//            }
        }else{
            var campaignId = $scope.upsellManage.upsellData[index]['triggerCAmpaignId'];
            if(!campaignId){
                return;
            }
//            console.log(index);
            $scope.upsellManage.upsellData[index]['triggerProductLists'] = [];
//            if(event = "change"){console.log(event);
//                $scope.upsellManage.upsellData[index]['triggerProducts'] = 0;
//            }
        }
        
        $http.post('../' + REST_API_PATH + 'campaigns/all/')
        .success(function (response) {
            $scope.campaignLists = response.data;
            for (var i = 0; i < $scope.campaignLists.length; i++) {
                
                if (parseInt($scope.campaignLists[i].id) == parseInt(campaignId)) {
                    $scope.campaign = $scope.campaignLists[i];
                    if(parentUpsell !== null){
                    $scope.upsellManage.upsellData[parentUpsell]
        ['defaultVariationFileds'][index]['campaignProductLists'] = JSON.parse($scope.campaign.product_array);
                    }else{
                        $scope.upsellManage.upsellData[index]['triggerProductLists'] = JSON.parse($scope.campaign.product_array);
                    }
                    break;
                }
            }
             console.log($scope.upsellManage.upsellData[index]['triggerProductLists']);
        });
       
    }
    
     $scope.$watch(function (scope) {
                return $rootScope.upsellPopup
                },
                function () {
                    $scope.updateModelWithRootscopeData();
                });
    
}).controller('UpsellManagerPopup', function ($scope,$routeParams, $templateCache, $compile, $http, $location, $mdDialog, Dialog, $rootScope, Toast) {
    $scope.closeModal = function(){
        $mdDialog.hide();
    }
    $scope.content = $rootScope.content;
    $scope.options = {
        language: 'en',
        allowedContent: true,
        entities: false,
        uiColor: '#cccccc'
    };
     $scope.$watch(function (scope) {
                return $scope.content
                },
                function () {
                    
                    $rootScope.upsellPopup[$rootScope.upsellPopupParentIndex]
                    [$rootScope.upsellPopupIndex] = $scope.content;
                    console.log($rootScope.upsellPopup);
                     //$rootScope.upsellPopup.splice($rootScope.upsellPopupIndex,0,$scope.content);
                });
    $scope.upsellManageTemplate = {};
    $scope.upsellManageTemplate.prebuiltTemplateContent = "100";
    $scope.prebulitTemplates = [];
    console.log($scope.upsellManageTemplate.prebuiltTemplateContent);
    
    $scope.getTemplates = function(id){
        var url = '../' + REST_API_PATH + 'upsell-manager/get-templates';
        var data = {id:id};
        $http.post(url, data)
                .then(function (response) {
                    if(!response.data){
                        Toast.showToast('Something went wrong!');
                        return;
                    }
                    if(id){
                        if(id !== 100){
                            $scope.content = response.data['content'] ;
                            $scope.upsellManageTemplate.alert = false;
                            return response.data;
                        }else{
                            $scope.content = "";
                        }
                    }
        $scope.prebulitTemplates = response.data;
         console.log($scope.prebulitTemplates)
        });
    }
    $scope.getTemplates(0);
   
   
    $scope.replaceTeam = function(){
//        var confirm = $mdDialog.confirm()
//                .title('Upsell Manager')
//                .textContent('Are you sure to replace this template?')
//                .ok('Yes')
//                .cancel('No');
//        $mdDialog.show(confirm).then(function () {
//           
//            
//        });
         $scope.upsellManageTemplate.alert = true;
    }
    $scope.upsellManageTemplate.alert = false;
    $scope.cancelPopup = function(){
        $scope.upsellManageTemplate.prebuiltTemplateContent = "100";
        $scope.upsellManageTemplate.alert = false;
    }
    $scope.overrideHtml = function(){
        $scope.getTemplates($scope.upsellManageTemplate.prebuiltTemplateContent);
       // $scope.content = "SDbsbsdsd";
//        var template = $scope.getTemplates($scope.upsellManageTemplate.prebuiltTemplateContent);
       // console.log();
//        $scope.content = template['content']; 
    }
    
});