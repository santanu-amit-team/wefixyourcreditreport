<!DOCTYPE HTML>
<html>

<head>
<meta charset="utf-8"/>



<title><?= get_meta_details('site_title', $steps['current']['id']) ?></title>



<meta name="description" content="<?= get_meta_details('meta_description', $steps['current']['id']); ?>" />



<?php if(!empty($config['block_robots'])): ?>

<meta name="robots" content="noindex,nofollow,noarchive,nosnippet,noydir,noodp" />

<?php endif; ?>



<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<meta http-equiv="content-language" content="en-us" />

 

<meta name="apple-mobile-web-app-capable" content="yes"/>

<meta name="apple-mobile-web-app-status-bar-style" content="black"/>

<meta name="HandheldFriendly" content="true"/>

<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no"/>



<link rel="stylesheet" href="<?= $path['assets_css'] . '/app.css' ?>" />

<link rel="icon" type="image/x-icon" href="<?= $path['images'] ?>/favicon.ico" />
<link rel="stylesheet" href="<?= $path['css'] ?>/custom.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/normalize.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/site.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/index.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/nouislider.min.css" type="text/css" /> 
<link rel="stylesheet" href="<?= $path['css'] ?>/how-it-works.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/lead-form.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/new-hero.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/hard-work.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/clientlibs.min.css" type="text/css">
<link rel="stylesheet" href="<?= $path['css'] ?>/page-pop.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/cr-hub.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/credit-ed.css" type="text/css"/>
<link rel="stylesheet" href="<?= $path['css'] ?>/cr-about-us.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/contact.css" type="text/css" />
<?php perfom_head_tag_close_actions(); ?>

   
</head>

<body>
 <?php perform_body_tag_open_actions(); ?>


    <div class="content">
        <div class="main">



            <div class="aem-Grid aem-Grid--12 aem-Grid--default--12 ">



            </div>




            <div class="aem-Grid aem-Grid--12 aem-Grid--default--12 ">

                <div class="htmlreference parbase aem-GridColumn aem-GridColumn--default--12">
                    <div id="header_wrapper">
                        <div class="container">
                            <a href="/"><img id="credit_repair_logo" src="<?= $path['images'] ?>/logo.png" alt="wefixyourcreditreport.com"></a>
                            <ul id="top_nav">
                                <li class="top_nav_member_login_wrapper" style="display: none;"><a class="top_nav_member_login" href="#">Log In<span class="top_nav_login_icon"></span></a></li>
                                <li class="top_nav_sign_up_wrapper"><a class="top_nav_sign_up get_started" href="javascript:void(0);">Get Started</a></li>
                                <li class="top_nav_phone_number_wrapper"><a class="top_nav_phone_number" href="#"><span class="phone_number"><span class="creditRepairPhoneNumber phoneNumber">888-586-9913</span></span><span class="mobile_text">Call</span><span class="mobile_text_icon"></span></a></li>
                            </ul>
                            <div id="mobile_nav_toggle" class="">Menu</div>
                            <ul id="navigation" class="">
                                <li><a href="fix-my-credit.php">Fix My Credit</a></li>
                                <li><a href="credit-education.php">Credit Education</a></li>
                                <li><a href="about-us.php">About Us</a></li>
                                <li class="navigation_login_wrap"><a class="navigation_login_btn" href="#">Log
                                        In<span class="navigation_login_icon"></span></a></li>
                                <li class="navigation_signup_wrap"><a class="navigation_signup_btn get_started" href="javascript:void(0);">Get Started</a></li>
                            </ul>
                        </div>
                    </div>
              </div> 

                <div class="htmlreference parbase aem-GridColumn aem-GridColumn--default--12">
                    <div id="page_pop_background"></div>

                    <!-- CR Terrance Hero -->
                    <div class="cr-hero-main">
                        <div class="hero-boundaries">
                            <div class="hero-container">

                                <p class="heading"><span class="heading-bold">They say</span>bad credit is forever <span class="heading-bold">We say</span>You can fix it</p>

                                <div class="hero-btn-container">
                                    <div class="button-wrapper">
                                        <p class="sub-header">Check your credit for FREE</p>
                                        <a class="call_btn get_started" href="javascript:void(0);" data-unique="phone-hero">Get Started</a>
                                        <a class="signup-online" data-unique="signup-hero" href="signup.php<?= make_query_string(); ?>">or sign up online »</a>
                                    </div>
                                </div>
                            </div>

                            <div class="hero-img"></div>

                            <div class="button-wrapper-mobile">
                                <div class="button-wrapper">
                                    <p class="sub-header">It’s time you tried credit repair.</p>
                                    <a class="call_btn" href="tel:+888-586-9913" data-unique="phone-hero"><span class="call_icon"></span><span class="phone_number">Call <span class="creditRepairPhoneNumber phoneNumber">888-586-9913</span></span></a>
                                    <a class="signup-online" data-unique="signup-hero" href="signup.php<?= make_query_string(); ?>">or sign up online »</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal"></div>
                    <div id="cr-modal">
                        <div class="modal-container">
                            <div class="modal-heading">
                                <span class="closeBtn">&#10005;</span>
                                <h1 class="modal-image"></h1>
                                <h2>You can improve your credit score.</h2>
                            </div>
                            <div class="modal-content">
                                <p class="call-now-heading"><span>&#65293;&#65293;&#65293;</span> Call now for a FREE
                                    <span>&#65293;&#65293;&#65293;</span>
                                </p>
                                <ul class="credit-list">
                                    <li>Personalized credit consultation</li>
                                    <li class="credit-rep">Credit report summary</li>
                                    <li>Score evaluation &amp; game plan</li>
                                </ul>
                                <div class="modal-buttons">
                                    <a class="call-now-btn" href="tel:+888-586-9913" data-unique="phone-modal">Call <span class="creditRepairPhoneNumber phoneNumber">888-586-9913</span></a>
                                    <a class="sign-up" href="signup.php<?= make_query_string(); ?>" data-unique="signup-modal">or sign up online &raquo;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End CR Terrance Hero -->

                </div>
                <div class="drips aem-GridColumn aem-GridColumn--default--12">
                    

                    <div data-emptytext="Drips Tool">
                        <div class="header-wrapper">
                            <div class="header">
                                <div class="logo">
                                </div>
                            </div>
                        </div>

                        <div class="lead-form">
                            <div class="form-spacing">
                                <div class="lead-form-container rescue" id="btop">

                                    <h2>We know a thing or two about credit repair.</h2>
                                    <div class="leader">Fill out the following information to find out how much fixing your credit could
                                        save
                                        you.
                                    </div>

                                   <!--  <form id="cr-lead-form" name="cr-lead-form" method="POST" action="" onsubmit="" novalidate="novalidate"> -->
                                    <form method="post"  name="prospect_form1" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8" id="formbtn">

                                        <div class="form">
                                            <div class="block floating_label first_name">
                                                <!-- <input type="text" required="" id="FirstName" class="input_first_name" name="first_name" maxlength="25" value="" data-rule-validchar="true" aria-required="true" placeholder="First Name" /> -->
                                                <input type="text" name="firstName" placeholder="First Name" class="required" value="" data-error-message="Please enter your first name!" />
                                                <label for="FirstName">First Name</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                            <div class="block floating_label last_name">
                                                <!-- <input type="text" required="" id="LastName" class="input_last_name" name="last_name" maxlength="25" value="" data-rule-validchar="true" aria-required="true" placeholder="Last Name" /> -->
                                                <input type="text" name="lastName" placeholder="Last Name" class="required" value="" data-error-message="Please enter your last name!" />
                                                <label for="LastName">Last Name</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                            <div class="block floating_label email">
                                                <!-- <input type="email" required="" id="Email" class="input_email" name="email" maxlength="50" value="" data-rule-email="true" aria-required="true" placeholder="Email" /> -->
                                                 <input type="text" name="email" placeholder="Email Address" class="required" value="" 
                                                   data-validate="email" 
                                                   data-error-message="Please enter a valid email id!" />
                                            <span id="mxcheck-placeholder" style="display:none;"><img src="<?= $path['assets_images'] ?>/ajax-loader.gif" /></span>
                                            <span id="mxcheck-message" style="display:none;"></span>
                                                <label for="Email">Email</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                            <div class="block floating_label phone">
                                                <!-- <input type="tel" required="" id="Phone" class="input_phone1" name="phone1" value="" data-rule-phoneus="true" data-form-format-helper="formatPhoneNumber" aria-required="true" maxlength="14" placeholder="Phone" /> -->
                                                <input type="text" name="phone" placeholder="Phone" class="required" data-validate="phone" data-min-length="10" data-max-length="15" value="" data-error-message="Please enter a valid contact number!" />
                                                <label for="Phone">Phone</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                            <div class="block floating_label street">
                                                <!-- <input type="text" required="" id="Address" class="input_street" name="street" maxlength="60" value="" data-rule-validchar="true" aria-required="true" placeholder="Address" /> -->
                                                <input type="text" name="shippingAddress1" placeholder="Your Address" class="required" value="" data-error-message="Please enter your address!" />
                                                <label for="Address">Address</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                            <div class="block floating_label street">
                                                <!-- <input type="text" required="" id="Address" class="input_street" name="street" maxlength="60" value="" data-rule-validchar="true" aria-required="true" placeholder="Address" /> -->
                                                <input type="text" name="shippingState" placeholder="Your State" class="required" data-selected="" data-error-message="Please select your state!" readonly id="shippingState" />
                                                <label for="Address">State</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                            </div>
                                              <div class="block floating_label street">
                                                <!-- <input type="text" required="" id="Address" class="input_street" name="street" maxlength="60" value="" data-rule-validchar="true" aria-required="true" placeholder="Address" /> -->
                                                <input type="text" name="shippingCity" placeholder="Your City" class="required" value="" data-error-message="Please enter your city!" />
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
                                               <input type="text" name="shippingZip" placeholder="Zip Code" class="required" value="" data-error-message="Please enter a valid zip code!" />
                                             <span id="zip-validate" style="display: none"><img src="<?= $path['assets_images'] ?>/ajax-loader.gif"></span>
                                                <label for="Zip">Zip Code</label>
                                                <em class="field-text"></em>
                                                <span class="field-icon"></span>
                                               <!--  <a style="display: none;text-decoration: underline;margin-top: 5px;" id="california-policy-btn" href="#" target="_blank">California
                                                    Privacy Rights</a> -->
                                            </div>
                                        </div>

                                        <div class="submit_button_wrap">
                                            <button type="button" name="lead-form-submit" class="btn-green submit-btn" autocomplete="off" id="formbtnsubmit">
                                                Submit Information
                                            </button>

                                            <div class="loading-box">
                                                <p class="loading-text-box loading-text-anim rescue">
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
                                                <div class="loading rescue"></div>
                                            </div>
                                            <div class="check-boundaries rescue">
                                                <div class="box rescue"></div>
                                                <div class="check rescue"></div>
                                            </div>
                                            <div class="error-boundaries rescue">
                                                <div class="error-box rescue"></div>
                                                <div class="error rescue"></div>
                                            </div>
                                        </div>

                                        <div class="disclaimer_wrap">
                                            <p>By clicking 'Submit Information' I agree by electronic signature to: (1) be contacted by
                                                credit
                                                repair or credit repair marketing by a live agent, artificial or prerecorded voice,and
                                                SMS
                                                text
                                                at
                                                my residencial or cellular number, dialed manually or by autodialer, and by email
                                                (consent
                                                to be
                                                contacted is not a condition to purchase services); and (2) the <a href="javascript:void(0);" onclick="javascript:openNewWindow('./page-privacy.php','modal'); return false;" target="_blank">Privacy
                                                    Policy</a>
                                                and <a href="javascript:void(0);" onclick="javascript:openNewWindow('./page-terms.php','modal'); return false;" target="_blank">Terms of Use</a>
                                                (including
                                                this <a href="#" target="_blank">arbitration
                                                    Provision</a>).
                                            </p>
                                        </div>
                                    </form>

                                </div>
                                <div class="slider-app-wrapper rescue">
                                    <div class="slider-container">

                                        <h2>How much could you save?</h2>

                                        <div class="header">
                                            <span id="credit-type">Bad</span> credit can save you
                                            <div id="savings-amount-wrapper" class="red">
                                                <sup>$</sup><span id="credit-savings-amount">0</span>*
                                            </div>
                                        </div>
 <div id="slider" class="noUi-target noUi-ltr noUi-horizontal">
                                <div class="noUi-base">
                                    <div class="noUi-connects">
                                        <div class="noUi-connect" style="transform: translate(0%, 0px) scale(0.25, 1);"></div>
                                    </div>
                                    <div class="noUi-origin" style="transform: translate(-75%, 0px); z-index: 4;">
                                        <div class="noUi-handle noUi-handle-lower" data-handle="0" tabindex="0" role="slider" aria-orientation="horizontal" aria-valuemin="17.0" aria-valuemax="100.0" aria-valuenow="25.0" aria-valuetext="25.00">
                                            <div class="noUi-touch-area">Bad</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                        <div class="slider-labels-wrapper">
                                            <div class="min">300</div>
                                            <div class="max">850</div>
                                        </div>

                                        <table>
                                            <thead>
                                                <tr>
                                                    <th class="table-label">Goals</th>
                                                    <th>Interest Rate</th>
                                                    <th>Monthly Payment</th>
                                                    <th>30 Year Savings</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="even">
                                                    <td class="table-label">Mortgage</td>
                                                    <td id="mortgage-rate">8.36%</td>
                                                    <td id="mortgage-payment">$1,900</td>
                                                    <td id="mortgage-savings" class="red">$0</td>
                                                </tr>
                                                <tr class="odd">
                                                    <td class="table-label">Credit Card</td>
                                                    <td id="cc-rate">22.99%</td>
                                                    <td id="cc-payment">$58</td>
                                                    <td id="cc-savings" class="red">$0</td>
                                                </tr>
                                                <tr class="even">
                                                    <td class="table-label">Auto Loan</td>
                                                    <td id="auto-rate">15.11%</td>
                                                    <td id="auto-payment">$697</td>
                                                    <td id="auto-savings" class="red">$0</td>
                                                </tr>
                                                <tr class="odd">
                                                    <td class="table-label">Personal Loan</td>
                                                    <td id="ploan-rate">210%</td>
                                                    <td id="ploan-payment">$1,810</td>
                                                    <td id="ploan-savings" class="red">$0</td>
                                                </tr>
                                                <tr class="even">
                                                    <td class="table-label">Apartment</td>
                                                    <td id="rental-rate">N/A</td>
                                                    <td id="rental-payment" class="red">Declined</td>
                                                    <td id="rental-savings" class="red">Declined</td>
                                                </tr>
                                                <tr class="odd">
                                                    <td class="table-label">Employment</td>
                                                    <td id="job-rate">N/A</td>
                                                    <td id="job-payment" class="red">Declined</td>
                                                    <td id="job-savings" class="red">Declined</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div class="slider-disclaimer">*Assumptions: 30 year period with a $2,000 limit credit card
                                            every
                                            month; $250,000 mortgage with a 30 year term; four 48 month car loans; two $10,000 personal
                                            loans; and insurance every month.
                                        </div>

                                        <div class="cta-text">Call for a free credit evaluation</div>
                                        <div class="btn-wrapper">
                                            <a class="btn-new" href="tel:+888-586-9913">Call <span class="creditRepairPhoneNumber phoneNumber">888-586-9913</span></a>
                                            <div><a class="text-link" href="signup.php<?= make_query_string(); ?>">or sign up online »</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        






                    </div>
                </div>
                <div class="htmlreference parbase aem-GridColumn aem-GridColumn--default--12">

                    <div class="hard-work-wrapper">
                        <div class="hard-work">

                            <h2>We've been hard at work for our members</h2>

                            <div class="chart-wrapper">
                                <div class="circle1-wrapper">
                                    <div class="circle-padding">
                                        <div class="circle-icon"></div>
                                        <div class="number">1.8+ million</div>
                                        <div class="big-content">removals</div>
                                        <div class="small-content">since 2012</div>
                                    </div>
                                </div>

                                <div class="circle2-wrapper">
                                    <div class="circle-padding">
                                        <div class="circle-icon"></div>
                                        <div class="number">19+ million</div>
                                        <div class="big-content">challenges <span>&amp;</span> disputes</div>
                                        <div class="small-content">sent since we started business</div>
                                    </div>
                                </div>

                                <div class="circle3-wrapper">
                                    <div class="circle-padding">
                                        <div class="circle-icon"></div>
                                        <div class="number">1+ million</div>
                                        <div class="big-content">interventions</div>
                                        <div class="small-content">sent in 2019</div>
                                    </div>
                                </div>
                            </div>


                            <div class="btn-wrapper">
                                <a class="btn-new get_started" href="javascript:void(0);">Get Started</a>
                                <div><a class="text-link" href="signup.php<?= make_query_string(); ?>">or sign up online »</a></div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="htmlreference parbase aem-GridColumn aem-GridColumn--default--12">
                    <!-------------------------- How Credit Works ---------------------------->
                    <div id="how-works-new-wrapper">
                        <div class="how-works-new-container">
                            <h2>How will credit repair work?</h2>

                            <div class="how-works-col-wrapper">
                                <div class="how-works-icon challenge"></div>
                                <div>
                                    <div class="how-works-title">Challenge</div>
                                    <div class="how-works-content">First and foremost we will challenge all the questionable negative items with all of the three bureaus which will ensure that your credit report is fair and accurate.</div>
                                </div>
                            </div>

                            <div class="how-works-col-wrapper">
                                <div class="how-works-icon dispute"></div>
                                <div>
                                    <div class="how-works-title">Dispute</div>
                                    <div class="how-works-content">We will ask your relevant creditor to verify the reported negative item. If they can’t prove it, the law requires them to stop reporting these items.</div>
                                </div>
                            </div>

                            <div class="how-works-col-wrapper">
                                <div class="how-works-icon monitor"></div>
                                <div>
                                    <div class="how-works-title">Monitor</div>
                                    <div class="how-works-content">We will continuously monitor your credit and address all the additional issues that might pop up from time to time. You will be able to reach your credit goals much more easily.</div>
                                </div>
                            </div>

                            <div class="mobile-carousels" id="how-works-mobile">
                                <ul class="carousel">
                                    <li>
                                        <a id="carousel-ext" href="#" data-unique="carousel-items">
                                            <ul class="carousel-boundaries">
                                                <li class="selected">
                                                    <div class="carousel-item">
                                                        <div class="how-works-col-wrapper-mobile">
                                                            <div class="how-works-icon challenge"></div>
                                                            <div>
                                                                <div class="how-works-title">Challenge</div>
                                                                <div class="how-works-content">First and foremost we will challenge all the questionable negative items with all of the three bureaus which will ensure that your credit report is fair and accurate.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="move-right">
                                                    <div class="carousel-item">
                                                        <div class="how-works-col-wrapper-mobile">
                                                            <div class="how-works-icon dispute"></div>
                                                            <div>
                                                                <div class="how-works-title">Dispute</div>
                                                                <div class="how-works-content">We will ask your relevant creditor to verify the reported negative item. If they can’t prove it, the law requires them to stop reporting these items.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="carousel-item">
                                                        <div class="how-works-col-wrapper-mobile">
                                                            <div class="how-works-icon monitor"></div>
                                                            <div>
                                                                <div class="how-works-title">Monitor</div>
                                                                <div class="how-works-content">We will continuously monitor your credit and address all the additional issues that might pop up from time to time. You will be able to reach your credit goals much more easily.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </a>
                                    </li>
                                </ul>
                                <div class="prev" id="prev">&#10094</div>
                                <div class="next" id="next">&#10095</div>
                            </div>


                            <div class="btn-wrapper">
                                <p>For a <strong>FREE</strong> 10-minute credit analysis</p>

                                <a class="btn-new" href="tel:+888-586-9913">Call <span class="creditRepairPhoneNumber phoneNumber">888-586-9913</span></a>
                                <div><a class="text-link" href="signup.php<?= make_query_string(); ?>">or sign up here »</a></div>
                            </div>

                        </div>
                    </div>

                    <!-------------------------- End How Credit Works---------------------------->

                </div>
                <div class="htmlreference parbase aem-GridColumn aem-GridColumn--default--12">
                    <section class="client-reviews">
                        <div class="boundaries">
                            <h2>What our members are saying</h2>

                            <div class="review-wrapper">
                                <div class="review">
                                    <div class="review-box-boundaries">

                                        <p class="blurb">Wefixyourcreditreport.com is very thorough in their process to correct your credit report. They will dive deep and find the real problems and give you the most optimal and practical advice to correct your credit report and regain the credit score you dream.</p>

                                        <div class="reviewer">
                                            <div class="name">Yvette B | <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star"></span></div>
                                            <p class="date">New York - February 20, 2019</p>
                                        </div>

                                    </div>
                                </div>
                                <div class="review">
                                    <div class="review-box-boundaries">

                                        <p class="blurb">Wefixyourcreditreport.com is the real deal. These guys can do magic for you. You know the importance of credit especially when you have a family and these guys put in a lot of effort to provide you the best solution for your problems. Everyone deserves to have a good credit score and these guys can help you achieve that.

</p>

                                        <div class="reviewer">
                                            <div class="name">Sharon R | <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star grey"></span></div>
                                            <p class="date">New Jersey - March 22, 2019</p>
                                        </div>

                                    </div>
                                </div>
                                <div class="review">
                                    <div class="review-box-boundaries">

                                        <p class="blurb">These guys will tell you the true story. They don’t make fake promises and they don’t claim overnight success but what they do is really outstanding. They helped me get all the negative items removed from my credit report and helped me achieve a great credit score. You can trust these guys to get you a great credit score.</p>

                                        <div class="reviewer">
                                            <div class="name">James P | <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star grey"></span></div>
                                            <p class="date">Washington - April 16, 2019</p>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="mobile-carousels" id="reviews-mobile">
                                <ul class="carousel">
                                    <li>
                                        <a id="carousel-ext" href="#" data-unique="carousel-items">
                                            <ul class="carousel-boundaries">
                                                <li class="selected">
                                                    <div class="carousel-item">
                                                        <div class="review">
                                                            <div class="review-box-boundaries">

                                                                <p class="blurb">Wefixyourcreditreport.com is very thorough in their process to correct your credit report. They will dive deep and find the real problems and give you the most optimal and practical advice to correct your credit report and regain the credit score you dream.</p>

                                                                <div class="reviewer">
                                                                    <div class="name">Yvette B | <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star"></span></div>
                                                                    <p class="date">New York - February 20, 2019</p>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="move-right">
                                                    <div class="carousel-item">
                                                        <div class="review">
                                                            <div class="review-box-boundaries">

                                                                <p class="blurb">Wefixyourcreditreport.com is the real deal. These guys can do magic for you. You know the importance of credit especially when you have a family and these guys put in a lot of effort to provide you the best solution for your problems. Everyone deserves to have a good credit score and these guys can help you achieve that.

</p>

                                                                <div class="reviewer">
                                                                    <div class="name">Sharon R | <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star grey"></span></div>
                                                                    <p class="date">New Jersey - March 22, 2019</p>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="carousel-item">
                                                        <div class="review">
                                                            <div class="review-box-boundaries">

                                                                <p class="blurb">These guys will tell you the true story. They don’t make fake promises and they don’t claim overnight success but what they do is really outstanding. They helped me get all the negative items removed from my credit report and helped me achieve a great credit score. You can trust these guys to get you a great credit score.</p>

                                                                <div class="reviewer">
                                                                    <div class="name">James P | <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star"></span> <span class="star grey"></span></div>
                                                                    <p class="date">Washington - April 16, 2019</p>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </a>
                                    </li>
                                </ul>
                                <div class="prev" id="prev">&#10094</div>
                                <div class="next" id="next">&#10095</div>
                            </div>

                            <div class="review-disclaimer">
                                <a href="#" class="info-modal">Info About Our Testimonials</a>
                            </div>
                        </div>
                    </section>

                    <div id="disclaimer-modal">
                        <div class="modal-container">
                            <div class="modal-heading">
                                <span class="closeBtn">✕</span>
                            </div>
                            <div class="modal-content">
                                <h5>What you need to know about our testimonials</h5>
                                <p>Testimonials represent the results of the particular individual and you should not expect the same result because your case is different than everyone else's. wefixyourcreditreport.com promises only to communicate with creditors on your behalf and in your name, verify report changes with bureaus, and provide you timely information about changes in your reports. Any credit score improvement seen after using our services is the result of many other additional factors, including: keeping credit balances low, paying bills on time, reducing or eliminating unnecessary inquiries, and developing appropriate types of credit, and sound financial planning.</p>
                            </div>
                        </div>
                    </div>

                  


                </div>
                <div class="htmlreference parbase aem-GridColumn aem-GridColumn--default--12">
                    <div id="cost-new-wrapper">
                        <div class="cost-new-container">

                            <h2>How much does it cost?</h2>
                            <div class="cost-new-leader">We offer services for a wide range of budgets.</div>
                            <div class="cost-panel-container">
                                <div class="cost-panel-wrapper">
                                    <div class="cost-panel">
                                        <div class="cost-panel-icon cost-icon-1"></div>
                                        <div class="cost-panel-title">Aggressive</div>
                                        <div class="cost-panel-desc">This package is for those who have a lot of negative items on their credit report.</div>
                                        <div class="cost-panel-price">&dollar;&dollar;&dollar;</div>
                                    </div>
                                </div>
                                <div class="cost-panel-wrapper">
                                    <div class="cost-panel">
                                        <div class="cost-panel-icon cost-icon-2"></div>
                                        <div class="cost-panel-title">Moderate</div>
                                        <div class="cost-panel-desc">This is for those who have relatively fewer negative items.
                                        </div>
                                        <div class="cost-panel-price">&dollar;&dollar;</div>
                                    </div>
                                </div>
                                <div class="cost-panel-wrapper">
                                    <div class="cost-panel">
                                        <div class="cost-panel-icon cost-icon-3"></div>
                                        <div class="cost-panel-title">Basic</div>
                                        <div class="cost-panel-desc">This is for those who have just a few negative items on their credit report.</div>
                                        <div class="cost-panel-price">&dollar;</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mobile-carousels" id="cost-carousel-mobile">
                                <ul class="carousel">
                                    <li>
                                        <a id="carousel-ext" href="#" data-unique="carousel-items">
                                            <ul class="carousel-boundaries">
                                                <li class="selected">
                                                    <div class="carousel-item">
                                                        <div class="cost-panel-wrapper">
                                                            <div class="cost-panel">
                                                                <div class="cost-panel-icon cost-icon-1"></div>
                                                                <div class="cost-panel-title">Aggressive</div>
                                                                <div class="cost-panel-desc">This package is for those who have a lot of negative items on their credit report.</div>
                                                                <div class="cost-panel-price">&dollar;&dollar;&dollar;</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="move-right">
                                                    <div class="carousel-item">
                                                        <div class="cost-panel-wrapper">
                                                            <div class="cost-panel">
                                                                <div class="cost-panel-icon cost-icon-2"></div>
                                                                <div class="cost-panel-title">Moderate</div>
                                                                <div class="cost-panel-desc">This is for those who have relatively fewer negative items.
                                                                </div>
                                                                <div class="cost-panel-price">&dollar;&dollar;</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="carousel-item">
                                                        <div class="cost-panel-wrapper">
                                                            <div class="cost-panel">
                                                                <div class="cost-panel-icon cost-icon-3"></div>
                                                                <div class="cost-panel-title">Basic</div>
                                                                <div class="cost-panel-desc">This is for those who have just a few negative items on their credit report.</div>
                                                                <div class="cost-panel-price">&dollar;</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </a>
                                    </li>
                                </ul>
                                <div class="prev" id="prev">&#10094</div>
                                <div class="next" id="next">&#10095</div>
                            </div>


                            <div class="cost-new-cta">
                                <div class="cost-new-cta-content">Find out how many negative items you have - <strong>FREE</strong></div>
                                <div class="btn-wrapper">
                                    <a class="btn-new" href="tel:+888-586-9913">Call <span class="creditRepairPhoneNumber phoneNumber">888-586-9913</span></a>
                                    <div><a class="text-link" href="signup.php<?= make_query_string(); ?>">or sign up online &raquo;</a></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="htmlreference parbase aem-GridColumn aem-GridColumn--default--12">
                    <!-------------------------- CR Overlay Lead Form ---------------------------->

                    <div class="overlay-modal">
                        <div class="lead-form-modal">
                            <div class="form-spacing">
                                <div class="close-modal"></div>
                                <div class="lead-form-container modal-form">

                                    <h2>We know a thing or two about credit repair.</h2>
                                    <div class="leader">Fill out the following information to find out how much fixing your credit could
                                        save
                                        you.
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>


                    <!-------------------------- End of Overlay CR Lead Form --------------------->

                </div>
                <div class="htmlreference parbase aem-GridColumn aem-GridColumn--default--12">
                    <div class="container" id="get-started-cr">
                        <div class="row get_started_banner">
                            <p class="text">Start with our Proven Online System <span>$99.95/month</span></p>
                            <a href="javascript:void(0);" class="btn btn-orange get_started" data-unique="get-started-section-signup" id="get-started-section-signup">Get Started</a>
                        </div>
                    </div>

                </div>
<?php require_once 'general/__footer__.tpl' ?>
     <script type="text/javascript" src="<?= $path['js'] ?>/nouislider.min.js"></script> 
     <script type="text/javascript" src="<?= $path['js'] ?>/cr-hub.js"></script>
     <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

<script type="text/javascript">
    
     $("#formbtnsubmit").click(function(){
        var errors = new Array();
if($('input[name=firstName]').val()==''){
      var dataerrormessage='Please enter your first name!';
      errors.push(dataerrormessage);     
     } else{
           $.cookie("firstName", $('input[name=firstName]').val());

     }
     if($('input[name=lastName]').val()==''){
      var dataerrormessage='Please enter your last name!';
      errors.push(dataerrormessage);     
     } else{
           $.cookie("lastName", $('input[name=lastName]').val());

     }
     if($('input[name=email]').val()==''){
      var dataerrormessage='Please enter your email id!';
      errors.push(dataerrormessage);     
     } else{
           $.cookie("email", $('input[name=email]').val());

     }
     if($('input[name=phone]').val()==''){
      var dataerrormessage='Please enter a valid contact number!';
      errors.push(dataerrormessage);     
     } else{
           $.cookie("phone", $('input[name=phone]').val());

     }
     if($('input[name=shippingAddress1]').val()==''){
      var dataerrormessage='Please enter your address!';
      errors.push(dataerrormessage);     
     }  else{
           $.cookie("shippingAddress1", $('input[name=shippingAddress1]').val());

     }
     if($('input[name=shippingCity]').val()==''){
      var dataerrormessage='Please enter your city!';
      errors.push(dataerrormessage);     
     }  else{
           $.cookie("shippingCity", $('input[name=shippingCity]').val());

     }
     if($('input[name=shippingCountry]').val()==''){
      var dataerrormessage='Please select your country!';
      errors.push(dataerrormessage);     
     } 
     else{
           $.cookie("shippingCountry", $('input[name=shippingCountry]').val());

     }
     if($('input[name=shippingZip]').val()==''){
      var dataerrormessage='Please enter a valid zip code!';
      errors.push(dataerrormessage);     
     }  else{
           $.cookie("shippingZip", $('input[name=shippingZip]').val());

     }
       if($('#shippingState :selected').text()=='Select State'){
         var dataerrormessage='Please enter your state!';
      errors.push(dataerrormessage);
     } else{
           $.cookie("shippingStatefull", $('#shippingState :selected').text());
            $.cookie("shippingState", $('#shippingState :selected').val());

     }
   
//console.log($.cookie("prod"));

if(errors==''){
     $(".lead-form-container").hide();

     $( ".slider-app-wrapper" ).show();

    
    
}else{
    cb.errorHandler(errors);
}

 });


</script>