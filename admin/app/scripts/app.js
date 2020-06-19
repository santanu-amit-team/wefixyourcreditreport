'use strict';
/**
 * @ngdoc overview
 * @name codeBaseAdminApp
 * @description
 * # codeBaseAdminApp
 *
 * Main module of the application.
 */
angular.module('codeBaseAdminApp', ['ngAnimate', 'ngCookies', 'ngResource', 'ngRoute', 'ngSanitize', 'textAngular', 'ngMaterial', 
    'ngclipboard', 'lr.upload', 'oc.lazyLoad', 'moment-picker', 'extensionManagerModule', 'angular-inview', 'btford.markdown'])
    .config(function ($routeProvider, $provide, $mdThemingProvider) {
        $provide.decorator('taOptions', ['taRegisterTool', '$delegate', function (taRegisterTool, taOptions) {
            taRegisterTool('customer_service_number', {
                buttontext: '[customer_service_number]',
                action: function (taRegisterTool, taOptions) {
                    this.$editor().wrapSelection('insertText', '[customer_service_number]');
                }
            });
            taRegisterTool('customer_support_email', {
                buttontext: '[customer_support_email]',
                action: function (taRegisterTool, taOptions) {
                    this.$editor().wrapSelection('insertText', '[customer_support_email]');
                }
            });
            taRegisterTool('hours_of_operation', {
                buttontext: '[hours_of_operation]',
                action: function (taRegisterTool, taOptions) {
                    this.$editor().wrapSelection('insertText', '[hours_of_operation]');
                }
            });
            taRegisterTool('return_address', {
                buttontext: '[return_address]',
                action: function (taRegisterTool, taOptions) {
                    this.$editor().wrapSelection('insertText', '[return_address]');
                }
            });
            taRegisterTool('domain', {
                buttontext: '[domain]',
                action: function (taRegisterTool, taOptions) {
                    this.$editor().wrapSelection('insertText', '[domain]');
                }
            });
            taRegisterTool('corporate_address', {
                buttontext: '[corporate_address]',
                action: function (taRegisterTool, taOptions) {
                    this.$editor().wrapSelection('insertText', '[corporate_address]');
                }
            });

            //token select box
            taRegisterTool('tokens', {
                buttontext: '[tokens]',
                action: function (event, val, taRegisterTool, taOptions) {


                    if(typeof val === 'string') {
                        this.$editor().wrapSelection('insertText', '[[' + val + ']]', true);
                    }
                    
                    return;
                }
            });
            

            taOptions.toolbar = [
                ['h1', 'h2', 'h3'],
                ['bold', 'italics', 'underline', 'html'],
                ['insertImage', 'insertLink', ],
                ['tokens'],
                // ['customer_support_email', 'customer_service_number', 'hours_of_operation', 'return_address', 'domain', 'corporate_address']
            ];
            taOptions.classes = {
                toolbarGroup: 'ta-button-group',
                toolbarButton: '',
                textEditor: 'ta-text-editor',
                htmlEditor: 'md-input'
            };
            return taOptions;
        }]);
    /***********/
    $provide.decorator('taTools', ['$delegate', function (taTools) {

            taTools.tokens.display = `
             
                <md-select class="editor-select" ng-model="valueModel" ng-change="action($event, valueModel)" placeholder="Add Tokens">
                    <md-option ng-value="opt" ng-repeat="opt in ['customer_support_email', 'customer_service_number', 'hours_of_operation', 'return_address', 'domain', 'corporate_address']">[[{{ opt }}]]</md-option>
                </md-select>
            `;
            taTools.customer_support_email.display = '<md-button aria-label="customer support email">[customer_support_email]</md-button>';
            taTools.customer_service_number.display = '<md-button aria-label="customer service number">[customer_service_number]</md-button>';
            taTools.hours_of_operation.display = '<md-button aria-label="hours of operation">[hours_of_operation]</md-button>';
            taTools.return_address.display = '<md-button aria-label="return address">[return_address]</md-button>';
            taTools.domain.display = '<md-button aria-label="domain">[domain]</md-button>';
            taTools.corporate_address.display = '<md-button aria-label="corporate address">[corporate_address]</md-button>';
            taTools.h1.display = '<md-button class="editor-btn" aria-label="Heading 1"><b>&nbsp;H1&nbsp;</b></md-button>';
            taTools.h2.display = '<md-button class="editor-btn" aria-label="Heading 2"><b>&nbsp;H2&nbsp;</b></md-button>';
            taTools.h3.display = '<md-button class="editor-btn" aria-label="Heading 3"><b>&nbsp;H3&nbsp;</b></md-button>';
            taTools.p.display = '<md-button class="editor-btn" aria-label="Paragraph">P</md-button>';
            taTools.pre.display = '<md-button class="editor-btn" aria-label="Pre">pre</md-button>';
            taTools.quote.display = '<md-button class="editor-btn" class="md-icon-button" aria-label="Quote"><md-icon md-font-set="material-icons">format_quote</md-icon></md-button>';
            taTools.bold.display = '<md-button class="editor-btn" aria-label="Bold"><md-icon md-font-set="material-icons">format_bold</md-icon></md-button>';
            taTools.italics.display = '<md-button class="editor-btn" aria-label="Italic"><md-icon md-font-set="material-icons">format_italic</md-icon></md-button>';
            taTools.underline.display = '<md-button class="editor-btn" aria-label="Underline"><md-icon md-font-set="material-icons">format_underlined</md-icon></md-button>';
            taTools.ul.display = '<md-button class="md-icon-button" aria-label="Buletted list"><md-icon md-font-set="material-icons">format_list_bulleted</md-icon></md-button>';
            taTools.ol.display = '<md-button class="md-icon-button" aria-label="Numbered list"><md-icon md-font-set="material-icons">format_list_numbered</md-icon></md-button>';
            taTools.undo.display = '<md-button class="md-icon-button" aria-label="Undo"><md-icon md-font-set="material-icons">undo</md-icon></md-button>';
            taTools.redo.display = '<md-button class="md-icon-button" aria-label="Redo"><md-icon md-font-set="material-icons">redo</md-icon></md-button>';
            taTools.justifyLeft.display = '<md-button class="md-icon-button" aria-label="Align left"><md-icon md-font-set="material-icons">format_align_left</md-icon></md-button>';
            taTools.justifyRight.display = '<md-button class="md-icon-button" aria-label="Align right"><md-icon md-font-set="material-icons">format_align_right</md-icon></md-button>';
            taTools.justifyCenter.display = '<md-button class="md-icon-button" aria-label="Align center"><md-icon md-font-set="material-icons">format_align_center</md-icon></md-button>';
            taTools.justifyFull.display = '<md-button class="md-icon-button" aria-label="Justify"><md-icon md-font-set="material-icons">format_align_justify</md-icon></md-button>';
            taTools.clear.display = '<md-button class="md-icon-button" aria-label="Clear formatting"><md-icon md-font-set="material-icons">format_clear</md-icon></md-button>';
            taTools.html.display = '<md-button class="editor-btn" aria-label="Show HTML"><md-icon md-font-set="material-icons">code</md-icon></md-button>';
            taTools.insertLink.display = '<md-button class="editor-btn" aria-label="Insert link"><md-icon md-font-set="material-icons">insert_link</md-icon></md-button>';
            taTools.insertImage.display = '<md-button class="editor-btn" aria-label="Insert photo"><md-icon md-font-set="material-icons">insert_photo</md-icon></md-button>';
            return taTools;
        }]);
    $routeProvider
    .when('/dashboard', {
        templateUrl: 'views/dashboard.html',
        controller: 'DashboardCtrl',
        controllerAs: 'dashboard',
        title: 'Dashboard'
    })
    .when('/configurations', {
        templateUrl: 'views/configurations.html',
        controller: 'Configurations',
        controllerAs: 'main',
        title: 'Funnel Configurations',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/pixels', {
        templateUrl: 'views/pixels.html',
        controller: 'Pixels',
        controllerAs: 'pixels',
        title: 'Pixel Manager',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/cms', {
        templateUrl: 'views/cms.html',
        controller: 'CmsCtrl',
        controllerAs: 'cms',
        title: 'CMS',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/crons', {
        templateUrl: 'views/crons.html',
        controller: 'CronJobsCtrl',
        controllerAs: 'cronJobs',
        title: 'Scheduler',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/cron-manager',{
        templateUrl: 'views/cron-manager.html',
        controller: 'CronManager',
        controllerAs: 'CronManager',
        title: 'Add New Scheduler',

    }).when('/cron-manager/:id',{
        templateUrl: 'views/cron-manager.html',
        controller: 'CronManager',
        controllerAs: 'CronManager',
        title: 'Edit Scheduler',

    }).when('/configuration-manager', {
        templateUrl: 'views/configuration-manager.html',
        controller: 'ConfigurationManager',
        controllerAs: 'configurationManager',
        title: 'Add New Funnel Configuration',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/configuration-manager/:id', {
        templateUrl: 'views/configuration-manager.html',
        controller: 'ConfigurationManager',
        controllerAs: 'configurationManager',
        title: 'Edit Funnel Configuration',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/settings', {
        templateUrl: 'views/settings.html',
        controller: 'Settings',
        controllerAs: 'settings',
        title: 'Settings',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/pixel-setup', {
        templateUrl: 'views/pixel-setup.html',
        controller: 'PixelSetupCtrl',
        controllerAs: 'pixelSetup',
        title: 'Add New Pixel',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/pixel-setup/:id', {
        templateUrl: 'views/pixel-setup.html',
        controller: 'PixelSetupCtrl',
        controllerAs: 'pixelSetup',
        title: 'Edit Pixel',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/cms-manager', {
        templateUrl: 'views/cms-manager.html',
        controller: 'CmsManagerCtrl',
        controllerAs: 'cmsManager',
        title: 'Add New Content',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/cms-manager/:id', {
        templateUrl: 'views/cms-manager.html',
        controller: 'CmsManagerCtrl',
        controllerAs: 'cmsManager',
        title: 'Edit Content',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/extension-catalogue', {
        templateUrl: 'views/extension-catalogue.html',
        controller: 'ExtensionCatalogueCtrl',
        controllerAs: 'extensionsCatalogue',
        title: 'Extension Catalogue',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/extensions', {
        templateUrl: 'views/extensions.html',
        controller: 'ExtensionsCtrl',
        controllerAs: 'extensions',
        title: 'Extensions',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/extensions/:file_name', {
        templateUrl: 'views/extension-manage.html',
        controller: 'ExtensionsManageCtrl',
        controllerAs: 'extensionsManage',
        title: '',
        resolve: {
            loadMyFiles: function (customJsLoadService) {
                return customJsLoadService.getCustomJS();
            }
        }
    }).when('/coupons', {
        templateUrl: 'views/coupons.html',
        controller: 'Coupons',
        controllerAs: 'coupons',
        title: 'Coupons',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/coupon-manager', {
        templateUrl: 'views/coupon-manager.html',
        controller: 'CouponManager',
        controllerAs: 'couponManager',
        title: 'Add New Coupon',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/coupon-manager/:id', {
        templateUrl: 'views/coupon-manager.html',
        controller: 'CouponManager',
        controllerAs: 'couponManager',
        title: 'Edit Coupon',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/coupon-user/:id', {
        templateUrl: 'views/coupon-user.html',
        controller: 'CouponUser',
        controllerAs: 'couponUser',
        title: 'Coupon Users',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/campaigns', {
        templateUrl: 'views/campaigns.html',
        controller: 'Campaigns',
        controllerAs: 'campaigns',
        title: 'Campaigns',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/campaign-manager', {
        templateUrl: 'views/campaign-manager.html',
        controller: 'CampaignManager',
        controllerAs: 'campaignManager',
        title: 'Add New Campaign',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/campaign-manager/:id', {
        templateUrl: 'views/campaign-manager.html',
        controller: 'CampaignManager',
        controllerAs: 'campaignManager',
        title: 'Edit Campaign',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/crms', {
        templateUrl: 'views/crms.html',
        controller: 'CrmsCtrl',
        controllerAs: 'CrmsCtrl',
        title: 'CRM',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/crms-manager', {
        templateUrl: 'views/crms-configuration.html',
        controller: 'CrmsConfigCtrl',
        controllerAs: 'CrmsConfigCtrl',
        title: 'Add New CRM',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/crms-manager/:id', {
        templateUrl: 'views/crms-configuration.html',
        controller: 'CrmsConfigCtrl',
        controllerAs: 'CrmsConfigCtrl',
        title: 'Edit CRM',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/advanced', {
        templateUrl: 'views/advanced.html',
        controller: 'Advanced',
        controllerAs: 'Advanced',
        title: 'Advanced',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/authentication', {
        templateUrl: 'views/authentication.html',
        controller: 'Authentication',
        controllerAs: 'Authentication',
        title: 'Authentication Required'
    }).when('/rotators', {
        templateUrl: 'views/rotators.html',
        controller: 'Rotators',
        controllerAs: 'rotators',
        title: 'Rotators',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/rotator-manager', {
        templateUrl: 'views/rotator-manager.html',
        controller: 'RotatorManager',
        controllerAs: 'rotatorManager',
        title: 'Rotators » Add',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/rotator-manager/:id', {
        templateUrl: 'views/rotator-manager.html',
        controller: 'RotatorManager',
        controllerAs: 'rotatorManager',
        title: 'Rotators » Edit',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/affiliates', {
        templateUrl: 'views/affiliates.html',
        controller: 'Affiliates',
        controllerAs: 'affiliates',
        title: 'Affiliates',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/affiliate-manager', {
        templateUrl: 'views/affiliate-manager.html',
        controller: 'AffiliateManager',
        controllerAs: 'affiliateManager',
        title: 'Add New Affiliate',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/affiliate-manager/:id', {
        templateUrl: 'views/affiliate-manager.html',
        controller: 'AffiliateManager',
        controllerAs: 'affiliateManager',
        title: 'Edit Affiliate',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/diagnosis', {
        templateUrl: 'views/diagnosis.html',
        controller: 'Diagnosis',
        controllerAs: 'diagnosis',
        title: 'Diagnosis'
    }).when('/users', {
        templateUrl: 'views/users.html',
        controller: 'Users',
        controllerAs: 'users',
        title: 'Users',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/user-manager',{
        templateUrl: 'views/user-manager.html',
        controller: 'UserManager',
        controllerAs: 'UserManager',
        title: 'Add Users',

    }).when('/user-manager/:id',{
        templateUrl: 'views/user-manager.html',
        controller: 'UserManager',
        controllerAs: 'UserManager',
        title: 'Edit Users',

    }).when('/routing', {
        templateUrl: 'views/mid-routing.html',
        controller: 'MidRouting',
        controllerAs: 'midRouting',
        title: 'Mid Routing',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/routing-manager', {
        templateUrl: 'views/mid-routing-manager.html',
        controller: 'MidRoutingManager',
        controllerAs: 'midRoutingManager',
        title: 'Add Mid Routing Configuration',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/routing-manager/:id', {
        templateUrl: 'views/mid-routing-manager.html',
        controller: 'MidRoutingManager',
        controllerAs: 'midRoutingManager',
        title: 'Edit Mid Routing Configuration',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/bin-manager', {
        templateUrl: 'views/bin-manager.html',
        controller: 'BinManager',
        controllerAs: 'binManager',
        title: 'Bin Manager'
    }).when('/admin_logs', {
        templateUrl: 'views/admin-logs.html',
        controller: 'AdminLogs',
        controllerAs: 'adminLogs',
        title: 'Admin Logs'
    }).when('/traffic-monitor', {
        templateUrl: 'views/traffic-monitor.html',
        controller: 'TrafficMonitor',
        controllerAs: 'trafficMonitor',
        title: 'Traffic Monitor'
    }).when('/auto-responder', {
        templateUrl: 'views/autoresponder.html',
        controller: 'AutoResponder',
        controllerAs: 'autoResponder',
        title: 'Auto Responder',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/autoresponder-manager', {
        templateUrl: 'views/autoresponder-manager.html',
        controller: 'AutoresponderManager',
        controllerAs: 'autoresponderManager',
        title: 'Add New Auto Responder',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/autoresponder-manager/:id', {
        templateUrl: 'views/autoresponder-manager.html',
        controller: 'AutoresponderManager',
        controllerAs: 'autoresponderManager',
        title: 'Edit Auto Responder',
        // resolve: {
        //     checkValidSlugPermission: function(customJsLoadService){
        //         return customJsLoadService.checkSlugPermission();
        //     }
        // }
    }).when('/changelog', {
        templateUrl: 'views/change-log.html',
        controller: 'ChangelogManager',
        controllerAs: 'changelogManager',
        title: 'Changelog',
    }).when('/split-test/', {
        templateUrl: 'views/split-test.html',
        controller: 'SplitTest',
        controllerAs: 'splitTest',
        title: 'Split Test'
    }).when('/split-test-manager/', {
        templateUrl: 'views/split-test-manager.html',
        controller: 'SplitTestManager',
        controllerAs: 'splitTestManager',
        title: 'Create New Split Test'
    }).when('/split-test-manager/:id', {
        templateUrl: 'views/split-test-manager.html',
        controller: 'SplitTestManager',
        controllerAs: 'splitTestManager',
        title: 'Edit Split Test'
    }).when('/upsell-manager/', {
        templateUrl: 'views/upsell-manager.html',
        controller: 'UpsellManager',
        controllerAs: 'upsellManager',
        title: 'Upsell Manager'
    }).when('/upsell-manager-configuration/', {
        templateUrl: 'views/upsell-manager-configuration.html',
        controller: 'UpsellManagerConfiguration',
        controllerAs: 'upsellManagerConfiguration',
        title: 'Add Upsell Manager'
    }).when('/upsell-manager-configuration/:id', {
        templateUrl: 'views/upsell-manager-configuration.html',
        controller: 'UpsellManagerConfiguration',
        controllerAs: 'upsellManagerConfiguration',
        title: 'Edit Upsell Manager'
    }).otherwise({
        redirectTo: '/dashboard',
        title: 'dashboard'
    });
    
    $mdThemingProvider.generateThemesOnDemand(true);
    $provide.value('themeProvider', $mdThemingProvider);
}).config(function ($httpProvider, momentPickerProvider) {
    $httpProvider.interceptors.push(function () {
        return {
            'responseError': function (rejection) {
                if (rejection.hasOwnProperty('status') && rejection.status === 401) {
                    window.location.href = '../logout.php';
                }
                return rejection;
            }
        };
    });
    momentPickerProvider.options({
            /* Picker properties */
            locale:        'en',
            format:        'L LTS',
            minView:       'decade',
            maxView:       'minute',
            startView:     'year',
            autoclose:     true,
            today:         false,
            keyboard:      false,
            
            /* Extra: Views properties */
            leftArrow:     '&larr;',
            rightArrow:    '&rarr;',
            yearsFormat:   'YYYY',
            monthsFormat:  'MMM',
            daysFormat:    'D',
            hoursFormat:   'HH:[00]',
            minutesFormat: moment.localeData().longDateFormat('LT').replace(/[aA]/, ''),
            secondsFormat: 'ss',
            minutesStep:   5,
            secondsStep:   1
        });
}).run(function ($rootScope, $location, Hint, Dialog, $http, $route, $window, themeProvider, $mdTheming) {
    $rootScope.cbVersion = CB_VER;
    $rootScope.orderFilterText = 'Order Filter';
    $rootScope.advanced_mode = false;
    $rootScope.goBack = function () {
        history.back();
    };
    $rootScope.issueSections = {};
    $rootScope.concatObject = function (o) {
        if (!o) {
            return 'N/A';
        }
        return JSON.parse(o).join(', ');
    };
    $rootScope.redirectPage = '';
    var controller_obj = {
        DashboardCtrl: {
            'model_name': 'dashboard',
            'help_page_name': 'dashboard',
            'access_allowed': true
        },
        ConfigurationManager: {
            'model_name': 'config',
            'help_page_name': 'configurations',
            'access_allowed': true
        },
        Configurations: {
            'model_name': '',
            'help_page_name': 'configurations',
            'access_allowed': true
        },
        PixelSetupCtrl: {
            'model_name': 'pixel',
            'help_page_name': 'pixels',
            'access_allowed': true
        },
        Pixels: {
            'model_name': '',
            'help_page_name': 'pixels',
            'access_allowed': true
        },
        CmsManagerCtrl: {
            'model_name': 'content',
            'help_page_name': 'cms',
            'access_allowed': true
        },
        CmsCtrl: {
            'model_name': '',
            'help_page_name': 'cms',
            'access_allowed': true
        },
        CampaignManager: {
            'model_name': 'campaign',
            'help_page_name': 'campaigns',
            'access_allowed': true
        },
        Campaigns: {
            'model_name': '',
            'help_page_name': 'campaigns',
            'access_allowed': true
        },
        CrmsConfigCtrl: {
            'model_name': 'crm',
            'help_page_name': 'crms',
            'access_allowed': true
        },
        CrmsCtrl: {
            'model_name': '',
            'help_page_name': 'crms',
            'access_allowed': true
        },
        Settings: {
            'model_name': 'settings',
            'help_page_name': 'settings',
            'access_allowed': true
        },
        Rotators: {
            'model_name': '',
            'help_page_name': 'rotators',
            'access_allowed': false
        },
        RotatorManager: {
            'model_name': 'rotator',
            'help_page_name': 'rotators',
            'access_allowed': true
        },
        Affiliates: {
            'model_name': '',
            'help_page_name': 'affiliates',
            'access_allowed': true
        },
        AffiliateManager: {
            'model_name': 'affiliate',
            'help_page_name': 'affiliates',
            'access_allowed': true
        },
        ExtensionsCtrl: {
            'model_name': '',
            'help_page_name': 'extensions',
            'access_allowed': true
        },
        ExtensionsManageCtrl: {
            'model_name': '',
            'help_page_name': 'extensions',
            'access_allowed': true
        },
        ExtensionCatalogueCtrl: {
            'model_name': 'ExtensionCatalogueCtrl',
            'help_page_name': 'extensions',
            'access_allowed': true
        },
        Advanced: {
            'model_name': 'advanced',
            'help_page_name': 'advanced',
            'access_allowed': false
        },
        CronJobsCtrl: {
            'model_name': '',
            'help_page_name': 'crons',
            'access_allowed': true
        },
        CronManager : {
            'model_name': '',
            'help_page_name': 'crons',
            'access_allowed': true
        },
        MidRouting: {
            'model_name': '',
            'help_page_name': 'routing',
            'access_allowed': true
        },
        MidRoutingManager: {
            'model_name': '',
            'help_page_name': 'routing',
            'access_allowed': true
        },
        Users: {
            'model_name': 'User',
            'help_page_name': 'users',
            'access_allowed': true
        },
        UserManager: {
            'model_name': 'UserManager',
            'help_page_name': 'users',
            'access_allowed': true
        },
        AutoResponder : {
            'model_name': 'AutoResponder',
            'help_page_name': 'auto-responders',
            'access_allowed': true
        },
        AutoresponderManager: {
            'model_name': 'AutoresponderManager',
            'help_page_name': 'auto-responders',
            'access_allowed': true
        }
    };
    
    $rootScope.theme = function (color, text_color){
        if(typeof color == 'undefined'){
                return false;
        }
                var rgb = color;
                var text_rgb = text_color;
                rgb = rgb.replace(/[^\d,]/g, '').split(',');
                rgb = rgb.map(Number);
                text_rgb = text_rgb.replace(/[^\d,]/g, '').split(',');
                text_rgb = text_rgb.map(Number);
                //document.getElementById('custom-styles').innerHTML = '.md-date-range-picker__calendar__selected{ background:' + color + ' !important;}';
                var workingPrimary = {
            "50": {contrast: [0, 0, 0, 0.87], value: rgb},
            "100": {contrast: [0, 0, 0, 0.87], value: rgb},
            "200": {contrast: [0, 0, 0, 0.87], value: rgb},
            "300": {contrast: [0, 0, 0, 0.87], value: rgb},
            "400": {contrast: [0, 0, 0, 0.87], value: rgb},
            "500": {contrast: [255, 255, 255, 0.87], value: rgb},
            "600": {contrast: [255, 255, 255, 0.87], value: rgb},
            "700": {contrast: [255, 255, 255, 0.87], value: rgb},
            "800": {contrast: [255, 255, 255, 0.87], value: rgb},
            "900": {contrast: [255, 255, 255, 0.87], value: rgb},
            "A100": {contrast: [255, 255, 255, 0.87], value: rgb},
            "A200": {contrast: [255, 255, 255, 0.87], value: rgb},
            "A400": {contrast: [255, 255, 255, 0.87], value: rgb},
            "A700": {contrast: [255, 255, 255, 0.87], value: rgb}
                };
                var TextPrimary = {
            "50": {contrast: [0, 0, 0, 0.87], value: text_rgb},
            "100": {contrast: [0, 0, 0, 0.87], value: text_rgb},
            "200": {contrast: [0, 0, 0, 0.87], value: text_rgb},
            "300": {contrast: [0, 0, 0, 0.87], value: text_rgb},
            "400": {contrast: [0, 0, 0, 0.87], value: text_rgb},
            "500": {contrast: [255, 255, 255, 0.87], value: text_rgb},
            "600": {contrast: [255, 255, 255, 0.87], value: text_rgb},
            "700": {contrast: [255, 255, 255, 0.87], value: text_rgb},
            "800": {contrast: [255, 255, 255, 0.87], value: text_rgb},
            "900": {contrast: [255, 255, 255, 0.87], value: text_rgb},
            "A100": {contrast: [255, 255, 255, 0.87], value: text_rgb},
            "A200": {contrast: [255, 255, 255, 0.87], value: text_rgb},
            "A400": {contrast: [255, 255, 255, 0.87], value: text_rgb},
            "A700": {contrast: [255, 255, 255, 0.87], value: text_rgb}
                };
            themeProvider.definePalette('workingPrimary', workingPrimary);
            themeProvider.definePalette('TextPrimary', TextPrimary);
            themeProvider.theme('default')
            .primaryPalette('workingPrimary', {"default": "500"})
            .accentPalette('TextPrimary', {"default": "500"});
            $mdTheming.generateTheme('default');
    }
    $rootScope.theme('92,121,255', '92,121,255');

    $rootScope.$on('$routeChangeStart', function($event, next, current) { 
        // ... you could trigger something here ...
        var slug = ($location.$$url).split("/");

        var url = '../' + REST_API_PATH + 'url-permission/is-valid/' + (slug[1] !== 'undefined' ? slug[1] : 'dashboard');
        $http.get(url).then(function (response) {
                if(!response.data.success)
                    $location.path('/dashboard');

            }).catch(() => {
                $location.path('/dashboard');
            });
        
    });


    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
        if (current.hasOwnProperty('$$route')) {
            $rootScope.page_title = current.$$route.title;
            
            $rootScope.closeHelpPopUp();
              var slug = (($location.$$url).split('/').length >2) ? ($location.$$url).split('/')[1] : 
            ($location.$$url).split('/').pop();
            $rootScope.helpAutoFocus = slug;
    
            var controller = current.$$route.controller;
            //Hint show
            var model_obj_name = controller_obj.hasOwnProperty(controller) ? controller_obj[controller]['model_name'] : '';
            if (model_obj_name != '') {
                Hint.showHint(model_obj_name);
            }
            //Hint show
            //Help show
            $rootScope.help_page = controller_obj.hasOwnProperty(controller) ? controller_obj[controller]['help_page_name'] : '';
            //Help show
            //Access condition
            $rootScope.accessAllowed = controller_obj.hasOwnProperty(controller) ? controller_obj[controller]['access_allowed'] : true;
            if (controller != 'Authentication') {
                if (!$rootScope.accessAllowed && !$rootScope.advanced_mode) {
                    $rootScope.redirectPage = current.$$route.originalPath;
                    $location.path('/authentication');
                } else if (!$rootScope.advanced_mode) {
                    $http.post('../' + REST_API_PATH + 'auth/check-login').then(function (response) {
                        if (response.data.success) {
                            $rootScope.advanced_mode = true;
                        }
                    });
                }
            }
        }
    });
    $rootScope.callHint = function (model_obj_name) {
        Hint.showHint(model_obj_name);
    };
    $rootScope.$on('$routeChangeError', function(event, current, previous, rejection){
        if(rejection === 'NotValidUser'){
            $location.path('/dashboard');
        }
    })

}).filter('html', function ($sce) {
    return function (input) {
        return $sce.trustAsHtml(input);
    }
}).directive("scroll", function ($window) {
    return function (scope, element, attrs) {
        var head = angular.element(document.getElementsByClassName("head-toolbar"));
        element.on("scroll", function () {
            var scrollTop = element.scrollTop();
            if (scrollTop == 0) {
                head.removeClass('dropShadow');
            } else {
                head.addClass('dropShadow');
            }
        });
    };
}).factory("customJsLoadService", function($ocLazyLoad, $http,$q, $timeout, $location){
   return {
        getCustomJS: function(){
            var deferred = $q.defer();
            var slug = ($location.$$url).split('/').pop();
             var url = '../' + REST_API_PATH + 'extensions/get-custom-js-files/'+slug;
                 $http.get(url).then(function (response) {
                     var s = response.data.data;
                     if(typeof s == 'undefined' || s === '' || s === null)
                     {
                         deferred.resolve({});
                     }
                      deferred.resolve($ocLazyLoad.load({
                         name: 'codeBaseAdminApp',
                         files: [
                             '../../extensions/'+slug+'/'+s
                         ]
                     }));
                 });

            return deferred.promise;
        },
        checkSlugPermission: function(){
            var deferred = $q.defer();
            
            var slug = ($location.$$url);
            var url = '../' + REST_API_PATH + 'url-permission/is-valid' + slug;
            $http.get(url).then(function (response) {
                var s = response.data.data;
                if(response.data.success)
                    deferred.resolve();
                else 
                    deferred.reject('NotValidUser') ;
                 
            }).catch(() => {
                deferred.reject('NotValidUser') ;
            });

            return deferred.promise;
        }
    }
   });
