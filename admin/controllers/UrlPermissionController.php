<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Application\Session;


class UrlPermissionController {

    private $menus =  array(

        "dashboard" => array(
            'widget' => array(
                'name' => 'Dashboard',
                'icon' => 'dashboard',
                'href' => 'dashboard'
            )
        ),
        "change_access_permissions" => array(
            'widget' => null,
        ),
        "ecommerce" => array(
            'widget' => array(
                'name' => 'Ecommerce',
                'icon' => 'shopping_cart',
                'child_open' => false
            )
        ),
        "campaigns" => array(
            'parent_menu' => 'ecommerce',
            'widget' => array(
                'name' => 'Campaigns',
                'icon' => 'flag',
                'href' => 'campaigns',
            ),
            'urls_group' => array( 'campaigns', 'campaign-manager')
        ),
        "funnel_configurations" => array(
            'parent_menu' => 'ecommerce',
            'widget' => array(
                'name' => 'Funnel Configurations',
                'icon' => 'build',
                'href' => 'configurations',
            ),
            'urls_group' => array( 'configurations', 'configuration-manager')
        ),
        "coupons" => array(
            'parent_menu' => 'ecommerce',
            'widget' => array(
                'name' => 'Coupons',
                'icon' => 'card_giftcard',
                'href' => 'coupons',
            ),
            'urls_group' => array( 'coupons', 'coupon-manager', 'coupon-user' )
        ),
        "cms" => array(
            'widget' => array(
                'name' => 'CMS',
                'icon' => 'web',
                'href' => 'cms',
            ),
            'urls_group' => array( 'cms', 'cms-manager' )
        ),
        "extensions" => array(
            'widget' => array(
                'name' => 'Extensions',
                'icon' => 'widgets',
                'href' => 'extensions',
            ),
            'urls_group' => array( 'extensions', 'extension-catalogue' )
        ),
        "logs" => array(
            'widget' => array(
                'name' => 'Logs',
                'icon' => 'description',
                'child_open' => false
            )
        ),
        "systems_log" => array(
            'parent_menu' => 'logs',
            'widget' => array(
                'name' => 'System Log',
                'icon' => 'subject',
                'href' => 'admin_logs',
            ),
            'urls_group' => array()
        ),
        "user_activity" => array(
            'parent_menu' => 'logs',
            'widget' => array(
                'name' => 'User Activity',
                'icon' => 'assessment',
                'href' => 'admin_logs',
            )
        ),
        "change_log" => array(
            'parent_menu' => 'logs',
            'widget' => array(
                'name' => 'Change Log',
                'icon' => 'list_alt',
                'href' => 'admin_logs',
            ),
            'urls_group' => array( 'changelog')
        ),
        "system" => array(
            'widget' => array(
                'name' => 'System',
                'icon' => 'desktop_windows',
                'child_open' => false
            )
        ),
        "crm" => array(
            'parent_menu' => 'system',
            'widget' => array(
                'name' => 'CRM',
                'icon' => 'timeline',
                'href' => 'crms',
            ),
            'urls_group' => array( 'crms', 'crms-manager' )
        ),
        "users" => array(
            'parent_menu' => 'system',
            'widget' => array(
                'name' => 'Users',
                'icon' => 'supervisor_account',
                'href' => 'users',
            ),
            'urls_group' => array( 'users', 'user-manager' )
        ),
        "settings" => array(
            'parent_menu' => 'system',
            'widget' => array(
                'name' => 'Settings',
                'icon' => 'settings',
                'href' => 'settings',
            ),
            'urls_group' => array( 'settings' )
        ),
        "advance_settings" => array(
            'parent_menu' => 'system',
            'widget' => array(
                'name' => 'Advanced Settings',
                'icon' => 'more_horiz',
                'href' => 'advanced',
            ),
            'urls_group' => array( 'advanced', 'authentication' )
        ),
        "tools" => array(
            'widget' => array(
                'name' => 'Tools',
                'icon' => 'work',
                'child_open' => false
            )
        ),
        "affiliate_manager" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Affiliate Manager',
                'icon' => 'merge_type',
                'href' => 'affiliates',
            ),
            'urls_group' => array( 'affiliates', 'affiliate-manager' )
        ),
        "pixel_manager" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Pixel Manager',
                'icon' => 'track_changes',
                'href' => 'pixels',
            ),
            'urls_group' => array( 'pixels', 'pixel-setup' )
        ),
        "rotators" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Rotators',
                'icon' => 'crop_rotate',
                'href' => 'rotators',
            ),
            'urls_group' => array( 'rotators', 'rotator-manager' )
        ),
        "mid_routing" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Mid Routing',
                'icon' => 'call_split',
                'href' => 'routing',
            ),
            'urls_group' => array( 'routing', 'routing-manager', 'midrouting' )
        ),
        "traffic_monitor" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Traffic Monitor',
                'icon' => 'show_chart',
                'href' => 'traffic-monitor',
            ),
            'urls_group' => array( 'traffic-monitor' )
        ),
        "auto_responder" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Auto Responder',
                'icon' => 'rotate_left',
                'href' => 'auto-responder',
            ),
            'urls_group' => array( 'auto-responder', 'autoresponder-manager' )
        ),
        "auto_filters" => null,
        "scheduler" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Scheduler',
                'icon' => 'alarm',
                'href' => 'crons',
            ),
            'urls_group' => array( 'cron' , 'crons', 'cron-manager' )
        ),
        "diagnosis" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Troubleshooting',
                'icon' => 'bug_report',
                'href' => 'diagnosis',
            ),
            'urls_group' => array( 'diagnosis' )
        ),
        "split_test" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Split Test',
                'icon' => 'crop_rotate',
                'href' => 'split-test',
            ),
            'urls_group' => array( 'split-test-manager', 'split-test' )
        ),
        "upsell_manager" => array(
            'parent_menu' => 'tools',
            'widget' => array(
                'name' => 'Upsell Manager',
                'icon' => 'add_to_queue',
                'href' => 'upsell-manager',
            ),
            'urls_group' => array( 'upsell-manager-configuration', 'upsell-manager' )
        ),
        "logout" => array(
            'widget' =>  array(
                'name' => 'Logout',
                'icon' => 'power_settings_new',
                'href' => 'logout',
            )
        )
    );

    function __construct()
    {
        
    }
    
    public function isValid($slug)
    {     
        //print $slug;
        if(!strncmp(Session::get('userType'), 'developer', strlen('developer')))
            return array(
                'success' => true,
                'data' => array(),
                'success_message' => 'Access granted.',
            );

        $validMenus = Session::get('access_urls');

        //print_r($validMenus);

        unset($this->menus['dashboard']);
        unset($this->menus['logout']);
        $widgetConfig = '';

        if(in_array(strtolower($slug), array( 'dashboard', 'logout', 'settings', 'versions') ))
            return array(
                'success' => true,
                'data' => array(),
                'success_message' => 'Access granted.',
            );

        foreach( $this->menus as $menu => $value ){
            if( is_array($value) && array_key_exists('urls_group', $value) && in_array(strtolower($slug), $value['urls_group'])){
                $widgetConfig = $menu;
                break;
            }
        }

        if( array_key_exists($widgetConfig, $validMenus) && $validMenus[$widgetConfig]){
            return array(
                'success' => true,
                'data' => array(),
                'success_message' => 'Access granted.',
            );
        }

        return array(
            'success' => false,
            'data' => array(),
            'error_message' => 'Unauthorized access block.',
        );
    }
}