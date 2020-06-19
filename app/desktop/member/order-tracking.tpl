<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        
        <form method="post" name="tracking_order" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <input type="hidden" id="status_code" value="<?= (!empty($trackDetails['statusCode']) ? $trackDetails['statusCode'] : ''); ?>" />
            <?php
            if(empty($trackDetails)){
            ?>
            <div class="row">
            <div class="col-sm-12">
            <p>
                <label>Tracking Number: </label>
                <input type="text" name="tracking_no" placeholder="Tracking Number" class="required" data-error-message="Please enter your first name!" />
            </p>
            <p>
                <button name="usps_track" type="button" class="btn btn-outline-primary">Track via USPS</button>
            </p>
            
            </div>
            </div>
            <?php
            }
            ?>
            <div class="col-sm-12">
                    <div class="row" >
                        <h3>Track Summary</h3>
                        <div class="col-sm-12" id="track_summary"><?= !empty($trackDetails['trackSummary']) ? $trackDetails['trackSummary'] : 'No Data'; ?></div>
                    </div>

                    <div class="row" >
                        <h3>Track Details</h3>
                        <div class="col-sm-12" id="track_details"><?= !empty($trackDetails['trackDetails']) ? $trackDetails['trackDetails'] : 'No Data'; ?></div>
                    </div>
            </div>
            <p id="loading-indicator" style="display:none;">Processing...</p>
        </form>
        
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
