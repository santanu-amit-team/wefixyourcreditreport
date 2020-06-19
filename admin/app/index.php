<?php
require_once '../../library/bootstrap.php';
Bootstrap::initialize('admin');

use Admin\Library\Auth;
use Application\Session;
use Application\Registry;
use Application\Helper\Provider;
use Application\Request;
use Application\Config;
use Application\Helper\Security;

$docUrlParams = base64_encode(   Request::getOfferUrl() . 'admin/service.php/auth/is-docs-valid' . '|' . Session::getSessionId() );

$menu_widget = array(

    "dashboard" => array(
        'widget' => array(
            'name' => 'Dashboard',
            'icon' => 'dashboard',
            'href' => 'dashboard',
            'show' => true,
        )
    ),
    "change_access_permissions" => array(
        'widget' => null,
    ),
    "ecommerce" => array(
        'widget' => array(
            'name' => 'Ecommerce',
            'icon' => 'shopping_cart',
            'child_open' => false,
            'show' => true,
        )
    ),
    "campaigns" => array(
        'parent_menu' => 'ecommerce',
        'widget' => array(
            'name' => 'Campaigns',
            'icon' => 'flag',
            'href' => 'campaigns',
            'show' => true,
        )
    ),
    "funnel_configurations" => array(
        'parent_menu' => 'ecommerce',
        'widget' => array(
            'name' => 'Funnel Configurations',
            'icon' => 'build',
            'href' => 'configurations',
            'show' => true,
        )
    ),
    "coupons" => array(
        'parent_menu' => 'ecommerce',
        'widget' => array(
            'name' => 'Coupons',
            'icon' => 'card_giftcard',
            'href' => 'coupons',
            'show' => true,
            'ext' => 'Coupons'
        ),
        'extension' => array(
            'needTocheck' => true,
            'extensionUrlSlug' => 'Coupons'
        )
    ),
    "cms" => array(
        'widget' => array(
            'name' => 'CMS',
            'icon' => 'web',
            'href' => 'cms',
            'show' => true,
        )
    ),
    "extensions" => array(
        'widget' => array(
            'name' => 'Extensions',
            'icon' => 'widgets',
            'href' => 'extensions',
            'show' => true,
        )
    ),
    "logs" => array(
        'widget' => array(
            'name' => 'Logs',
            'icon' => 'description',
            'child_open' => false,
            'show' => true,
        )
    ),
    "systems_log" => array(
        'parent_menu' => 'logs',
        'widget' => array(
            'name' => 'System Log',
            'icon' => 'subject',
            'href' => 'admin_logs',
            'show' => false,
        )
    ),
    "user_activity" => array(
        'parent_menu' => 'logs',
        'widget' => array(
            'name' => 'User Activity',
            'icon' => 'assessment',
            'href' => 'admin_logs',
            'show' => true,
            'ext' => 'AdminLogs'
        ),
        'extension' => array(
            'needTocheck' => true,
            'extensionUrlSlug' => 'AdminLogs'
        )
    ),
    "change_log" => array(
        'parent_menu' => 'logs',
        'widget' => array(
            'name' => 'Changelog',
            'icon' => 'list_alt',
            'href' => 'changelog',
            'show' => true,
        )
    ),
    "system" => array(
        'widget' => array(
            'name' => 'System',
            'icon' => 'desktop_windows',
            'child_open' => false,
            'show' => true,
        )
    ),
    "crm" => array(
        'parent_menu' => 'system',
        'widget' => array(
            'name' => 'CRM',
            'icon' => 'timeline',
            'href' => 'crms',
            'show' => true,
        )
    ),
    "users" => array(
        'parent_menu' => 'system',
        'widget' => array(
            'name' => 'Users',
            'icon' => 'supervisor_account',
            'href' => 'users',
            'show' => true,
        )
    ),
    "settings" => array(
        'parent_menu' => 'system',
        'widget' => array(
            'name' => 'Settings',
            'icon' => 'settings',
            'href' => 'settings',
            'show' => true,
        )
    ),
    // "advance_settings" => array(
    //     'parent_menu' => 'system',
    //     'widget' => array(
    //         'name' => 'Advanced Settings',
    //         'icon' => 'more_horiz',
    //         'href' => 'advanced',
    //         'show' => true,
    //     )
    // ),
    "tools" => array(
        'widget' => array(
            'name' => 'Tools',
            'icon' => 'work',
            'child_open' => false,
            'show' => true,
        )
    ),
    "affiliate_manager" => array(
        'parent_menu' => 'tools',
        'widget' => array(
            'name' => 'Affiliate Manager',
            'icon' => 'merge_type',
            'href' => 'affiliates',
            'ext' => 'Affiliates',
            'show' => true,
        ),
        'extension' => array(
            'needTocheck' => true,
            'extensionUrlSlug' => 'Affiliates'
        )
    ),
    "pixel_manager" => array(
        'parent_menu' => 'tools',
        'widget' => array(
            'name' => 'Pixel Manager',
            'icon' => 'track_changes',
            'href' => 'pixels',
            'show' => true,
        )
    ),
    // "rotators" => array(
    //     'parent_menu' => 'tools',
    //     'widget' => array(
    //         'name' => 'Rotators',
    //         'icon' => 'crop_rotate',
    //         'href' => 'rotators',
    //         'show' => true,
    //     )
    // ),
    "mid_routing" => array(
        'parent_menu' => 'tools',
        'widget' => array(
            'name' => 'Mid Routing',
            'icon' => 'call_split',
            'href' => 'routing',
            'ext' => 'MidsRouting',
            'show' => true,
        ),
        'extension' => array(
            'needTocheck' => true,
            'extensionUrlSlug' => 'MidsRouting'
        )
    ),
    "traffic_monitor" => array(
        'parent_menu' => 'tools',
        'widget' => array(
            'name' => 'Traffic Monitor',
            'icon' => 'traffic',
            'href' => 'traffic-monitor',
            'ext' => 'TrafficMonitor',
            'show' => true,
        ),
        'extension' => array(
            'needTocheck' => true,
            'extensionUrlSlug' => 'TrafficMonitor'
        )
    ),
    
    "upsell_manager" => array(
        'parent_menu' => 'tools',
        'widget' => array(
            'name' => 'Upsell Manager',
            'icon' => 'add_to_queue',
            'href' => 'upsell-manager',
            'ext' => 'UpsellManager',
            'show' => true,
        ),
        'extension' => array(
            'needTocheck' => true,
            'extensionUrlSlug' => 'UpsellManager'
        )
    ),
    "auto_responder" => array(
        'parent_menu' => 'tools',
        'widget' => array(
            'name' => 'Auto Responder',
            'icon' => 'rotate_left',
            'href' => 'auto-responder',
            'show' => true,
        )
    ),
    "auto_filters" => null,
    "scheduler" => array(
        'parent_menu' => 'tools',
        'widget' => array(
            'name' => 'Scheduler',
            'icon' => 'alarm',
            'href' => 'crons',
            'show' => true,
        )
    ),
    "diagnosis" => array(
        'parent_menu' => 'tools',
        'widget' => array(
            'name' => 'Troubleshooting',
            'icon' => 'bug_report',
            'href' => 'diagnosis',
            'show' => true,
        )
    ),
    "split_test" => array(
        'parent_menu' => 'tools',
        'widget' => array(
            'name' => 'Split Test',
            'icon' => 'crop_rotate',
            'href' => 'split-test',
            'show' => true,
            'ext' => 'SplitTest',
        ),
        'extension' => array(
            'needTocheck' => true,
            'extensionUrlSlug' => 'SplitTest'
        )
    ),
    "knowledge_base" => array(
        'widget' =>  array(
            'name' => 'Knowledge Base',
            'icon' => 'book',
            'href' => 'knowledge_base',
            'show' => true,
        )
    ),
    "logout" => array(
        'widget' =>  array(
            'name' => 'Logout',
            'icon' => 'power_settings_new',
            'href' => 'logout',
            'show' => true,
        )
    )
);
if( ini_get('allow_url_fopen') ) {
    $googleAdManagerDetails = file_get_contents("https://framework.unify.to/advertisement/info.json");
    $googleAdManagerDetails = json_decode($googleAdManagerDetails, TRUE);
}

$retirctWidgetIfLicenseKeyNotValid = array (
    'logs', 'auto_responder', 'troubleshooting', 'scheduler'
);

$dev_menu_schema = array(
    "change_access_permissions" => true,
        "ecommerce" => true,
        "campaigns" => true,
        "funnel_configurations" => true,
        "coupons" => true,
        "system" => true,
        "crm" => true,
        "users" => true,
        "settings" => true,
        "cms" => true,
        "tools" => true,
        "affiliate_manager" => true,
        "pixel_manager" => true,
        "rotators" => true,
        "mid_routing" => true,
        "traffic_monitor" => true,
        "upsell_manager" => true,
        "auto_responder" => true,
        "auto_filters" => true,
        "scheduler" => true,
        "diagnosis" => true,
        "split_test" => true,
        "extensions" => true,
        "logs" => true,
        "systems_log" => true,
        "user_activity" => true,
        "change_log" => true ,
        "advance_settings" => true
);

if (Auth::isLoginAuthorized())
{
    $validMenus = Session::get('access_urls');

    if(!is_array($validMenus))
        $validMenus = $dev_menu_schema;

    $validLicense = Security::isValidLicenseKey(Config::settings('domain'), Config::settings('license_key'), Config::settings('unify_authentication_key'));

    if (!$validLicense) {
        // Remove the Restricted url if license key are not valid.
        foreach($retirctWidgetIfLicenseKeyNotValid as $restrictUrl) {
            if(array_key_exists($restrictUrl, $validMenus)){}
                unset($validMenus[$restrictUrl]);
        }
    }

    $submenu_list = Array();

    if( !strncmp(Session::get('userType'), 'developer', strlen('developer'))){

        //this is developer login
        foreach( $validMenus as $widget => $menu) {

            if ( $menu && array_key_exists($widget, $menu_widget)){
                
                if(is_array($menu_widget[$widget]) && !is_null($menu_widget[$widget]['widget']) && !array_key_exists('parent_menu', $menu_widget[$widget])) {
                    array_push($submenu_list, $menu_widget[$widget]['widget']);
                }
                else if(is_array($menu_widget[$widget]) && array_key_exists('parent_menu', $menu_widget[$widget])) {

                    // Check if extension based menu or not and extension installed or not;
                    if(array_key_exists('extension', $menu_widget[$widget]) && is_array($menu_widget[$widget]['extension']) && $menu_widget[$widget]['extension']['needTocheck'] && !Provider::checkExtensions($menu_widget[$widget]['extension']['extensionUrlSlug'])) {
                        $menu_widget[$widget]['widget']['show'] = false;                        
                    }

                    //get the index 
                    if(in_array($menu_widget[$widget]['parent_menu'], array_map('strtolower', array_column($submenu_list, 'name'))) ){
                    
                        $correctPosition =  array_search(strtolower($menu_widget[$widget]['parent_menu']), array_map('strtolower', array_column($submenu_list, 'name')));
                        $submenu_list[$correctPosition]['child_menu'][] = $menu_widget[$widget]['widget'];
                    }
                }
                else {
                    continue;
                }
            }    
        }
    }
    else {
        
        // if(!$validMenus['change_access_permissions']) {
        //     $validMenus = array(
        //         'change_access_permissions' => false,
        //         'ecommerce' => true,
        //         'campaigns' => true,
        //         'funnel_configurations' => true,
        //         'coupons' => true,
        //         'system' => true,
        //         'crm' => true,
        //         'users' => false,
        //         'settings' => true,
        //         'cms' => true,
        //         'tools' => true,
        //         'affiliate_manager' => false,
        //         'pixel_manager' => true,
        //         'rotators' => false,
        //         'mid_routing' => false,
        //         'traffic_monitor' => false,
        //         'auto_responder' => false,
        //         'auto_filters' => false,
        //         'scheduler' => false,
        //         'diagnosis' => false,
        //         'extensions' => false,
        //         'logs' => false,
        //         'systems_log' => false,
        //         'user_activity' => false,
        //         'change_log' => false,
        //         'advance_settings' => false,
        //     ); 
        // }
       
        //other user login
        foreach( $validMenus as $widget => $menu) {

            if ( $menu && array_key_exists($widget, $menu_widget)){
                if(is_array($menu_widget[$widget]) && !is_null($menu_widget[$widget]['widget']) && !array_key_exists('parent_menu', $menu_widget[$widget])) {
                    array_push($submenu_list, $menu_widget[$widget]['widget']);
                }
                else if(is_array($menu_widget[$widget]) && array_key_exists('parent_menu', $menu_widget[$widget])) {

                    // Check if extension based menu or not and extension installed or not;
                    if(array_key_exists('extension', $menu_widget[$widget]) && is_array($menu_widget[$widget]['extension']) && $menu_widget[$widget]['extension']['needTocheck'] && !Provider::checkExtensions($menu_widget[$widget]['extension']['extensionUrlSlug'])) {
                        $menu_widget[$widget]['widget']['show'] = false;
                    }

                    //get the index 
                    if(in_array($menu_widget[$widget]['parent_menu'], array_map('strtolower', array_column($submenu_list, 'name'))) ){
                    
                        $correctPosition =  array_search(strtolower($menu_widget[$widget]['parent_menu']), array_map('strtolower', array_column($submenu_list, 'name')));
                        $submenu_list[$correctPosition]['child_menu'][] = $menu_widget[$widget]['widget'];
                    }
                }
                else {
                    continue;
                }
            }    
        }
    }


    array_unshift($submenu_list, $menu_widget['dashboard']['widget']);
    if( !strncmp(Session::get('userType'), 'developer', strlen('developer'))){
        array_push($submenu_list, $menu_widget['knowledge_base']['widget']);
    }
    array_push($submenu_list, $menu_widget['logout']['widget']);

    //remove in blank index
    $submenu_list = array_filter($submenu_list, function($value) { return is_array($value); });

    //remove those parent who does not have any child menu.
    $submenu_list = array_filter($submenu_list, function($value) { return array_key_exists('child_menu', $value) || array_key_exists('href', $value); });


    // echo "<pre>";
    // print_r($submenu_list);
    // //die();
    ?>
    <!doctype html>
    <html>
        <head>
            <meta charset="utf-8">
            <title>Admin</title>
            <meta name="description" content="">
            <meta name="viewport" content="width=device-width">
            <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
            <!-- build:css(.) styles/vendor.css -->

            <!-- bower:css -->

            <link rel='stylesheet' href='bower_components/font-awesome/css/font-awesome.min.css'>
            <link rel='stylesheet' href='bower_components/textAngular/dist/textAngular.css'>
            <!-- endbower -->
            <!-- endbuild -->
            <link href="bower_components/angular-moment-picker-master/dist/angular-moment-picker.min.css" rel="stylesheet">
            <!-- build:css(.tmp) styles/main.css -->
            <link rel="stylesheet" href="styles/main.css">
            <link rel="stylesheet" href="styles/material-icon.css">
            <!--Font-->
            <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
                  rel="stylesheet">
            <!-- Angular Material style sheet -->
            <link rel="stylesheet" href="bower_components/angular-material/angular-material.min.css">
            <!-- <link rel="stylesheet" href="bower_components/angular-material-multilevel-menu/angular-material-multilevel-menu.min.css" /> -->
             <!-- Custome css -->
            <!-- Custom scroll bar CSS -->
            <link rel="stylesheet" href="https://unpkg.com/simplebar@latest/dist/simplebar.css" />

            <link rel="stylesheet" href="styles/custom-style.css">
            <link rel="stylesheet" href="styles/new-style.css">
            <link rel="stylesheet" href="styles/animate.css">
            <style type="text/css">
                .ctrl-btns .md-icon-button {}
                [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
                    display: none !important;
                }
                .pull-right{
                    text-align: right;
                }
                .zoom-body {
                    /*zoom: 90%;*/
                    /*background: red;*/
                }
                .f1f1f1 {
                    background: #f1f1f1;
                }

                /* ngIf animation */
                .animationIf.ng-enter,
                .animationIf.ng-leave {
                    -webkit-transition: height linear 300ms;
                    -moz-transition: height linear 300ms;
                    -ms-transition: height linear 300ms;
                    -o-transition: height linear 300ms;
                    transition: height linear 300ms;
                    overflow-y: hidden;  
                }
                .animationIf.ng-enter,
                .animationIf.ng-leave.ng-leave-active {
                    /*opacity: 0;*/
                    height: 0px;
                }
                .animationIf.ng-leave,
                .animationIf.ng-enter.ng-enter-active {
                    /*opacity: 1;*/
                    height: 47px;
                }

                .arrow-down{
                    transform: rotate(0deg);
                    transition: transform 300ms linear;
                }

                .arrow-down.open{
                    transform: rotate(180deg);
                    transition: transform 300ms linear;
                }
                .helpsrch-display{display:block;}
                .helpsrch-not-display{display:none;}
            </style>
            <!-- endbuild -->

            <?php
            printf(
                '<script> var REST_API_PATH = "%s"; var AJAX_PATH = "%s"; var CB_VER = "%s" ; var OFFER_PATH = "%s" ; var IS_NGINX = "%s" ; </script>', REST_API_PATH, AJAX_PATH, Registry::system('systemConstants.version'), Request::getOfferUrl(), (bool)Bootstrap::isNginxServer()
            );

            ?>
            <?php
            printf(
                '<script type="text/javascript">var SUBMENU_LIST = %s;</script>', json_encode($submenu_list)
            );

            ?>

            <?php
                printf(
                    '<script type="text/javascript">var KNOWLEDGE_BASE = "%s";</script>', $docUrlParams
                );
            ?>

            <?php
                // Google Ad Manager Details

                if(!empty($googleAdManagerDetails)) {
                    printf(
                        '<script type="text/javascript">var GoogleAdManagerId = "%s";</script>', $googleAdManagerDetails['id']
                    );
    
                    printf(
                        '<script type="text/javascript">var GoogleAdManagerSlot = "%s";</script>', $googleAdManagerDetails['slot']
                    );
                    printf(
                        '<script type="text/javascript">var AdImgLink = "%s";</script>', $googleAdManagerDetails['img_link']
                    );
                    printf(
                        '<script type="text/javascript">var AdImgSrc = "%s";</script>', $googleAdManagerDetails['img_src']
                    );
                }
                
            ?>

            <!-- Google Ad manager -->
            <script type="application/javascript">
                function loadAdManager(callback) {
                    // DOM: Create the script element
                    var jsElm = document.createElement("script");
                    // set the type attribute
                    jsElm.type = "application/javascript";
                    // make the script element load file
                    jsElm.src = "https://securepubads.g.doubleclick.net/tag/js/gpt.js";
                    
                    jsElm.addEventListener('load', callback);
                    
                    // finally insert the element to the body element in order to load the script
                    document.body.appendChild(jsElm);                    
                }
            </script>
        </head>
        <body ng-app="codeBaseAdminApp" ng-controller="MainCtrl" class="zoom-body f1f1f1"  ng-cloak>
            <!--[if lte IE 8]>
              <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
            <![endif]-->

            <!---sidebar-->
        <md-sidenav class="md-sidenav-left md-whiteframe-z2"
                    md-component-id="left">
            <md-toolbar>
                <div class="md-toolbar-tools toolbar-style">
                    <h2>
                        <span>Menu</span>
                    </h2>
                    <span flex></span>
                    <md-button class="md-icon-button" aria-label="Close Side Panel" ng-click="toggleLeft()">
                        <md-icon class="md-default-theme" class="material-icons">&#xE5CD;</md-icon>
                    </md-button>
                </div>
            </md-toolbar>
            <md-content>
                <md-list>
                    <div ng-repeat="menu in submenu" ng-if="menu.show">
                        <md-list-item
                            class="md-2-line menu-list {{menu.name == 'Advanced' ? 'bt-1':''}}" ng-click="open(menu.href, $index, menu)" ng-class="{selectedIndex: (menuChoose(menu))}" ng-init="menuToggle(menu)">
                            <i class="material-icons menu-icon">{{menu.icon}}</i>
                            <div class="md-list-item-text">
                                <h3 class="menu-text-h3">{{menu.name}}</h3>
                            </div>
                            <i ng-class="{open:menu.child_open}" class="material-icons arrow-down" ng-if="menu.hasOwnProperty('child_menu')">arrow_drop_down</i>
                        </md-list-item>

                        <div ng-if="menu.child_open && menu_child.show" class="child-menu animationIf"  ng-repeat="menu_child in menu.child_menu" ng-if="menu.hasOwnProperty('child_menu') && ((menu.name == 'Advanced' && advanced_mode == 1) || menu.name != 'Advanced')">
                            <md-list-item
                                class="md-2-line menu-list" ng-click="open(menu_child.href, menu.child_menu.length + $index, menu_child)" ng-class="{selectedIndex: (menuChoose(menu_child))}">
                                <i class="material-icons menu-icon child-menu-txt">{{menu_child.icon}}</i>
                                <div class="md-list-item-text">
                                    <h3 class="menu-text-h3 child-menu-txt">{{menu_child.name}}</h3>
                                </div>
                            </md-list-item>
                        </div>
                    </div>
                </md-list>
            </md-content>
        </md-sidenav>

        <!---->
        <div layout="column" style="height: 100%" id="container">


            <!-- help-popup -->


            <div class="help-cell" ng-draggable='dragOptions' ng-if="showHelpPopUp">
                <div class="help-cell-heading">
                    <div class="select-plus">
                        <i style="float:left;color:#ffffff;" ng-click="hideContent()" style="cursor: pointer" ng-mousedown="$event.stopPropagation()" ng-mouseup="$event.stopPropagation()" ng-if="showHelpContentDetails" class="material-icons">arrow_back &nbsp;</i><h2>Help</h2>
                        <md-button class="md-icon-button close-helpdesk" ng-mousedown="$event.stopPropagation()" ng-mouseup="$event.stopPropagation()" ng-click="closeHelpPopUp()">
                            <i class="material-icons">close</i>
                        </md-button>
                    </div>
                    <div ng-class="searchStyle" ng-mousedown="$event.stopPropagation()" ng-mouseup="$event.stopPropagation()">
                        <input class="search-help" ng-model="helpSearchText" type="text" placeholder="Search Help" />
                        <md-button class="md-icon-button" aria-label="Add" ng-click="addMethod(addInput)">
                            <i class="material-icons">search</i>
                        </md-button>
                    </div>

                </div>
                <div ng-class="chooseHelpContClass" ng-mousedown="$event.stopPropagation()" ng-mouseup="$event.stopPropagation()">



                    <ul class="help-listing"  ng-if="!showHelpContentDetails">
                        <li ng-repeat="txt in helpText| filter:{label: helpSearchText}">
                            <a href="javascript:void(0);" ng-click="showContent(txt.slug)">    
                                <i class="material-icons">description</i>                
                                {{txt.label}}
                            </a>
                        </li>

                    </ul>
                    <div layout="row" ng-if="showHelpContentDetails">
                        <div class="" flex ng-bind-html="markdown_innerHTML"></div>
                        <div ng-if="!markdown_innerHTML || markdown_innerHTML == null" layout="row" layout-sm="column" layout-align="center center" class="loader-feed-iframe" style="min-height: 280px;top:111px;">
                                <md-progress-circular md-mode="indeterminate"></md-progress-circular>
                        </div>
                    </div>

                    <a ng-if="!showHelpContentDetails || (showHelpContentDetails && markdown_innerHTML)" href="https://framework.unify.to/unify_help_doc/?token=<?php echo $docUrlParams;?>" target="_blank" class="all-article">Browse All Articles <i class="material-icons">open_in_new</i></a>

                </div>
            </div>

            <!-- help-popup -->


            <md-toolbar class="head-toolbar">
                <div layout="row" flex layout-align="center center">
                    <div flex="50">
                        <div class="md-toolbar-tools">

                            <md-button class="md-icon-button" aria-label="Side Panel" ng-click="toggleLeft()">
                                <md-icon class="md-default-theme" class="material-icons">&#xE5D2;</md-icon>
                            </md-button>
                            <h2>{{page_title}}</h2>

                        </div>
                    </div>
                    <div flex="60" layout="row" layout-align="end center">
                        <md-button class="md-icon-button" ng-click="showHelp();" ng-if="help_page" areia-label="Help"><i class="material-icons">&#xE887;</i><md-tooltip md-direction="left">Help</md-tooltip></md-button>
                    </div>
                    <div flex="20"></div>
                </div>
            </md-toolbar>

            <md-content  class="f1f1f1" scroll>
                <div layout="row" layout-align="center center">
                    <div ng-view flex="60"></div>
                </div>
                <div layout="row" layout-align="center" layout-padding>
                    <br><br>
                    <div>Created with <span style="color: #e81224">❤</span> by <a target="_blank" href="https://www.codeclouds.com/">CodeClouds</a></div>
                </div>
            </md-content>
        </div>

        <!-- build:js(.) scripts/vendor.js -->
        <!-- bower:js -->
        <script src="bower_components/jquery/dist/jquery.min.js"></script>
        <script src="bower_components/angular/angular.min.js"></script>
        <script src="bower_components/angular-animate/angular-animate.min.js"></script>
        <script src="bower_components/angular-cookies/angular-cookies.min.js"></script>
        <script src="bower_components/angular-resource/angular-resource.min.js"></script>
        <script src="bower_components/angular-route/angular-route.min.js"></script>
        <script src="bower_components/angular-sanitize/angular-sanitize.min.js"></script>
        <script src='bower_components/textAngular/dist/textAngular-rangy.min.js'></script>
        <script src='bower_components/textAngular/dist/textAngular-sanitize.min.js'></script>
        <script src='bower_components/textAngular/dist/textAngular.min.js'></script>
        <!-- <script src='bower_components/textAngular/dist/textAngular.min.js'></script> -->
        <script src='bower_components/ngclipboard-master/dist/clipboard.min.js'></script>
        <script src='bower_components/ngclipboard-master/dist/ngclipboard.js'></script>
        <script src='bower_components/angular-upload.min.js'></script>
        <!--angularjs-material-->
        <script src="bower_components/angular-material/angular-material.min.js"></script>
        <!-- <script src="bower_components/a0-angular-storage/dist/angular-storage.min.js"></script> -->
        <script src="bower_components/angular-aria/angular-aria.min.js"></script>
        <!-- <script src="bower_components/angular-material-multilevel-menu/angular-material-multilevel-menu.min.js"></script> -->
        <!-- <script src="bower_components/angular-messages/angular-messages.min.js"></script> -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment-with-locales.js"></script>
        <script src="bower_components/angular-moment-picker-master/dist/angular-moment-picker.min.js"></script>
        <script src="bower_components/showdown/showdown.js"></script>
        <script src="bower_components/angular-markdown-directive-master/markdown.js"></script>

        <!-- endbower -->
        <!-- endbuild -->

        <!-- build:js({.tmp,app}) scripts/scripts.js -->
        <script src="scripts/app.js"></script>
        <script src="scripts/drag.js"></script>
        <script src="scripts/extension-manager/extensionModule.js"></script>

        <script src="scripts/hint-text.js"></script>
        <script src="scripts/controllers/crms.js"></script>
        <script src="scripts/controllers/crms-configuration.js"></script>
        <script src="scripts/controllers/configurations.js"></script>
        <script src="scripts/controllers/pixels.js"></script>
        <script src="scripts/controllers/campaign-manager.js"></script>
        <script src="scripts/controllers/coupons.js"></script>
        <script src="scripts/controllers/coupon-manager.js"></script>
        <script src="scripts/controllers/coupon-user.js"></script>
        <script src="scripts/controllers/campaigns.js"></script>
        <script src="scripts/controllers/cms.js"></script>
        <script src="scripts/controllers/cms-manager.js"></script>
        <script src="scripts/controllers/crons.js"></script>
        <script src="scripts/controllers/cron-manager.js"></script>
        <script src="scripts/controllers/main.js"></script>
        <script src="scripts/controllers/configuration-manager.js"></script>
        <script src="scripts/controllers/dashboard.js"></script>
        <script src="scripts/controllers/pixel-setup.js"></script>
        <script src="scripts/controllers/extension-catalogue.js"></script>
        <script src="scripts/controllers/extensions.js"></script>
        <script src="scripts/controllers/extension-manage.js"></script>
        <script src="scripts/controllers/user-management.js"></script>
        <script src="scripts/controllers/users.js"></script>
        <script src="scripts/controllers/user-manager.js"></script>
        <script src="scripts/filters/ucfirst.js"></script>
        <script src="scripts/filters/capitalizeword.js"></script>
        <script src="scripts/controllers/settings.js"></script>
        <script src="scripts/directives/extensioninputfield.js"></script>
        <script src="scripts/directives/extensioncustomview.js"></script>
        <script src="scripts/directives/formgroup.js"></script>
        <script src="scripts/services/settings.js"></script>
        <script src="scripts/services/toast.js"></script>
        <script src="scripts/services/menu.js"></script>
        <script src="scripts/filters/changecase.js"></script>
        <script src="scripts/filters/filterbycolumn.js"></script>
        <script src="scripts/filters/strreplace.js"></script>
        <script src="scripts/services/dialog.js"></script>
        <script src="scripts/directives/no-data-text.js"></script>
        <script src="scripts/directives/versionlist.js"></script>
        <script src="scripts/directives/addremoveinput.js"></script>
        <script src="scripts/directives/filterbar.js"></script>
        <script src="scripts/directives/flat-button-directive.js"></script>
        <script src="scripts/services/hint.js"></script>
        <script src="scripts/controllers/authentication.js"></script>
        <script src="scripts/controllers/advanced.js"></script>
        <script src="scripts/controllers/rotators.js"></script>
        <script src="scripts/controllers/rotator-manager.js"></script>
        <script src="scripts/controllers/affiliates.js"></script>
        <script src="scripts/controllers/affiliate-manager.js"></script>
        <script src="scripts/controllers/diagnosis.js"></script>
        <script src="scripts/controllers/mid-routing.js"></script>
        <script src="scripts/controllers/mid-routing-manager.js"></script>
        <script src="scripts/directives/file-upload.js"></script>
        <!-- <script src="scripts/controllers/bin-manager.js"></script> -->
        <script src="scripts/controllers/traffic-monitor.js"></script>
        <script src="scripts/controllers/autoresponder.js"></script>
        <script src="scripts/controllers/autoresponder-manager.js"></script>
        <script src="scripts/controllers/split-test.js"></script>
        <script src="scripts/controllers/split-test-manager.js"></script>
        <script src="scripts/controllers/admin-logs.js"></script>
        <script src="scripts/ocLazyLoad.js"></script>
        <script src="scripts/services/crms.js"></script>
        <script src="scripts/controllers/changelog-manager.js"></script>
        <script src="scripts/controllers/upsell-manager.js"></script>
        <script src="scripts/controllers/upsell-manager-configuration.js"></script>
        <!-- endbuild -->

        <!-- Extra service js -->

        <!-- Custom scroll bar JS -->
        <script src="https://unpkg.com/simplebar@latest/dist/simplebar.js"></script>
        <script src="bower_components/angular-inview.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script src="https://cdn.ckeditor.com/4.13.1/standard-all/ckeditor.js"></script>
        <script src="scripts/angular-ckeditor.js"></script>
    </body>
    </html>
    <?php
}
else
{
    Auth::backToLogin();
}
?>
