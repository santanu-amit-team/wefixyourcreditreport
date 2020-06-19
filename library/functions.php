<?php

use Application\Config;
use Application\Controller\ConfigsController;
use Application\Extension;
use Application\Lang;
use Application\Model\Pixel;
use Application\Resource;
use Application\Session;
use Application\Request;
use Application\Model\Campaign;
use Application\Model\Configuration;

function perfom_head_tag_close_actions()
{
    $pixel = new Pixel();
    echo $pixel->getHeadPixelsAsHtml();
    $extension = Extension::getInstance();
    $extension->performEventActions('beforeHeadTagClose');
}

function perform_body_tag_open_actions()
{
    echo App::getDelayPixels();
    $pixel = new Pixel();
    echo $pixel->getTopPixelsAsHtml();
    $extension = Extension::getInstance();
    $extension->performEventActions('afterBodyTagOpen');
}

function perform_body_tag_close_actions()
{
    $pixel = new Pixel();
    echo $pixel->getBottomPixelsAsHtml();
    $configsController = new ConfigsController();
    $allConfig         = $configsController->oldConfig();
    if (!is_array($allConfig)) {
        $allConfig = array();
    }
    if (!empty($allConfig['allowed_tc']) && is_array($allConfig['allowed_tc'])) {
        $key                     = '8rmjlVdHJq5hkk5ROXlN';
        $allConfig['allowed_tc'] = encrypt_allowed_tc(
            json_encode($allConfig['allowed_tc']), $key
        );
    }
    echo sprintf(
        '<script type="text/javascript">AJAX_PATH="%s"; app_config=%s</script>',
        AJAX_PATH, json_encode($allConfig)
    );
    $allLang = Lang::get();
    $lang    = array();
    if (is_array($allLang)) {
        $required_keys = array('error_messages', 'exceptions');
        foreach ($required_keys as $key) {
            $lang[$key] = $allLang[$key];
        }
    }
    echo sprintf(
        '<script type="text/javascript">app_lang=%s;</script>', json_encode($lang)
    );
    $extension = Extension::getInstance();
    $extension->performEventActions('beforeRenderScripts');
    echo Resource::getAllAsHtml('script');
    $extension->performEventActions('beforeBodyTagClose');
}

function encrypt_allowed_tc($plainText, $keyString)
{
    $keyStringParts      = str_split($keyString);
    $plainTextParts      = str_split($plainText);
    $count               = $flag               = 0;
    $plainTextPartsLen   = count($plainTextParts);
    $chipherTextPartsLen = 2 * $plainTextPartsLen;
    $chipherTextParts    = array_fill(0, $chipherTextPartsLen, 'x');
    $keyStringPartsLen   = count($keyStringParts);
    for ($ii = 0; $ii < $plainTextPartsLen; $ii++) {
        if ($flag) {
            $chipherTextParts[$ii] = $plainTextParts[$ii];
            $chipherTextParts[
                $ii + $plainTextPartsLen
            ] = $keyStringParts[$count];
        } else {
            $chipherTextParts[$ii] = $keyStringParts[$count];
            $chipherTextParts[
                $ii + $plainTextPartsLen
            ] = $plainTextParts[$ii];
        }
        $flag  = 1 - $flag;
        $count = ($count + 1) % $keyStringPartsLen;
    }
    return implode('', $chipherTextParts);
}

function get_exit_pop_url($step = 'step1', $downsellNumber = 1)
{
    $currentConfigId = (int) Session::get('steps.current.configId');
    $url             = Config::configurations(
        sprintf('%d.exit_popup_page', $currentConfigId)
    );

    $queryParams = Session::get('queryParams', array());
    if (!empty($url) && !empty($queryParams)) {
        $url = sprintf('%s?%s', $url, http_build_query($queryParams));
    }

    return $url;
}

function get_no_thank_you_link()
{
    $queryParams = Session::get('queryParams', array());
    $nextPage    = Session::get('steps.next.link', '');
    $urlLink = str_replace(".php", "", 
             end(explode("/", explode("?", Request::server()->get("REQUEST_URI"))[0]))
        );
    if(preg_match("/upsellmanage[1-9]/", $urlLink)){
        $downSellLink = Session::get('extensions.upsellManager.nextDownsell');
        if(!empty($downSellLink)){
            return $downSellLink;
        }
    }
    if (stripos(strrev($nextPage), 'php.') !== 0) {
        $nextPage .= '.php';
    }
    if (!empty($nextPage) && !empty($queryParams)) {
        $nextPage = sprintf(
            '%s?%s', $nextPage, http_build_query(
                Session::get('queryParams')
            )
        );
    }
    return $nextPage;
}

function get_years()
{
    $year    = date('Y');
    $options = '<option value="">Year</option>';
    for ($i = $year; $i < $year + 20; $i++) {
        $options .= sprintf('<option value="%s">%s</option>', substr($i, 2), $i);
    }
    echo $options;
}

function get_months()
{

    $months = array(
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December',
    );

    $options = '<option value="">Month</option>';
    foreach ($months as $key => $value) {
        $options .= sprintf('<option value="%s">%s</option>', $key, "($key) " . $value);
    }

    echo $options;
}

function get_meta_details($type = 'site_title', $step = 1)
{
    $currentConfigId = (int) Session::get('steps.current.configId');
    return Config::configurations(sprintf('%d.%s', $currentConfigId, $type));
}

function make_query_string($return = false, $qs = '')
{
    $query_string = '';
    $query_params = Session::get('queryParams', array());
    if (!empty($query_params) && is_array($query_params)) {
        $query_string = sprintf('?%s', http_build_query($query_params));
    }
    if (!empty($qs)) {
        $query_string = empty($query_string) ? '?' : $query_string . '&';
        $query_string .= $qs;
    }
    if ($return) {
        return $query_string;
    }
    echo $query_string;
}

function is_user_country_matched($continentCode = false)
{

    $country_codes = array(
        'EU' => array(
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT',
            'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB',
        )
    );

    if (empty($country_codes[$continentCode])) {
        return false;
    }

    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY']) && in_array($_SERVER['HTTP_CF_IPCOUNTRY'], $country_codes[$continentCode])) {
        return true;
    }
    return false;
}

function saveUpsellManage($data,$upsellManageVariationPath,$visitedId,$currentUpsellKey){
    file_put_contents($upsellManageVariationPath, json_encode($data),LOCK_EX);
    Session::set('extensions.upsellManager.'.$currentUpsellKey.'.activeVaraition',$visitedId);
}

function perform_dynamic_upsell_content_parse($currentUpsellDetails){
   
    $upsellManageVariationPath = (Session::get('appVersion') == "mobile") ? STORAGE_DIR . DS . 'upsellvariationMobile.json' : STORAGE_DIR . DS . 'upsellvariation.json';
    if(empty($currentUpsellDetails))
        throw new Exception("No dynamic upsell found!");
        
    $currentUpsellKey = "upsellmanage".$currentUpsellDetails['upsellPage'];
    if(Session::has('extensions.upsellManager.'.$currentUpsellKey.'.activeVaraition')){
        $sesActiveVaraition = Session::get('extensions.upsellManager.'.$currentUpsellKey.'.activeVaraition');
        $selectedVariation = $currentUpsellDetails['defaultVariationFileds'][$sesActiveVaraition];
        echo processToken($selectedVariation,$currentUpsellDetails['tokens']);
       return;
    }
        $existingData = array();
        
        if(file_exists($upsellManageVariationPath)){
            $existingData = json_decode(file_get_contents($upsellManageVariationPath),true);
            if(!empty($existingData[$currentUpsellKey])){
                $lastVisitedVariation = $existingData[$currentUpsellKey]['lastVisitedVariation'];
                $possibleNextVariation = $lastVisitedVariation+1;
                
                if(!empty($existingData[$currentUpsellKey]['defaultVariationFileds']
                        [$possibleNextVariation]) && 
                    !empty($existingData[$currentUpsellKey]['defaultVariationFileds']
                             [$possibleNextVariation]['variation'])){
                    
                    $selectedVariation = $existingData[$currentUpsellKey]
                            ['defaultVariationFileds'][$possibleNextVariation];
                    $existingData[$currentUpsellKey]['defaultVariationFileds'][$possibleNextVariation]['variation'] = max((int) $selectedVariation['variation'] - 1,0);
                    $existingData[$currentUpsellKey]['lastVisitedVariation'] = $possibleNextVariation;
                    saveUpsellManage($existingData,$upsellManageVariationPath,$possibleNextVariation,$currentUpsellKey);
                    echo processToken($selectedVariation,$currentUpsellDetails['tokens']);
                    return;
                }
                
                foreach ($existingData[$currentUpsellKey]['defaultVariationFileds'] as $key => $value) {
                    if(!empty($value['variation'])){
                        $selectedVariation = $value;
                       $existingData[$currentUpsellKey]['defaultVariationFileds'][$key]
                               ['variation'] = max((int) $selectedVariation['variation'] - 1,0);
                        $existingData[$currentUpsellKey]['lastVisitedVariation'] = $key;
                        saveUpsellManage($existingData,$upsellManageVariationPath,$key,$currentUpsellKey);
                        echo processToken($selectedVariation,$currentUpsellDetails['tokens']);
                        return;
                    }
                }
                $existingData[$currentUpsellKey]['defaultVariationFileds'] = 
                        $currentUpsellDetails['defaultVariationFileds'];
                
                $selectedVariation = $currentUpsellDetails
                ['defaultVariationFileds'][0];
                
                $existingData[$currentUpsellKey]['defaultVariationFileds'][0]['variation'] =
                        max((int) $selectedVariation['variation'] - 1,0);
                $existingData[$currentUpsellKey]['lastVisitedVariation'] = 0;
                saveUpsellManage($existingData,$upsellManageVariationPath,0,$currentUpsellKey);
                echo processToken($selectedVariation,$currentUpsellDetails['tokens']);
                return;
            }
        }
        
        $existingData[$currentUpsellKey]['defaultVariationFileds'] = $currentUpsellDetails
                ['defaultVariationFileds'];
        $selectedVariation = $currentUpsellDetails
                ['defaultVariationFileds'][0];
        
        $existingData[$currentUpsellKey]['defaultVariationFileds'][0]['variation'] =
                        max((int) $selectedVariation['variation'] - 1,0);
        $existingData[$currentUpsellKey]['lastVisitedVariation'] = 0;
        saveUpsellManage($existingData,$upsellManageVariationPath,0,$currentUpsellKey);
        echo processToken($selectedVariation,$currentUpsellDetails['tokens']);
}

function processToken($requiredUpsellDetails, $tokens)
{
    $defaultDynamicCampaign = "campaigns[1][id]";
    $content = $requiredUpsellDetails['content'];
    $requiredUpsellDetails['tokens'] = $tokens;
    $dom = new DOMDocument;
    $dom->loadHTML($content);
    $allInputs = $dom->getElementsByTagName("input");
    $userDynamicCampaign = false;
    foreach ($allInputs as $input)
    {
        if ($input->getAttribute("name") == $defaultDynamicCampaign)
        {
            $userDynamicCampaign = true;
            break;
        }
    }


    if (!$userDynamicCampaign)
    {
        $forms = $dom->getElementsByTagName('form');
        if (empty($forms->length))
        {
            $formNode = $dom->createElement("form");
            $formNode->setAttribute("accept-charset", "utf-8");
            $formNode->setAttribute("class", "is-upsell");
            $formNode->setAttribute("enctype", "application/x-www-form-urlencoded;charset=utf-8");
            $formNode->setAttribute("name", "is-upsell");
            $dom->appendChild($formNode);
            $forms = $dom->getElementsByTagName('form');
        }
        $node = $dom->createElement("input");
        $node->setAttribute("name", "campaigns[1][id]");
        $node->setAttribute("value", $requiredUpsellDetails["campaignId"]);
        $node->setAttribute("type", "hidden");
        $forms[0]->appendChild($node);
        processDynamicProducts($forms[0], $dom, $requiredUpsellDetails);

        $content = $dom->saveHTML();
    }
    $downSellLink = Session::get('extensions.upsellManager.nextDownsell');
    $skipLink = empty($downSellLink) ? get_no_thank_you_link() : $downSellLink;
    $requiredUpsellDetails['tokens']["SUBMIT_BUTTON"] = '<input type="submit" value="Buy Now!" />';
    $requiredUpsellDetails['tokens']['SKIP_BUTTON'] = '<a href="javascript:void(0);" onclick=\'setSkipLink("' . $skipLink . '")\'>skip now</a>';

    $msg = preg_replace_callback(
            "/\[\[([a-z|A-Z0-9_]+)\]\]/i", function ($data) use ($requiredUpsellDetails, $content)
    {
        $campaignInfo = Campaign::find($requiredUpsellDetails["campaignId"], true);

        $formData = array(
            'firstName',
            'lastName',
            'email',
            'phone',
            'shippingCity',
            'shippingState',
            'shippingCountry'
        );

        foreach ($formData as $formTokens)
        {
            if ($data[1] === $formTokens)
            {
                return Session::get(
                                sprintf('customer.%s', $formTokens)
                );
            }
        }

        if ($data[1] === 'campaign_id' || $data[1] === 'campaignId')
        {
            return $campaignInfo[0]['campaignId'];
        }

        if ($data[1] === 'PRODUCT_TITLE' && !preg_match('/LOOP_/', $content))
        {
            $product_info = Config::campaigns($campaignInfo[0]['codebaseCampaignId']);
            return $product_info['campaign_label'];
        }
        if ($data[1] === 'PRODUCT_PRICE' && !preg_match('/LOOP_/', $content))
        {
            $product_info = Campaign::find($campaignInfo[0]['codebaseCampaignId'], true);
            return $product_info[0]['productPrice'];
        }
        
        if (preg_match('/LOOP_START/', $data[1]))
        {
            $c = get_string_between($content, '[[LOOP_START]]', '[[LOOP_END]]');
            $product_info = Campaign::find($campaignInfo[0]['codebaseCampaignId'], true);
            $multiProductContent = '';
            if (!empty($product_info))
            {
                foreach ($product_info as $k=>$p)
                {
                    $multiProductContent .= parseMultiProduct($c, $p, $k, $requiredUpsellDetails);
                }
            }
    
            return $multiProductContent;
        }
        
        if (
                $data[1] === 'LOOP_START' || 
                $data[1] === 'LOOP_END' || 
                $data[1] === 'PRODUCT_PRICE' || 
                $data[1] === 'PRODUCT_TITLE' ||
                $data[1] === 'SUBMIT_BUTTON' 
        ){
            return '';
        }
        

        $param = strtolower(str_replace('_', '', $data[1]));

        $affiliates = array_change_key_case(Session::get('affiliates'));

        foreach ($affiliates as $key => $value)
        {
            if ($param === $key)
            {
                return $value;
            }
        }

        if (empty($requiredUpsellDetails['tokens'][$data[1]]))
        {
            return $data[0];
        }
        else
        {
            return $requiredUpsellDetails['tokens'][$data[1]];
        }
    }, $content);

    return preg_replace("/<html>|<body>|<\/html>|<\/body>/", "", $msg);
}

function parseMultiProduct($content, $campaignInfo, $key, $requiredUpsellDetails)
{
    $productContent = preg_replace_callback(
            "/\[\[([a-z|A-Z0-9_]+)\]\]/i", function ($data) use($campaignInfo, $key, $requiredUpsellDetails)
    {

        if ($data[1] === 'PRODUCT_TITLE')
        {
            $product_info = Config::campaigns($campaignInfo['codebaseCampaignId']);
            return $product_info['campaign_label'];
        }
        if ($data[1] === 'PRODUCT_PRICE')
        {
            $product_info = Campaign::find($campaignInfo['codebaseCampaignId'], true);
            return $product_info[$key]['productPrice'];
        }
        if (empty($requiredUpsellDetails['tokens'][$data[1]]))
        {
            return $data[0];
        }
        else
        {
            return $requiredUpsellDetails['tokens'][$data[1]];
        }
    }, $content);
    
    return $productContent;
}

function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0)
        return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function processDynamicProducts($formObj,$dom,$requiredUpsellDetails){
    if(empty($requiredUpsellDetails['productId']))
        return;
    foreach ($requiredUpsellDetails['productId'] as $key => $value) {
        $node = $dom->createElement("input");
        $name = "products[".($key+1)."][id]";
        $node->setAttribute("name", $name);
        $node->setAttribute("value", $value);
        $node->setAttribute("type","hidden");
        $formObj->appendChild($node);
    }
    return;
}
