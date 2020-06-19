<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        <h1>Orders</h1>
        
        <div class="row" id="order-lists">
            <div class="col-sm-1">Order ID</div>
            <div class="col-sm-1">Product</div>
            <div class="col-sm-2">Credit Card</div>
            <div class="col-sm-1">Order Status</div>
            <div class="col-sm-1">Order Total</div>
            <div class="col-sm-2">Order Date</div>
            <div class="col-sm-2">Next Recurring Product</div>
            <div class="col-sm-1">Next Recurring Date</div>
            <div class="col-sm-1">Action</div>
                
        </div>
        <div class="order-data">           
        </div>
        
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
