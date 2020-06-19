<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        <h1>Order Cancel</h1>
        <div id="msg"></div>
        <form method="post" name="cancel-order" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <div class="row">
                <div class="col-sm-12">
                    <p>
                        <label>Cancel Reason: </label>
                        <textarea class="required" name="cancel_reason" placeholder="Please specify the reason for cancelling"></textarea>
                    </p>
                </div>
            </div>
            <input type="hidden" name="order_id" value="<?= $orderID;?>" />
            <input type="submit" name="cancel_order" value="Cancel Order" />
            <p id="loading-indicator" style="display:none;">Processing...</p>
        </form>
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
