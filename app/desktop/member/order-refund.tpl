<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        <h1>Order Refund</h1>
        <div id="msg"></div>
        <form method="post" name="refund-order" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <div class="row">
                <div class="col-sm-12">
                    <p>
                        <label>Order Total: $<?= $orderTotal ?></label>
                        
                    </p>
                    
                    <input type="hidden" class="required" name="refund_amount" value="<?= $orderTotal ?>">
                    
                </div>
            </div>
            <input type="hidden" name="order_id" value="<?= $orderID;?>" />
            <input type="hidden" name="order_amount" value="<?= $orderTotal ?>">
            <input type="submit" name="refund_order" value="Refund Order" />
            <p id="loading-indicator" style="display:none;">Processing...</p>
        </form>
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
