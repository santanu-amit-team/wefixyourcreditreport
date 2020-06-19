<html>
    <head>
        <?php include 'general/__header__.tpl';
		?>
    </head>
    <body>
		<?php perform_body_tag_open_actions(); ?>
        <form method="post" action="ajax.php?method=downsell1" name="downsell_form1" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <input type="hidden" name="limelight_charset" id="limelight_charset" value="utf-8" />
            <p>
                <label>First Name: </label>
                <input type="text" name="firstName" placeholder="First Name" class="required" data-error-message="Please enter your first name!" />
            </p>
            <p>
                <label>Last Name: </label>
                <input type="text" name="lastName" placeholder="Last Name" class="required" data-error-message="Please enter your last name!" />
            </p>
            <p>
                <label>Address: </label>
                <input type="text" name="shippingAddress1" placeholder="Your Address" class="required" data-error-message="Please enter your address!" />
            </p>
            <p>
                <label>Country: </label>
                <select name="shippingCountry" class="required" data-selected="US" data-error-message="Please select your country!">
                    <option value="">Select Country</option>
                </select>
            </p>
            <p>
                <label>State: </label>
                <input type="text" name="shippingState" placeholder="Your State" class="required" data-error-message="Please select your state!" readonly />
            </p>
            <p>
                <label>City: </label>
                <input type="text" name="shippingCity" placeholder="Your City" class="required" data-error-message="Please enter your city!" />
            </p>
            <p>
                <label>Zip Code: </label>
                <input type="text" name="shippingZip" placeholder="Zip Code" class="required" data-error-message="Please enter a valid zip code!" />
            </p>
            <p>
                <label>Phone: </label>
                <input type="text" name="phone" placeholder="Phone" class="required" data-validate="phone" data-min-length="10" data-max-length="15" data-error-message="Please enter a valid contact number!" />
            </p>
            <p>
                <label>Email: </label>
                <input type="email" name="email" placeholder="Email Address" class="required" data-validate="email" data-error-message="Please enter a valid email id!" />
            </p>
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
                    <select name="billingCountry" data-error-message="Please select your billing Country!">
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
                </ul>
                <div class="clear"></div>
            </div>
            <p>
                <label>Select Card Type: </label>
                <select name="creditCardType" class="required" data-error-message="Please select valid card type!">
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
                <input type="checkbox" class="agree-checkbox" data-error-message="You have to agree with the terms in order to proceed!" /> I agree to the <a href="javascript:void(0);" onClick="javascript:openNewWindow('page-terms.php');">Terms &amp; Conditions</a> of this site
            </p>
            <p>
                <input type="hidden" name="campaignIds" value="13" />
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
		?>
		<?php perform_body_tag_close_actions(); ?>
    </body>
</html>
