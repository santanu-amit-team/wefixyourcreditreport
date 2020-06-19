'use strict';

/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:CmsCtrl
 * @description
 * # CmsCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('CmsCtrl', function ($scope, $http, $location, Dialog, $filter, $window, Toast) {

		$scope.lists = {};
		$scope.dataLoadComplete = false;
		$scope.cmstypes = ['draft', 'published'];
	    $scope.filterActions = [{key : 'delete', val : 'Delete', method : 'deleteCms'}];
	    $scope.filterTypes = [{key : 'all', val : 'All'}];
	    $scope.selectedType = 'all';
	    $scope.listIds = [];
	    $scope.statusArray = {'draft': 'Draft', 'published': 'Published'};

		$scope.editCms = function (id) {
			$location.path('/cms-manager/' + id);
		};

		$scope.deleteCms = function (id) {
			var url = '';
            if(id.length > 1){
                url = '../' + REST_API_PATH + 'cms/delete';
                Dialog.showDialog(url, '', {ids : JSON.parse(JSON.stringify(id))});
            }
            else{
                url = '../' + REST_API_PATH + 'cms/delete/' + id;
                Dialog.showDialog(url, '', '');
            }
			// Dialog.showDialog('../' + REST_API_PATH + 'cms/delete/' + id);
		}

		$scope.loadTypes = function(){
            angular.forEach($scope.cmstypes, function (type, index) {
                $scope.filterTypes.push({key: type, val : $filter('ucfirst')(type)});
            });
        }
        $scope.loadTypes();
        $scope.getListIds = function(){
            angular.forEach($scope.lists, function (item, index) {
                $scope.listIds.push(item.id);
            });
        }

		$http.post('../' + REST_API_PATH + 'cms/all/')
			.success(function (response) {
				$scope.lists = response.data;
				$scope.fullList = response.data;
				$scope.getListIds();
				if($scope.lists.length > 0){
                    $scope.filterTypes[0].val = 'All (' + Object.keys($scope.lists).length + ')';
                }
				$scope.dataLoadComplete = true;
			});
		$scope.preview = function(data){
			$window.open('../backend/preview.php?id=' + data.id + '&slug=' + data.content_slug);
		}


		$scope.copyToClip = function (content) {
			try {

				// create temp element
				var copyElement = document.createElement("span");
				copyElement.appendChild(document.createTextNode(content));
				copyElement.id = 'tempCopyToClipboard';
				angular.element(document.body.append(copyElement));

				// select the text
				var range = document.createRange();
				range.selectNode(copyElement);
				window.getSelection().removeAllRanges();
				window.getSelection().addRange(range);

				// copy & cleanup
				document.execCommand('copy');
				window.getSelection().removeAllRanges();
				copyElement.remove();

				Toast.showToast('Public URL copied to clipboard.');
			}
			catch(err) {

			}
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


	});
