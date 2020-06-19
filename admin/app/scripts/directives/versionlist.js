'use strict';
/**
 * @ngdoc directive
 * @name codeBaseAdminApp.directive:versionList
 * @description
 * # versionList
 */
angular.module('codeBaseAdminApp')
	.directive('versionList', function ($http, $routeParams, Dialog, $route) {
		var res, path, id, data, url;
		return {
			templateUrl: 'views/version-list.html',
			restrict: 'E',
			link: function postLink(scope, element, attrs) {

				if (attrs.name == 'settings') {
					id = 1;
				}
				else {
					path = location.hash.match(/[0-9]+\/?$/);
					if (path) {
						id = path[0];
					} else {
						return false;
					}
				}

				$http.post('../' + REST_API_PATH + 'versions/get-versions/' + attrs.name)
					.then(function (result) {
						res = result.data;
						if (Object.keys(res).length > 0) {
							scope.versions = true;
						}
						scope.list = result.data;
					});
				scope.restore = function () {
					if (scope.versionSelect == undefined) {
						return false;
					}
					data = {
						name: attrs.name,
						dataId: id,
						db: scope.versionSelect,
					};
					url = '../' + REST_API_PATH + 'versions/restore-version/';
					Dialog.versionConfirmDialog(url, data);
				}
			}
		};
	});
