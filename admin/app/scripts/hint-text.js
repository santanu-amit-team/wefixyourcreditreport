'use strict';
/**
 * @ngdoc overview
 * @name codeBaseAdminApp.module
 * @description
 * # Hint text
 * module of the codeBaseAdminApp
 */
angular.module('codeBaseAdminApp')
	.run(function ($rootScope, $filter) {
		$rootScope.hint = [];
		/* For pixel */
		$rootScope.hint['pixel'] = {
			pixel_name: '',
			pixel_type: '',
			postback_url: '',
			click_pixel: '',
			convert_pixel: '',
			prepaid: '',
			affiliate_id: '',
			sub_id: '',
			page: '',
			device: '',
			os: '',
			pixel_placement: '',
			html_pixel: '',
			third_party_postback_url: '',
			third_party_html: '',
			configuration_id: ''
		};

		/* For CMS */
		$rootScope.hint['content'] = {
			content_name: '',
			content_body: '',
			content_slug: ''
		};

		/* For Campaign */
		$rootScope.hint['campaign'] = {
			campaign_label: '',
			product_type: '',
			campaign_id: '',
			shipping_id: '',
			shipping_price: '',
			product_id: '',
			product_price: '',
			product_quantity: '',
			rebill_product_id: '',
			rebill_product_price: '',
			prepaid_id: '',
			scrap_id: ''
		};

		/* For CRM */
		$rootScope.hint['crm'] = {
			crm_label: '',
			crm_type: '',
			endpoint: '',
			username: '',
			password: '',
		};

		/* For Configurations */
		$rootScope.hint['config'] = {
			configuration_label: '',
			crm_id: '',
			campaign_ids: '',
			upsell_preferred_method: '',
			site_title: '',
			meta_description: '',
			notes: '',
			force_gateway_id: '',
			preserve_gateway: '',
			enable_delay: '',
			delay_time: '',
			split_charge: '',
			split_preferred_method: '',
			split_campaign_ids: '',
			link_with_parent: '',
			order_placement_method: '',
			initialize_new_subscription: '',
			initialize_new_subscription_value_type: '',
			initialize_new_subscription_value: '',
			split_enable_delay: '',
			split_delay_time: '',
			accept_prepaid_cards: '',
			enable_downsells: '',
			exit_popup_enabled: '',
			exit_popup_element_id: '',
			exit_popup_page: '',
			enable_kount: '',
			kount_pixel: ''
		};

		/* For Settings */
		$rootScope.hint['settings'] = {
			license_key: '',
			domain: '',
			offer_path: '',
			app_timezone: '',
			development_mode: '',
			allowed_country_codes: '',
			google_login: '',
			google_client_id: '',
			google_secret_key: '',
			whitelisted_domains: '',
			enable_affiliates: '',
			enable_rotators: '',
			db_host: '',
			db_username: '',
			db_password: '',
			db_name: '',
			encryption_key: '',
			gateway_switcher_id: '',
			force_https: '',
			force_https_based_on_env: '',
			force_www: '',
			enable_browser_caching: '',
			disable_cache_header: '',
			enable_gzip_compression: '',
			mobile_path: '',
			enable_mobile_version: '',
			mobile_version_only: '',
			redirect_tablet_screen: '',
			allow_direct_access: '',
			enable_cdn: '',
			enable_alternate_cdn_path: '',
			cdn_basepath: '',
			img_cdn_path: '',
			css_cdn_path: '',
			js_cdn_path: '',
			allowed_card_types: '',
			allowed_test_cards: '',
			ga_site_id: '',
			customer_support_email: '',
			customer_service_number: '',
			hours_of_operation: '',
			return_address: '',
			error_email_addresses: '',
			corporate_address: '',
			show_validation_errors: '',
			country_lang_mapping: '{COUNTRYCODE}|{STATE}|{ZIP}'
		};

		/* For Rotator */
		$rootScope.hint['rotator'] = {
			label: '',
			company_name: '',
			image_name: '',
			address: '',
			phone_no: '',
			email: '',
			percentage: '',
			configuration_ids: ''
		};

		/* For Affiliate */
		$rootScope.hint['affiliate'] = {
			label: '',
			affiliatesHint: {
				affiliate_id: '',
				affiliate_value: ''
			},
			configurationHint: {
				config_first: '',
				config_second: ''
			},
			configuration_ids: '',
			scrap_step_1: 'Enter ' + $filter('lowercase')($rootScope.orderFilterText) + ' percentage for step 1',
			scrap_step_2: 'Enter ' + $filter('lowercase')($rootScope.orderFilterText) + ' percentage for step 2',
		};

		/* For Advanced */
		$rootScope.hint['advanced'] = {
			scrapper: {
				enable: '',
				remote: '',
				percentage: {
					one: 'Enter ' + $filter('lowercase')($rootScope.orderFilterText) + ' percentage for step 1',
					two: 'Enter ' + $filter('lowercase')($rootScope.orderFilterText) + ' percentage for step 2'
				},
				push_track_id: ''
			}
		};

		$rootScope.hint['MidRouting'] = {
			default_gateway : 'Leave empty to use LB from CRM',
		};
	});