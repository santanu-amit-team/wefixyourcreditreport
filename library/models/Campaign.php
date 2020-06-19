<?php

namespace Application\Model;

use Application\Config;
use Application\Session;
use Exception;

class Campaign
{

    private function __construct()
    {
        return;
    }

    public static function find($id, $isFormatted = false)
    {
        $campaign = Config::campaigns(sprintf('%d', $id));
        if ($campaign === null || !is_array($campaign)) {
            if (DEV_MODE) {
                Session::set('lastException.message', sprintf(
                    'Campaign not found with id %d', $id
                ));
            }
            $properError = sprintf(
                'Campaign not found with id %d.', $id
            );

            Session::set('lastExceptionToPopup.message', $properError);
            throw new Exception('General config error.( Error: ' . $properError .')', 1001);
        }
        if (Session::get('steps.meta.isScrapFlow') === true && !empty($campaign['enable_order_filter_campaigns'])) {
            $campaign = Config::campaigns(
                sprintf('%d', $campaign['scrap_campaign_id'])
            );
        }
        if ($campaign === null || !is_array($campaign)) {
            if (DEV_MODE) {
                Session::set('lastException.message', sprintf(
                    'Scrap campaign not found for campaign # %d', $id
                ));
            }
            $properError = sprintf(
                'Campaign not found with id %d.', $id
            );

            Session::set('lastExceptionToPopup.message', $properError);
            throw new Exception('General config error.' . $properError, 1001);
        }
        if (Session::get('steps.meta.isPrepaidFlow') === true && !empty($campaign['enable_prepaid_campaigns'])) {
            $campaign = Config::campaigns(
                sprintf('%d', $campaign['prepaid_campaign_id'])
            );
        }
        if ($campaign === null || !is_array($campaign)) {
            if (DEV_MODE) {
                Session::set('lastException.message', sprintf(
                    'Prepaid campaign not found for campaign # %d', $id
                ));
            }

            $properError = sprintf(
                'Campaign not found with id %d.', $id
            );
            
            Session::set('lastExceptionToPopup.message', $properError);
            throw new Exception('General config error.' . $properError, 1001);
        }

        $cleanCampaign = self::getCleanedCampaign($campaign);
        if($isFormatted) {
            $products = array();
            if(!empty($cleanCampaign['product_array'])){  
                foreach ($cleanCampaign['product_array'] as $childProduct) {
                    unset($cleanCampaign['product_array']);
                    array_push($products, array_merge($cleanCampaign, $childProduct));
                }
            }

            return $products;
        }
        else {
            return $cleanCampaign;
        }
    }

    private static function getCleanedCampaign($campaign)
    {
        $common_attributes = array(
            'codebaseCampaignId' => $campaign['id'],
            'campaignId'         => $campaign['campaign_id'],
            'shippingId'         => $campaign['shipping_id'],
            'shippingPrice'      => $campaign['shipping_price'],
            'enableBillingModule'  => $campaign['billing_type'] == 2 ? true : false,
            'offerId'              => !empty($campaign['offer_id']) ? $campaign['offer_id'] : '',
            'billingModelId'       => !empty($campaign['billing_model_id']) ? $campaign['billing_model_id'] : '',
            'trialProductId'       => !empty($campaign['trial_product_id']) ? $campaign['trial_product_id'] : '',
            'trialProductPrice'    => !empty($campaign['trial_product_price']) ? $campaign['trial_product_price'] : '',
            'trialProductQuantity' => !empty($campaign['trial_product_quantity']) ? $campaign['trial_product_quantity'] : '',
            'trialProductQuantity' => !empty($campaign['trial_product_quantity']) ? $campaign['trial_product_quantity'] : '',
            'campaign_limit'       => !empty($campaign['enable_campaign_limit']) ? $campaign['campaign_limit'] : '',
            'alter_campaignid'     => !empty($campaign['enable_campaign_limit']) ? $campaign['alter_campaignid'] : ''
        );
        
        if(!empty($campaign['product_array']))
        {
            $common_attributes['product_array'] = self::prepareProducts($campaign['product_array']);
        }
        
        return $common_attributes;
        
    }
    
    
    private static function prepareProducts($productArray)
    {
        $pArry = json_decode($productArray, true);
        $products = array();        
        foreach ($pArry as $childProduct) {
            $product['productId'] = $childProduct['product_id'];
            $product['productPrice'] = $childProduct['product_price'];
            $product['retail_price'] = $childProduct['retail_price'];
            $product['productQuantity'] = $childProduct['product_quantity'];
            $product['rebillProductPrice'] = $childProduct['rebill_product_price'];
            $product['productKey'] = !empty($childProduct['product_key']) ? $childProduct['product_key'] : '';
            $product['m1billing_offer_id'] = !empty($childProduct['m1billing_offer_id']) ? $childProduct['m1billing_offer_id'] : '';
            $product['vrioOfferId'] = !empty($childProduct['offer_id']) ? $childProduct['offer_id'] : '';
            $product['offerPrice'] = !empty($childProduct['offer_price']) ? $childProduct['offer_price'] : '';
            $product['offerQuantity'] = !empty($childProduct['offer_quantity']) ? $childProduct['offer_quantity'] : '';
            $product['orderOfferId'] = !empty($childProduct['order_offer_id']) ? $childProduct['order_offer_id'] : '';
            
            array_push($products, $product);
        }
        return $products;
    }

    public static function getProductsByCampaignIds($campaignIds)
    {
        $products = array();

        foreach ($campaignIds as $campaignId) {

            array_push($products, Campaign::find($campaignId));

        }

        return $products;
    }

}
