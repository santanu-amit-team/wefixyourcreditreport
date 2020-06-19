<html>
    <head>
        <?php include 'general/__header__.tpl'; ?>
    </head>
    <body>
        <?php perform_body_tag_open_actions(); ?>
        <h1>One Click Upsell 1!</h1>
        <form name="is-upsell" class="is-upsell" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <input type="hidden" name="limelight_charset" id="limelight_charset" value="utf-8" />
            <input type="submit" value="Buy Now!" />
        </form>
        <a href="<?= get_no_thank_you_link() ?>">No, Thank You.</a>
        <?php include 'general/__scripts__.tpl'; ?>
        <p id="loading-indicator" style="display:none;">Processing...</p>
        <?php include 'general/__analytics__.tpl'; ?>
        <?php perform_body_tag_close_actions(); ?>
    </body>
</html>
