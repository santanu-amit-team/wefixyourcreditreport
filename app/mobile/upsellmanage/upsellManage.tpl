
<html>
    <head>
        <?php include 'general/__header__.tpl'; ?>
    <h1>mobile</h1>
    </head>
    <body>
        <?php perform_body_tag_open_actions(); ?>
        <?php perform_dynamic_upsell_content_parse($currentUpsellDetails);?>
     <!--   <form name="is-upsell" class="is-upsell" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <input type="hidden" name="limelight_charset" id="limelight_charset" value="utf-8" />
            <input type="submit" value="Buy Now!" />
        </form>-->
        <a onclick="setSkipLink('<?= get_no_thank_you_link() ?>')" href="javascript:void(0);">No, Thank You.</a>
        <?php include 'general/__scripts__.tpl'; ?>
          <script>
        function setSkipLink(lnk){
            let xhr = new XMLHttpRequest();
            xhr.open("GET", app_config.offer_path + AJAX_PATH + "extensions/upsellmanager/set-skip-link?link="+lnk);
            xhr.send();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                   // console.log("casd"+xhr.responseText);
                    window.location.href = lnk;
                }
            };
        }
        </script>
        <p id="loading-indicator" style="display:none;">Processing...</p>
        <?php include 'general/__analytics__.tpl'; ?>
        <?php perform_body_tag_close_actions(); ?>
    </body>
</html>
