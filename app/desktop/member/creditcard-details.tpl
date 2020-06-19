
        <h3>Credit card Details</h3>
        <div id="msg"></div>
        <form method="post" name="creditcard_details" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <div class="row">
                <div class="col-sm-12">
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
           
            </div>
            </div>
            <input type="hidden" name="order_id" value="<?= $orderID;?>" />
            <p id="loading-indicator" style="display:none;">Processing...</p>
            <input type="submit" name="update_cc_details" value="Update Credit Card Details" />
        </form>
        
