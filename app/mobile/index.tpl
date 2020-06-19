<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
    </head>
    <body>
        <?php perform_body_tag_open_actions(); ?>
        Mobile version
		<form method="post" action="ajax.php?method=new_prospect" name="prospect_form1" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
            <input type="hidden" name="limelight_charset" id="limelight_charset" value="utf-8" />
            <p>
                <label>First Name: </label>
                <input type="text" name="firstName" placeholder="First Name" class="required" value="" data-error-message="Please enter your first name!" />
            </p>
            <p>
                <label>Last Name: </label>
                <input type="text" name="lastName" placeholder="Last Name" class="required" value="" data-error-message="Please enter your last name!" />
            </p>
            <p>
                <label>Address: </label>
                <input type="text" name="shippingAddress1" placeholder="Your Address" class="required" value="" data-error-message="Please enter your address!" />
            </p>
            <p>
                <label>Country: </label>
                <select name="shippingCountry" class="required" data-selected="" data-error-message="Please select your country!">
                    <option value="">Select Country</option>
                </select>
            </p>
            <p>
                <label>State: </label>
                <input type="text" name="shippingState" placeholder="Your State" class="required" data-selected="" data-error-message="Please select your state!" readonly />
            </p>
            <p>
                <label>City: </label>
                <input type="text" name="shippingCity" placeholder="Your City" class="required" value="" data-error-message="Please enter your city!" />
            </p>
            <p>
                <label>Zip Code: </label>
                <input type="text" name="shippingZip" placeholder="Zip Code" class="required" value="" data-error-message="Please enter a valid zip code!" />
                <span id="zip-validate" style="display: none"><img src="<?= $path['assets_images'] ?>/ajax-loader.gif"></span>
            </p>
            <p>
                <label>Phone: </label>
                <input type="text" name="phone" placeholder="Phone" class="required" data-validate="phone" data-min-length="10" data-max-length="15" value="" data-error-message="Please enter a valid contact number!" />
            </p>
            <p>
                <label>Email: </label>
                <input type="text" name="email" placeholder="Email Address" class="required" value="" 
                       data-validate="email" 
                       data-error-message="Please enter a valid email id!" />
                <span id="mxcheck-placeholder" style="display:none;"><img src="<?= $path['assets_images'] ?>/ajax-loader.gif" /></span>
                <span id="mxcheck-message" style="display:none;"></span>
            </p>
            <p>
                <input type="submit" name="create_prospect" value="Rush My Order" />
            </p>
            <p id="loading-indicator" style="display:none;">Processing...</p>
            <p id="crm-response-container" style="display:none;">Limelight messages will appear here...</p>
        </form>

        <?php require_once 'general/__footer_link__.tpl'; ?>
        <div id="exitpopup-overlay" class="exitpopup-overlay">
            <div id="exit_pop" class="exitpop-content">
                <a href="<?= get_exit_pop_url('step1', 1); ?>">
                    <img src="<?= $path['assets_images'] ?>/downsell.jpg" />
                </a>
            </div>
        </div>
        <?php require_once 'general/__scripts__.tpl' ?>
        <?php require_once 'general/__analytics__.tpl' ?>
        <?php perform_body_tag_close_actions(); ?>
    </body>
</html>
