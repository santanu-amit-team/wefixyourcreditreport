<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        <h1>Order Details</h1>
        <div id="msg"></div>
        <form method="post" name="customer_order_details" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <div class="row">
                <div class="col-sm-12">
            <h3>Shipping Details</h3>
            <p>
                <label>First Name: </label>
                <input type="text" name="first_name" placeholder="First Name" class="required" value="<?= !empty($orderDetails['shipping_first_name'])?$orderDetails['shipping_first_name']:''; ?>" data-error-message="Please enter your first name!" />
            </p>
            <p>
                <label>Last Name: </label>
                <input type="text" name="last_name" placeholder="Last Name" class="required" value="<?= !empty($orderDetails['shipping_last_name'])?$orderDetails['shipping_last_name']:''; ?>" data-error-message="Please enter your last name!" />
            </p>
            <p>
                <label>Address: </label>
                <input id="autocomplete" type="text" name="shipping_address1" placeholder="Your Address" class="required" value="<?= !empty($orderDetails['shipping_street_address'])?$orderDetails['shipping_street_address']:''; ?>" data-error-message="Please enter your address!" />
            </p>
            <p>
                <label>Country: </label>
                <select data-statedefaultoption-gb="Select County" name="shipping_country" class="required" data-selected="<?= !empty($orderDetails['shipping_country'])?$orderDetails['shipping_country']:'US'; ?>" data-error-message="Please select your country!">
                    <option value="">Select Country</option>
                </select>
            </p>
            <p>
                <label>State: </label>
                <input type="text" name="shipping_state" placeholder="Your State" class="required" data-selected="<?= !empty($orderDetails['shipping_state'])?$orderDetails['shipping_state']:''; ?>" data-error-message="Please select your state!" readonly />
            </p>
            <p>
                <label>City: </label>
                <input type="text" name="shipping_city" placeholder="Your City" class="required" value="<?= !empty($orderDetails['shipping_city'])?$orderDetails['shipping_city']:''; ?>" data-error-message="Please enter your city!" />
            </p>
            <p>
                <label>Zip Code: </label>
                <input type="text" maxlength="10" name="shipping_zip" placeholder="Zip Code" class="required" value="<?= !empty($orderDetails['shipping_postcode'])?$orderDetails['shipping_postcode']:''; ?>" data-error-message="Please enter a valid zip code!" />
                <span id="zip-validate" style="display: none"><img src="<?= $path['assets_images'] ?>/ajax-loader.gif"></span>
            </p>
            <p>
                <label>Phone: </label>
                <input type="text" name="phone" placeholder="Phone" class="required" data-validate="phone" data-min-length="10" data-max-length="15" value="<?= !empty($orderDetails['customers_telephone'])?$orderDetails['customers_telephone']:''; ?>" data-error-message="Please enter a valid contact number!" />
            </p>
            <p>
                <label>Email: </label>
                <input type="text" name="email" placeholder="Email Address" class="required" value="<?= !empty($orderDetails['email_address'])?$orderDetails['email_address']:''; ?>"  data-validate="email" data-error-message="Please enter a valid email id!" />
                
            </p>
            <h3>Billing Details</h3>
                <p>
                    <label>Billing First Name</label>
                    <input type="text" name="billing_first_name" placeholder="Billing First Name" value="<?= !empty($orderDetails['billing_first_name'])?$orderDetails['billing_first_name']:''; ?>" data-error-message="Please enter your billing first name!" />
                </p>
                <p>
                    <label>Billing Last Name</label>
                    <input type="text" name="billing_last_name" placeholder="Billing Last Name" value="<?= !empty($orderDetails['billing_last_name'])?$orderDetails['billing_last_name']:''; ?>" data-error-message="Please enter your billing last name!" />
                </p>
                <p>
                    <label>Billing Address</label>
                    <input type="text" name="billing_address1" placeholder="Billing Address" value="<?= !empty($orderDetails['billing_street_address'])?$orderDetails['billing_street_address']:''; ?>" data-error-message="Please enter your billing address!" />
                </p>
                <p>
                    <label>Billing City</label>
                    <input type="text" name="billing_city" placeholder="Billing City" value="<?= !empty($orderDetails['billing_city'])?$orderDetails['billing_city']:''; ?>" data-error-message="Please enter your billing city!" />
                </p>
                <p>
                    <label>Billing Country: </label>
                    <select name="billing_country" data-selected="<?= !empty($orderDetails['billing_country'])?$orderDetails['billing_country']:''; ?>" data-error-message="Please select your billing country!">

                        <option value="">Select Country</option>
                    </select>
                </p>
                <p>
                    <label>Billing State: </label>

                    <input type="text" name="billing_state" data-selected="<?= !empty($orderDetails['billing_state'])?$orderDetails['billing_state']:''; ?>" placeholder="Billing State" data-error-message="Please enter your billing state!" />
                </p>
                <p>
                    <label>Billing Zip Code: </label>
                    <input type="text" name="billing_zip" placeholder="Billing Zip Code" value="<?= !empty($orderDetails['billing_postcode'])?$orderDetails['billing_postcode']:''; ?>" data-error-message="Please enter a valid billing zip code!" />

                </p>
                
                <p>
                <input type="submit" name="update_customer" value="Update Order Details" />
            </p>
        
                
            </div>
            </div>
            <input type="hidden" name="order_id" value="<?= $orderID;?>" />
            <p id="loading-indicator" style="display:none;">Processing...</p>
        </form>
        <?php require_once 'creditcard-details.tpl' ?>
        <?php require_once 'order-subscription-details.tpl' ?>
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
