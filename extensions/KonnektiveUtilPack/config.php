<?php

return array(
    'custom_html' => array(
        'template_js' => 'js/konnektive.js',
        'enable' => true,
        'template_name' => 'html/konnektive.html'
    ),
    'hooks'    => array(
        array(
            'event'    => 'beforeBodyTagClose',
            'callback' => "Extension\\KonnektiveUtilPack\\ConfirmOrder@injectScript",
            'priority' => 100,
        ),
        array(
            'event'    => 'beforeBodyTagClose',
            'callback' => "Extension\\KonnektiveUtilPack\\CustomerNote@injectScript",
            'priority' => 100,
        ),
        array(
            'event'    => 'beforeBodyTagClose',
            'callback' => "Extension\\KonnektiveUtilPack\\ImportClick@injectScript",
            'priority' => 100,
        ),
        array(
            'event'    => 'afterAnyCrmRequest',
            'callback' => "Extension\\KonnektiveUtilPack\\CustomerNote@savePayload",
            'priority' => 100,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => "Extension\\KonnektiveUtilPack\\ImportClick@addSessionId",
            'priority' => 100,
        ),
        array(
            'event'    => 'pageLoad',
            'callback' => "Extension\\KonnektiveUtilPack\\ExtraSources@addDefineParams",
            'priority' => 100,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => "Extension\\KonnektiveUtilPack\\ExtraSources@pushParams",
            'priority' => 900,
        ),
        array(
            'event'    => 'afterCrmPayloadReady',
            'callback' => "Extension\\KonnektiveUtilPack\\ExtraSources@bypassZeroValue",
            'priority' => 900,
        ),
        array(
            'event'    => 'afterCrmPayloadReady',
            'callback' => "Extension\\KonnektiveUtilPack\\ImportClick@switchMethod",
            'priority' => 600,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => "Extension\\KonnektiveUtilPack\\ExtraSources@addAdditionalParams",
            'priority' => 900,
        ),
        array(
            'event' => 'beforeAnyCrmRequest',
            'callback' => "Extension\\KonnektiveUtilPack\\DirectDebit@captureCrmPayload",
            'priority' => 100,
        ),
        array(
            'event'    => 'beforeAnyCrmRequest',
            'callback' => "Extension\\KonnektiveUtilPack\\ExtraSources@addProductVariation",
            'priority' => 901,
        ),
    ),
    'routes'   => array(
        array(
            'slug'     => 'fire-confirm-order',
            'callback' => "Extension\\KonnektiveUtilPack\\ConfirmOrder@fire",
        ),
        array(
            'slug'     => 'send-customer-note',
            'callback' => "Extension\\KonnektiveUtilPack\\CustomerNote@send",
        ),
        array(
            'slug'     => 'fire-import-click',
            'callback' => "Extension\\KonnektiveUtilPack\\ImportClick@fire",
        ),
    ),
    'settings' => array(
        array(
            "label"    => "Extra Source Keys",
            "key"      => "extra_source_keys",
            "type"     => "string",
            "hint"     => "Extra paramas name in csv format (e. g: Source1,Source2)",
            "value"    => "",
            "optional" => true,
        ),
        array(
            'label' => 'Activate Membership Service',
            'key'   => 'membership_service',
            'type'  => 'boolean',
            'value' => false,
        ),
        array(
            'label' => 'Activate Insure Shipment',
            'key'   => 'insureship_service',
            'type'  => 'boolean',
            'value' => false,
        ),
        array(
            'label' => 'Allow note for prospect',
            'key'   => 'note_for_prospect',
            'type'  => 'boolean',
            'value' => false,
        ),
        array(
            'label' => 'Skip note',
            'key'   => 'skip_note',
            'type'  => 'boolean',
            'value' => false,
        ),
        array(
            'label' => 'Convert IPv6 To IPv4',
            'key'   => 'convert_ip',
            'type'  => 'boolean',
            'value' => false,
        ),
        array(
            'label' => 'Bypass zero value to CRM',
            'key'   => 'bypass_zero_value',
            'type'  => 'boolean',
            'value' => false,
        ),
        array(
            'label' => 'Activate Shipping ProfileId',
            'key'   => 'activate_ship_profileid',
            'type'  => 'boolean',
            'value' => false,
            'hint'  => 'If enabled shipping price will be ignored'
        ),
    ),
);
