<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        <h1>
            <?php
            if($refundApproved){
                
                echo ($isRefundSuccess)?'Refund process has been successfully completed':'Refund process has failed';
                
            }else{
                
                echo "Refund request rejected";
            
            }
            
            ?></h1>
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
