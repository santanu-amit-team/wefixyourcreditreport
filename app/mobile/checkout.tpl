<html>
    <head>
        <?php include 'general/__header__.tpl'; ?>
    </head>
    <body>
        <?php perform_body_tag_open_actions(); ?>
        <form method="post" action="ajax.php?method=new_order_prospect" name="checkout_form" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <input type="hidden" name="limelight_charset" id="limelight_charset" value="utf-8" />
            <p>
                <label>Billing same as Shipping</label>
                <input type="radio" name="billingSameAsShipping" value="yes" checked="checked" /> YES
                <input type="radio" name="billingSameAsShipping" value="no" /> NO
            </p>
			<div class="billing-info" style="display:none;">
                <p>
                    <label>Billing First Name</label>
                    <input type="text" name="billingFirstName" placeholder="Billing First Name" data-error-message="Please enter your billing first name!" />
                </p>
                <p>
                    <label>Billing Last Name</label>
                    <input type="text" name="billingLastName" placeholder="Billing Last Name" data-error-message="Please enter your billing last name!" />
                </p>
                <p>
                    <label>Billing Address</label>
                    <input type="text" name="billingAddress1" placeholder="Billing Address" data-error-message="Please enter your billing address!" />
                </p>
                <p>
                    <label>Billing City</label>
                    <input type="text" name="billingCity" placeholder="Billing City" data-error-message="Please enter your billing city!" />
                </p>
                <p>
                    <label>Billing Country: </label>
                    <select name="billingCountry" data-error-message="Please select your billing country!">
                        <option value="">Select Country</option>
                    </select>
                </p>
                <p>
                    <label>Billing State: </label>
                    <input type="text" name="billingState" placeholder="Billing State" data-error-message="Please enter your billing state!" />
                </p>
                <p>
                    <label>Billing Zip Code: </label>
                    <input type="text" name="billingZip" placeholder="Billing Zip Code" data-error-message="Please enter a valid billing zip code!" />
                </p>
            </div>
            <div>
                <label> Selected Card Type: </label>
                <ul class="all-card-types">
                    <li class="visa">Visa</li>
                    <li class="master">Master Card</li>
                    <li class="discover">Discover</li>
                    <li class="amex">Amex</li>
                    <li class="jcb">JCB</li>
                    <li class="maestro">Maestro</li>
                    <li class="solo">Solo</li>
                    <li class="laser">Laser</li>
                    <li class="diners">Diners</li>
                    <li class="paypal">PayPal</li>
                </ul>
                <div class="clear"></div>
            </div>
            <p>
                <label>Select Card Type: </label>
                <select name="creditCardType" class="required" data-deselect="false" data-error-message="Please select valid card type!">
                    <option value="">Card Type</option>
                    <?php foreach($config['allowed_card_types'] as $key=>$value): ?>
                    <option value="<?= $key ?>"><?= ucfirst($value) ?></option>
                    <?php endforeach ?>
                </select>
            </p>
            <p>
                <label>Credit Card Number: </label>
                <input type="text" name="creditCardNumber" class="required" maxlength="16" data-error-message="Please enter a valid credit card number!" />
            </p>
            <p>
                <label>Expiry Month: </label>
                <select name="expmonth" class="required" data-error-message="Please select a valid expiry month!">
                    <?php get_months(); ?>
                </select>
            </p>
            <p>
                <label>Expiry Year: </label>
                <select name="expyear" class="required" data-error-message="Please select a valid expiry year!">
                    <?php get_years(); ?>
                </select>
            </p>
            <p>
                <label>CVV: </label>
                <input type="text" name="CVV" class="required" data-validate="cvv" maxlength="3" data-error-message="Please enter a valid CVV code!" />
            </p>
            <?php include 'general/__sepa__.tpl'; ?>
            <?php include 'general/__directdebit__.tpl'; ?>
            <p>
                <input type="checkbox" class="agree-checkbox" data-error-message="You have to agree with the terms in order to proceed!" /> I agree to the <a href="javascript:void(0);" onClick="javascript:openNewWindow('cms/2/terms-conditions');">Terms &amp; Conditions</a> of this site
            </p>
            <p>
                <input type="submit" name="" value="Place Order" />
            </p>
            <p id="loading-indicator" style="display:none;">Processing...</p>
            <p id="crm-response-container" style="display:none;">Limelight messages will appear here...</p>

        </form>
        <?php include 'general/__footer_link__.tpl'; ?>
        <div id="exitpopup-overlay" class="exitpopup-overlay">
            <div id="exit_pop" class="exitpop-content">
                <a href="javascript:void(0);"><img src="<?= $path['assets_images'] ?>/downsell.jpg" onClick="javascript:checkoutDownsell();" /></a>
            </div>
        </div>
        <?php
		include 'general/__scripts__.tpl';
		include 'general/__analytics__.tpl';
		perform_body_tag_close_actions();
		?>

    </body>
</html>
