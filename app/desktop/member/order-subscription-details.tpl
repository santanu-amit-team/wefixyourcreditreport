
        <h3>Order Subscription Details</h3>
        <div id="msg"></div>
        <form method="post" name="order_subscription_details" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <div class="row">
                <div class="col-sm-12">
            <p>
                <label>Next Subscription Product : <?= !empty($orderDetails['products[0][next_subscription_product]'])? $orderDetails['products[0][next_subscription_product]']:''; ?> </label>
                <input type="text" name="next_rebill_product" placeholder="Next Subscription Product (ID)" class="required" value="<?= !empty($orderDetails['products[0][next_subscription_product_id]'])?$orderDetails['products[0][next_subscription_product_id]']:''; ?>" data-error-message="Please enter next recurring product ID!" />
            </p>
            <p>
                <label>Next Subscription Date: (MM/DD/YYYY)</label>
                <input class="datepicker" data-date-format="mm/dd/yyyy">
                <input type="text" readonly="" name="recurring_date" placeholder="Next Subscription Date" class="required" value="<?= !empty($orderDetails['products[0][recurring_date]'])? date('m/d/Y',strtotime($orderDetails['products[0][recurring_date]'])):''; ?>" data-error-message="Please enter next recurring date!" />
            </p>
           
            </div>
            </div>
			 <input type="hidden" name="existing_product_ID" value="<?= !empty($orderDetails['products[0][next_subscription_product_id]'])?$orderDetails['products[0][next_subscription_product_id]']:'';?>" />
            <input type="hidden" name="order_id" value="<?= $orderID;?>" />
            <p id="loading-indicator" style="display:none;">Processing...</p>
            <input type="submit" name="update_subscription" value="Update Subscription Details" />
        </form>
        
