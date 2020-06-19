<!DOCTYPE HTML>
<html>

<head>

<?php require_once 'general/__header__.tpl' ?>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />





    <meta name="viewport" content="width=device-width, initial-scale=1" />









    <link rel="icon" type="image/x-icon" href="<?= $path['images'] ?>/cr-icon.png" />










    <title>Signup</title>


</head>

<body>
 <?php perform_body_tag_open_actions(); ?>

    <div class="content">
        <div class="main">



            <div class="aem-Grid aem-Grid--12 aem-Grid--default--12 ">

                <div class="signup-page aem-GridColumn aem-GridColumn--default--12">









                    <div data-emptytext="Signup - Page">









                        <link rel="stylesheet" href="<?= $path['css'] ?>/prod.min.css" type="text/css">



                        <!------------/*  Sign Up Page - Header  */------------->
                        <section id="header-signup">
                            <div class="header-signup-wrapper">
                                <div class="logo">
                                    <a href="/"><img src="<?= $path['images'] ?>/logo-black.png" alt="wefixyourcreditreport.com"></a>
                                </div>

                                <div class="header-cta">
                                    Questions? <a href="#"><span class="mobile-phone">1-<span class="creditRepairPhoneNumber phoneNumber">888-586-9913</span></span></a>
                                </div>
                            </div>

                            <section class="errors">888-586-9913
                                <span class="errors-icon">!</span> There are errors that need attention.
                            </section>
                        </section>
                        <!------------/*  End of Sign Up Page - Header  */------------->





                        <!------------/*  Sign Up Page - 1  */------------->
                        <section id="signup-v2" style="opacity: 1;height: auto;">

                            <div class="steps-container">
                                <ul class="steps">
                                    <li class="active">Personal Information</li>
                                    <li>Our Services</li>
                                    <li>Legal Agreements</li>
                                </ul>
                            </div>

                            <div class="step1-wrapper">

                                <h2 class="family-sign-up-wording">Let's Start By Getting Some Info</h2>

                                  <form method="post" action="ajax.php?method=new_prospect" name="prospect_form1" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
                                    <fieldset class="contact_info">
                                        <div class="block floating_label first_name">
                                            <!-- <input type="text" required id="FirstName" class="input_first_name" name="firstName" maxlength="25" value="" data-rule-validchar="true" aria-required="true"> -->
                                             <input type="text" name="firstName" placeholder="First Name" class="required" value="<?= $_COOKIE['firstName']?>" data-error-message="Please enter your first name!" />
                                            <label for="FirstName">First Name</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                        <div class="block floating_label last_name">
                                            <!-- <input type="text" required id="LastName" class="input_last_name" name="lastName" maxlength="25" value="" data-rule-validchar="true" aria-required="true"> -->
                                             <input type="text" name="lastName" placeholder="Last Name" class="required" value="<?= $_COOKIE['lastName']?>" data-error-message="Please enter your last name!" />
                                            <label for="LastName">Last Name</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                        <div class="block floating_label email">
                                          <!--   <input type="email" required id="Email" class="input_email" name="address" maxlength="50" value="" data-rule-email="true" aria-required="true"> -->
                                          <input type="text" name="email" placeholder="Email Address" class="required" value="<?= $_COOKIE['email']?>" 
                                                   data-validate="email" 
                                                   data-error-message="Please enter a valid email id!" />
                                            <label for="Email">Email</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                        <div class="block floating_label phone1">
                                           <!--  <input type="tel" required="" id="Phone" class="input_phone1" name="number" value="" data-rule-phoneus="true" data-form-format-helper="formatPhoneNumber" aria-required="true" maxlength="14"> -->
                                                <input type="text" name="phone" placeholder="Phone" class="required" data-validate="phone" data-min-length="10" data-max-length="15" value="<?= $_COOKIE['phone']?>" data-error-message="Please enter a valid contact number!" />
                                            <label for="Phone">Phone</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                       <!--  <div class="block floating_label street">
                                            <input type="text" required id="Address" class="input_street" name="line1" maxlength="60" value="" data-rule-validchar="true" aria-required="true">
                                            <label for="Address">Street Address</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                        <div class="block floating_label zip">
                                            <input type="tel" required id="Zip" class="input_zip" name="zipCode" data-form-format-helper="forceNumeric" data-rule-zipcodeus="true" maxlength="5" value="" aria-required="true">
                                            <label for="Zip">Zip Code</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                            <a style="display: none;color: #2d9ed7;text-decoration: underline;" class="california-policy-btn" href="#" target="_blank">California
                                                Privacy Rights</a>
                                        </div> -->
                                          <div class="block floating_label street">
                                                <!-- <input type="text" required="" id="Address" class="input_street" name="street" maxlength="60" value="" data-rule-validchar="true" aria-required="true" placeholder="Address" /> -->
                                                <input type="text" name="shippingAddress1" placeholder="Your Address" class="required" value="<?= $_COOKIE['shippingAddress1']?>" data-error-message="Please enter your address!" />
                                                <label for="Address">Address</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                            <div class="block floating_label street">
                                                <!-- <input type="text" required="" id="Address" class="input_street" name="street" maxlength="60" value="" data-rule-validchar="true" aria-required="true" placeholder="Address" /> -->
                                                <input type="text" name="shippingState" placeholder="Your State" class="required" data-selected="" data-error-message="Please select your state!" readonly value="" id="shippingState" />

                                                
                                                <input type="hidden" name="state" value="<?= $_COOKIE['shippingState']?>" id="state">
                                                 <input type="hidden" name="statefull" value="<?= $_COOKIE['shippingStatefull']?>" id="statefull">
                                                <label for="Address">State</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                              <div class="block floating_label street">
                                                <!-- <input type="text" required="" id="Address" class="input_street" name="street" maxlength="60" value="" data-rule-validchar="true" aria-required="true" placeholder="Address" /> -->
                                                <input type="text" name="shippingCity" placeholder="Your City" class="required" value="<?= $_COOKIE['shippingCity']?>" data-error-message="Please enter your city!" />
                                                <label for="Address">City</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                              <div class="block floating_label street" style="display:none">
                                                <!-- <input type="text" required="" id="Address" class="input_street" name="street" maxlength="60" value="" data-rule-validchar="true" aria-required="true" placeholder="Address" /> -->
                                                <select name="shippingCountry" class="required" data-selected="US" data-error-message="Please select your country!">
                                                <option value="">Select Country</option>
                                                </select>
                                                <label for="Address">Country</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                            <div class="block floating_label zip" style="text-align: left">
                                              <!--   <input type="tel" required="" id="Zip" class="input_zip" name="zip" data-form-format-helper="forceNumeric" data-rule-zipcodeus="true" maxlength="5" value="" aria-required="true" placeholder="Zip Code" /> -->
                                               <input type="text" name="shippingZip" placeholder="Zip Code" class="required" value="<?= $_COOKIE['shippingZip']?>" data-error-message="Please enter a valid zip code!" />
                                             <span id="zip-validate" style="display: none"><img src="<?= $path['assets_images'] ?>/ajax-loader.gif"></span>
                                                <label for="Zip">Zip Code</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                               <!--  <a style="display: none;text-decoration: underline;margin-top: 5px;" id="california-policy-btn" href="#" target="_blank">California
                                                    Privacy Rights</a> -->
                                            </div>
                                    </fieldset>

                                   <!--  <div class="ffhd-box">
                                        <label for="yes-ffd">Would you like to sign up with a friend or family member
                                            today and each receive 50% off your first work fee?</label>

                                        <div class="styled_checkbox" style="margin-bottom: 5px;">
                                            <input type="checkbox" value="yes" class="signup-checkbox" id="yes-ffd" name="ffhd-check">
                                            <label for="yes-ffd"></label>
                                        </div>
                                        <label for="yes-ffd" class="ffhd-check-text">Yes</label>
                                    </div> -->
                                

                                <h2 class="family-sign-up-wording">Friend or Family Member's Contact Information</h2>
<!-- 
                                <form name="Signup-Form-ffhd" id="Signup-Form-ffhd" method="POST" action="" onsubmit="" novalidate="novalidate">
                                    <input type="hidden" name="last_step" value="step1">

                                    <fieldset class="contact_info">
                                        <div class="block floating_label first_name">
                                            <input type="text" required id="FirstName-ffhd" class="input_first_name" name="firstName" maxlength="25" value="" data-rule-validchar="true" aria-required="true">
                                            <label for="FirstName">First Name</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                        <div class="block floating_label last_name">
                                            <input type="text" required id="LastName-ffhd" class="input_last_name" name="lastName" maxlength="25" value="" data-rule-validchar="true" aria-required="true">
                                            <label for="LastName">Last Name</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                        <div class="block floating_label email">
                                            <input type="email" required id="Email-ffhd" class="input_email" name="address" maxlength="50" value="" data-rule-email="true" aria-required="true">
                                            <label for="Email">Email</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                        <div class="block floating_label phone1">
                                            <input type="tel" required="" id="Phone-ffhd" class="input_phone1" name="number" value="" data-rule-phoneus="true" data-form-format-helper="formatPhoneNumber" aria-required="true" maxlength="14">
                                            <label for="Phone">Phone</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                        <div class="block floating_label street">
                                            <input type="text" required id="Address-ffhd" class="input_street" name="line1" maxlength="60" value="" data-rule-validchar="true" aria-required="true">
                                            <label for="Address">Street Address</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                        </div>
                                        <div class="block floating_label zip">
                                            <input type="tel" required id="Zip-ffhd" class="input_zip" name="zipCode" data-form-format-helper="forceNumeric" data-rule-zipcodeus="true" maxlength="5" value="" aria-required="true">
                                            <label for="Zip">Zip Code</label>
                                            <em class="field-text"></em>
                                            <span class="field-icon"></span>
                                            <a style="display: none;color: #2d9ed7;text-decoration: underline;" class="california-policy-btn" href="#" target="_blank">California
                                                Privacy Rights</a>
                                        </div>
                                    </fieldset>
                                </form> -->

                                <div class="submit_button_wrap">
                                    <button type="submit" name="contact-info-submit" class="submit-info submit-button" autocomplete="off">
                                        See Our Services
                                    </button>

                                    <div class="loading-box">
                                        <p class="loading-text-box loading-text-anim">
                                            <span class="loading-text-1">l</span>
                                            <span class="loading-text-2">o</span>
                                            <span class="loading-text-3">a</span>
                                            <span class="loading-text-4">d</span>
                                            <span class="loading-text-5">i</span>
                                            <span class="loading-text-6">n</span>
                                            <span class="loading-text-7">g</span>
                                            <span class="loading-text-8">.</span>
                                            <span class="loading-text-9">.</span>
                                            <span class="loading-text-10">.</span>
                                        </p>
                                        <div class="loading"></div>
                                    </div>
                                    <div class="check-boundaries createUser">
                                        <div class="box createUser"></div>
                                        <div class="check createUser"></div>
                                    </div>
                                    <div class="error-boundaries createUser">
                                        <div class="error-box createUser"></div>
                                        <div class="error createUser"></div>
                                    </div>
                                </div>
</form>
                                <div class="disclaimer_wrap">
                                    <p>By clicking 'See Our Services' I agree by electronic signature to: (1) be contacted by credit repair or credit
                                        repair marketing by a live agent, artificial or prerecorded voice,and SMS text at my residential or
                                        cellular number, dialed manually or by autodialer, and by email (consent to be contacted is not a
                                        condition to purchase services); and (2) the <a href="javascript:void(0);" onclick="javascript:openNewWindow('./page-privacy.php','modal'); return false;" target="_blank">Privacy
                                                    Policy</a>
                                                and <a href="javascript:void(0);" onclick="javascript:openNewWindow('./page-terms.php','modal'); return false;" target="_blank">Terms of Use</a> (including this <a href="#" target="_blank">arbitration
                                            Provision</a>).</p>
                                </div>

                                <div class="cert_wrapper">
                                    <span id="siteseal">
                                    <img style="cursor:pointer;cursor:hand" src="<?= $path['images'] ?>/siteseal_gd_3_h_l_m.gif" alt="SSL site seal - click to verify"></span>
                                </div>

                            </div>

                        </section>
                        <!------------/* End of Sign Up Page - 1  */------------->





                        <!------------/*  Sign Up Page - Loading  */------------->
                        <div class="loading-box full-sized">
                            <div class="loading"></div>
                        </div>
                        <!------------/*  END of Sign Up Page - Loading  */------------->
                        <!------------/*  Modal Pop Up  */------------->
                        <div class="overlay-modal" style="display: none">
                            <div class="modal-box">
                                <div class="modal-content">
                                </div>
                                <div class="submit_button_wrap" style="display: none">
                                    <button type="submit" name="sign-up-modal" class="submit-info submit_button" autocomplete="off">
                                        Continue
                                    </button>

                                    <div class="loading-box">
                                        <p class="loading-text-box loading-text-anim">
                                            <span class="text-anim loading-text-1">l</span>
                                            <span class="text-anim loading-text-2">o</span>
                                            <span class="text-anim loading-text-3">a</span>
                                            <span class="text-anim loading-text-4">d</span>
                                            <span class="text-anim loading-text-5">i</span>
                                            <span class="text-anim loading-text-6">n</span>
                                            <span class="text-anim loading-text-7">g</span>
                                            <span class="text-anim loading-text-8">.</span>
                                            <span class="text-anim loading-text-9">.</span>
                                            <span class="text-anim loading-text-10">.</span>
                                        </p>
                                        <div class="loading"></div>
                                    </div>
                                    <div class="check-boundaries">
                                        <div class="box"></div>
                                        <div class="check"></div>
                                    </div>
                                    <div class="error-boundaries">
                                        <div class="error-box"></div>
                                        <div class="error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!------------/*  End of Modal Pop Up  */------------->
                    </div>
                </div>


            </div>

        </div>
    </div>
    <p id="loading-indicator" style="display:none;">Processing...</p>
<?php require_once 'general/__scripts__.tpl' ?>
<?php require_once 'general/__analytics__.tpl' ?>
<?php perform_body_tag_close_actions(); ?>
 <!--    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->
    <script src="<?= $path['js'] ?>/jquery-validate-v1.13.1.min.js"></script>
<!--     <script type="text/javascript" src="<?= $path['js'] ?>/forms.js"></script>
    <script type="text/javascript" src="<?= $path['js'] ?>/prod.min.js"></script>
    <script type="text/javascript" src="<?= $path['js'] ?>/prod.min2.js"></script> -->
    <p class='site_bottom_copyright_ref_and_tid' style='display:none;' id='footer_tid'>(0)</p>
    <script type="text/javascript">
        $(document).ready(function() {
setTimeout(function(){
        //$('#shippingState :selected').text($('#state').val());
       // $("#shippingState option:selected").text($('#state').val());
       // $().find('option:selected').val($('#state').val());
        //$('#shippingState'). children("option:selected"). val($('#state').val());
        $('#shippingState option:selected').val($('#state').val());
        $('#shippingState option:selected').text($('#statefull').val());
console.log('statefull'+$('#statefull').val());
console.log('state'+$('#state').val());
        }, 3000);




});

    </script>
</body>

</html>
