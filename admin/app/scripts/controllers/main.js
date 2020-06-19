'use strict';
/**
 * @ngdoc function
 * @name codeBaseAdminApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.controller('MainCtrl', function ($scope, $location, $mdSidenav, $mdToast, $rootScope, Dialog, $route, $sce, $http) {
		$scope.counter = 2;
		$scope.selectedIndex = 0; //set 0 index menu active by default



		$scope.linkClick = function () {
			//alert(1);
		}

		$scope.appName = 'Unify Framework';
		$scope.toggleLeft = buildToggler('left');

		function buildToggler(componentId) {
			return function () {
				$mdSidenav(componentId).toggle();
			}
		}
        $scope.menuChoose = function (currentMenu) {

			var currentHref = $location.path().split('/')[1];
			if (currentMenu.hasOwnProperty('href') && (
				currentMenu.href.search(currentHref) !== -1
				|| currentHref.search(currentMenu.href.slice(0, currentMenu.href.length - 1)) !== -1
				)

				) {

				return true;
			}

			return false;
		};

		$scope.menuToggle = function (currentMenu) {
			var currentHref = $location.path().split('/')[1];
                        if(typeof currentHref == "undefined" || currentHref == null || currentHref == '')return;
			if (currentMenu.hasOwnProperty('child_menu')) {
				for (let child of currentMenu.child_menu) {
					if (child.hasOwnProperty('href') && (
						child.href.search(currentHref) !== -1
						|| currentHref.search(child.href.slice(0, child.href.length - 1)) !== -1
						)

						) {
						var currentState = currentMenu.child_open;
						currentMenu.child_open = !currentState;
					}
				}
			}
			return;
		}

		$scope.open = function (state, index, menu) {
			if (menu.hasOwnProperty('child_menu')) {
				menu.child_open = menu.child_open ? false : true;
				return;
			}
			/*menu active*/
			if ($scope.selectedIndex === 0) {
				$scope.selectedIndex = index;
			} else if ($scope.selectedIndex === index) {
				$scope.selectedIndex = index;
			} else {
				$scope.selectedIndex = index;
			}
			/*menu active*/
			$scope.toggleLeft();
			if (state === 'logout') {
				window.location.href = '../logout.php';
            }
            else if(state === 'knowledge_base') {
                window.open('https://framework.unify.to/unify_dev_doc/?token=' + KNOWLEDGE_BASE, '_blank');
            } 
            else {
                
				$location.path(state);
			}
		}

		$scope.isActive = function (view) {
			return view === $location.path();
		}

		// $scope.submenu = SUBMENU_LIST;
		$rootScope.submenu = SUBMENU_LIST;

		$rootScope.$watch('submenu', function(NewValue){
			$scope.submenu = NewValue;
		});

		$scope.helpText = [
            {
                label: 'Dashboard',
                slug: 'dashboard'
            },
            {
                label: 'Campaigns',
                slug: 'campaigns'
            },
            {
                label: 'Funnel Configurations',
                slug: 'configurations'
            }
            ,
            {
                label: 'CRM',
                slug: 'crms'
            },
            {
                label: 'Users',
                slug: 'users'
            },
            {
                label: 'Settings',
                slug: 'settings'
            },
            {
                label: 'CMS',
                slug: 'cms'
            },
            {
                label: 'Pixel Manager',
                slug: 'pixels'
            },
            {
                label: 'Auto Responder',
                slug: 'auto-responder'
            },
            {
                label: 'Scheduler',
                slug: 'scheduler'
            },
            {
                label: 'Extensions',
                slug: 'extensions'
            },
            {
                label: 'Mid Routing',
                slug: 'routing'
            }
            /*,
            {
                label: 'Troubleshooting',
                slug: 'diagnosis'
            },
            {
                label: 'Changelog',
                slug: 'changelog'
            }*/

        ];
        $rootScope.helpAutoFocus = null;
        $scope.showHelpContentDetails = false;
        $scope.searchStyle = 'select-plus helpsrch-display';
        var slug = (($location.$$url).split('/').length >2) ? ($location.$$url).split('/')[1] : 
            ($location.$$url).split('/').pop();
       
       
        $rootScope.helpAutoFocus = slug;
        $scope.showHelp = function ()
        { 
            if($rootScope.helpAutoFocus !== null && $rootScope.helpAutoFocus !== "dashboard"){
                $scope.showContent($rootScope.helpAutoFocus)
            }
            
            $rootScope.showHelpPopUp = true;
            angular.element('body').attr('style', 'overflow:hidden !important');
            //Dialog.showCustomDialog($rootScope.help_page);
        };
        $rootScope.closeHelpPopUp = function () {
            $rootScope.showHelpPopUp = false;
            if (angular.element('body').attr('style') == 'overflow:hidden !important') {
                angular.element('body').removeAttr('style');
            }
            $scope.hideContent();
           // $rootScope.helpAutoFocus = null;
        };
        $scope.markdown_innerHTML = null;
        $scope.showContent = function (path) {
            $scope.markdown_innerHTML = null;
            $http.post('../' + REST_API_PATH + 'dashboard/get-documentation', {
                data: {
                    slug:path
                }
            }).then(function (response) {
                
                response = response.data;
               
                if (response.hasOwnProperty('success')) {
                    $scope.markdown_innerHTML = $sce.trustAsHtml(marked(response.data));
                    // $scope.markdown_innerHTML = $sce.trustAsHtml(markdown.toHTML(response.data));
                } else {
                    $scope.markdown_innerHTML = $sce.trustAsHtml(marked('**404 Not found**'));
                   // $scope.markdown_innerHTML = $sce.trustAsHtml(markdown.toHTML('##404 Not found'));
                }
                 angular.element('.help-content-details').scrollTop(0);
            });

            $scope.chooseHelpContClass = 'help-content-details';
            $scope.showHelpContentDetails = true;
            $scope.searchStyle = 'select-plus helpsrch-not-display';

        }
        $scope.chooseHelpContClass = 'help-content';
        $scope.hideContent = function () {
            $scope.chooseHelpContClass = 'help-content';
            $scope.showHelpContentDetails = false;
            $scope.searchStyle = 'select-plus helpsrch-display';
        };
    });