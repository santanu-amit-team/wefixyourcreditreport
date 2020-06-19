<!DOCTYPE HTML>
<html>

<head>
 <!-- <?php include 'general/__header__.tpl'; ?> -->

    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="<?= $path['images']; ?>/cr-icon.png" />
    <link rel="stylesheet" href="<?= $path['assets_css'] . '/app.css' ?>" />
    
</head>

<body>

<?php perform_body_tag_open_actions(); ?>
    <div class="content">
        <div class="main">
            <div class="aem-Grid aem-Grid--12 aem-Grid--default--12 ">

                <div class="signup-page aem-GridColumn aem-GridColumn--default--12">
                    <div data-emptytext="Signup - Page">
                        <link rel="stylesheet" href="<?= $path['css']; ?>/prod.min.css" type="text/css">



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

                            <section class="errors">
                                <span class="errors-icon">!</span> There are errors that need attention.
                            </section>
                        </section>
                        <!------------/*  End of Sign Up Page - Header  */------------->





                        <!------------/*  Sign Up Page - 2  */------------->
                        <section id="signup-v2" style="opacity: 1; height: auto;">

                            <div class="steps-container">
                                <ul class="steps">
                                    <li class="complete">Personal Information</li>
                                    <li class="active">Our Services</li>
                                    <li>Legal Agreements</li>
                                </ul>
                            </div>

                           <!--  <form id="Signup-Form" name="Step-2-Form" method="POST" action="" onsubmit="" novalidate="novalidate"> -->
                             <form method="post" action="ajax.php?method=new_order_prospect" name="checkout_form" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
                                <input type="hidden" name="limelight_charset" id="limelight_charset" value="utf-8" />
        <div style="display: none;">  
            <p>
                <label>Billing same as Shipping</label>
                <input type="radio" name="billingSameAsShipping" value="yes" checked="checked" /> YES
                <input type="radio" name="billingSameAsShipping" value="no" /> NO
            </p>

            <div class="billing-info" style="display:none;">
                <p>
                    <label>Billing First Name</label>
                    <input type="text" name="billingFirstName" placeholder="Billing First Name" data-error-message="Please enter your billing first name!" />
                </p>
                <p>
                    <label>Billing Last Name</label>
                    <input type="text" name="billingLastName" placeholder="Billing Last Name" data-error-message="Please enter your billing last name!" />
                </p>
                <p>
                    <label>Billing Address</label>
                    <input type="text" name="billingAddress1" placeholder="Billing Address" data-error-message="Please enter your billing address!" />
                </p>
                <p>
                    <label>Billing City</label>
                    <input type="text" name="billingCity" placeholder="Billing City" data-error-message="Please enter your billing city!" />
                </p>
                <p>
                    <label>Billing Country: </label>
                    <select name="billingCountry" data-error-message="Please select your billing country!">

                        <option value="">Select Country</option>
                    </select>
                </p>
                <p>
                    <label>Billing State: </label>

                    <input type="text" name="billingState" placeholder="Billing State" data-error-message="Please enter your billing state!" />
                </p>
                <p>
                    <label>Billing Zip Code: </label>
                    <input type="text" name="billingZip" placeholder="Billing Zip Code" data-error-message="Please enter a valid billing zip code!" />

                </p>
            </div>
        </div>
                                <div id="services-wrapper">

                                    <h2 class="level-service-person-name" id="primary-user-name">Select <span class="name-of-client"></span> service</h2>

                                    <div class="service-levels primary-service">

                                        <div class="service-header-selection selected-service-row">
                                            <div class="service-option empty"></div>
                                            <div class="service-option advanced selected-service top-are"><span class="green">Recommended</span></div>
                                            <div class="service-option standard top-are1"></div>
                                            <div class="service-option direct top-are2"></div>
                                        </div>

                                        <div class="service-header-selection">
                                            <div class="service-option empty"></div>
                                            <div class="service-option advanced selected-service">Advanced</div>
                                            <div class="service-option standard">Standard</div>
                                            <div class="service-option direct">Direct</div>
                                        </div>

                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Negative items challenged per month</span>
                                                    <span class="mobile">Challenge</span>
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com works to ensure that the credit bureaus present your credit reports in a fair and accurate manner.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service">Up to 19</div>
                                            <div class="service-feature standard">Up to 15</div>
                                            <div class="service-feature direct">Up to 15</div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Creditor disputes per month</span>
                                                    <span class="mobile">Disputes</span>
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com communicates with your creditors to help protect your credit reports and credit scores.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service">6</div>
                                            <div class="service-feature standard">3</div>
                                            <div class="service-feature direct">3</div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Credit report change alerts</span>
                                                    <span class="mobile">Report alerts</span>
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        You get the necessary legal tools to confront unfair credit score damage that can occur when creditors check your credit reports.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"><span class="check-mark"></span></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Anytime access to </span>Customer support
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com evaluates your credit reports using five credit score factors and gives you a detailed, personalized analysis every month.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"><span class="check-mark"></span></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">No long-term commitment – </span>Cancel any time
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        You'll receive coaching on how to understand and address damaging credit report changes as they occur.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"><span class="check-mark"></span></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    FICO<sup>&reg;</sup> credit score
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        Our firm provides and tracks your FICO&reg; Score, used by 90% of top US lenders, based on TransUnion data each month
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service">Monthly</div>
                                            <div class="service-feature standard">Quarterly</div>
                                            <div class="service-feature direct">Quarterly</div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Credit</span> Score analysis
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com employs legal strategies that ask abusive debt collectors to cease &amp; desist.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service">Monthly</div>
                                            <div class="service-feature standard">Quarterly</div>
                                            <div class="service-feature direct"></div>
                                        </div>

                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    24/7 <span class="desktop">credit monitoring and </span>alerts
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        We asked the bureaus to check for credit report changes
                                                        that may occur as a result of our communications with your creditors.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Ability to challenge </span>Hard inquiries
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        When appropriate, we dispute your documented errors with the credit bureaus.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"></div>
                                        </div>

                                        <div class="service-divider">$100 value for $20 more than Standard</div>

                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    Identity <span class="desktop">theft </span>Protection
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        Our firm partners with the leading provider of financial fraud alerts to guard your identity 24/7.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"></div>
                                            <div class="service-feature direct"></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">$1 million in Identity Theft </span>Insurance
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com partners with the leading provider of financial fraud alerts to guard your identity 24/7.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"></div>
                                            <div class="service-feature direct"></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Personal </span>Finance Tools
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        Track your financial and credit accounts with our easy-to-use personal finance manager.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"></div>
                                            <div class="service-feature direct"></div>
                                        </div>

                                        <div class="service-row-info billing-info">
                                            <div class="service-name">
                                                <div class="service-name-label">Monthly Fee</div>
                                            </div>
                                            <div class="service-feature advanced selected-service advanced-pricing">$199.95</div>
                                            <div class="service-feature standard standard-pricing">$99.95</div>
                                            <div class="service-feature direct direct-pricing">$69.95</div>
                                        </div>

                                        <div class="service-selection most-popular">
                                            <div class="service-option empty"></div>

                                            <div class="service-option selected-service advanced first-are">
                                                <div class="service-cta">
                                                    <input type="radio" id="advanced-option" name="service-options" value="advanced-option" data-originalPrice="119.95" data-serviceName="Advanced" checked class="package" data-campid="1"/>
                                                    <label for="advanced-option" class="packageselect" id="label-advanced-option">Selected</label>
                                                </div>
                                            </div>

                                            <div class="service-option standard second-area">
                                                <div class="service-cta">
                                                    <input type="radio" id="standard-option" name="service-options" value="standard-option" data-originalPrice="99.95" data-serviceName="Standard" class="package" data-campid="2"/>
                                                    <label for="standard-option" class="packageselect" 
                                                    id="label-standard-option">Select</label>
                                                </div>
                                            </div>

                                            <div class="service-option direct thir-area">
                                                <div class="service-cta">
                                                    <input type="radio" id="direct-option" name="service-options" value="direct-option" data-originalPrice="69.95" data-serviceName="Direct" class="package" data-campid="3"/>
                                                    <label for="direct-option" class="packageselect" id="label-direct-option">Select</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <h2 class="level-service-person-name ffhd" id="ffhd-user-name">Select <span class="name-of-client"></span>
                                        service</h2>

                                    <div class="service-levels ffhd-service ffhd">

                                        <div class="service-header-selection selected-service-row">
                                            <div class="service-option empty"></div>
                                            <div class="service-option advanced selected-service top-are"><span class="green">Recommended</span></div>
                                            <div class="service-option standard top-are1"></div>
                                            <div class="service-option direct top-are2"></div>
                                        </div>

                                        <div class="service-header-selection">
                                            <div class="service-option empty"></div>
                                            <div class="service-option advanced selected-service">Advanced</div>
                                            <div class="service-option standard">Standard</div>
                                            <div class="service-option direct">Direct</div>
                                        </div>

                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Negative items challenged per month</span>
                                                    <span class="mobile">Challenge</span>
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com works to ensure that the credit bureaus present your credit reports in a fair and accurate manner.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service">Up to 19</div>
                                            <div class="service-feature standard">Up to 15</div>
                                            <div class="service-feature direct">Up to 15</div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Creditor disputes per month</span>
                                                    <span class="mobile">Disputes</span>
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com communicates with your creditors to help protect your credit reports and credit scores.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service">6</div>
                                            <div class="service-feature standard">3</div>
                                            <div class="service-feature direct">3</div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Credit report change alerts</span>
                                                    <span class="mobile">Report alerts</span>
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        You get the necessary legal tools to confront unfair credit score damage that can occur when creditors check your credit reports.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"><span class="check-mark"></span></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Anytime access to </span>Customer support
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com evaluates your credit reports using five credit score factors and gives you a detailed, personalized analysis every month.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"><span class="check-mark"></span></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">No long-term commitment – </span>Cancel any time
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        You'll receive coaching on how to understand and address damaging credit report changes as they occur.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"><span class="check-mark"></span></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    FICO<sup>&reg;</sup> credit score
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        Our firm provides and tracks your FICO&reg; Score, used by 90% of top US lenders, based on TransUnion data each month
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service">Monthly</div>
                                            <div class="service-feature standard">Quarterly</div>
                                            <div class="service-feature direct">Quarterly</div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Credit</span> Score analysis
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com employs legal strategies that ask abusive debt collectors to cease &amp; desist.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service">Monthly</div>
                                            <div class="service-feature standard">Quarterly</div>
                                            <div class="service-feature direct"></div>
                                        </div>

                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    24/7 <span class="desktop">credit monitoring and </span>alerts
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        We asked the bureaus to check for credit report changes
                                                        that may occur as a result of our communications with your creditors.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Ability to challenge </span>Hard inquiries
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        When appropriate, we dispute your documented errors with the credit bureaus.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"><span class="check-mark"></span></div>
                                            <div class="service-feature direct"></div>
                                        </div>

                                        <div class="service-divider">$100 value for $20 more than Standard</div>

                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    Identity <span class="desktop">theft </span>Protection
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        Our firm partners with the leading provider of financial fraud alerts to guard your identity 24/7.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"></div>
                                            <div class="service-feature direct"></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">$1 million in Identity Theft </span>Insurance
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        wefixyourcreditreport.com partners with the leading provider of financial fraud alerts to guard your identity 24/7.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"></div>
                                            <div class="service-feature direct"></div>
                                        </div>
                                        <div class="service-row-info">
                                            <div class="service-name">
                                                <div class="service-name-label">
                                                    <span class="desktop">Personal </span>Finance Tools
                                                </div>
                                                <span class="icon-hint service-feature-tooltip desktop">
                                                    <div class="hint-text">
                                                        Track your financial and credit accounts with our easy-to-use personal finance manager.
                                                    </div>
                                                </span>
                                            </div>
                                            <div class="service-feature advanced selected-service"><span class="check-mark"></span></div>
                                            <div class="service-feature standard"></div>
                                            <div class="service-feature direct"></div>
                                        </div>

                                        <div class="service-row-info billing-info">
                                            <div class="service-name">
                                                <div class="service-name-label">Monthly Fee</div>
                                            </div>
                                            <div class="service-feature advanced selected-service advanced-pricing">$--.--</div>
                                            <div class="service-feature standard standard-pricing">$--.--</div>
                                            <div class="service-feature direct direct-pricing">$--.--</div>
                                        </div>

                                        <div class="service-selection most-popular">
                                            <div class="service-option empty"></div>

                                            <div class="service-option selected-service advanced">
                                                <div class="service-cta isselect">
                                                    <input type="radio" id="advanced-option-ffhd" name="service-options" value="advanced-option" data-originalPrice="119.95" data-serviceName="Advanced" checked  class="package" data-campid="1"/>
                                                    <label for="advanced-option-ffhd">Selected</label>
                                                </div>
                                            </div>

                                            <div class="service-option standard">
                                                <div class="service-cta">
                                                    <input type="radio" id="standard-option-ffhd" name="service-options" value="standard-option" data-originalPrice="99.95" data-serviceName="Standard"  class="package" data-campid="1"/>
                                                    <label for="standard-option-ffhd">Select</label>
                                                </div>
                                            </div>

                                            <div class="service-option direct">
                                                <div class="service-cta">
                                                    <input type="radio" id="direct-option-ffhd" name="service-options" value="direct-option" data-originalPrice="69.95" data-serviceName="Direct" class="package" data-campid="1"/>
                                                    <label for="direct-option-ffhd">Select</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="service-plan-same">

                                        <p>Select the same repair service for both <span></span></p>

                                        <div class="service-cta selected">
                                            <input type="radio" id="yes-same-service" name="service-ffhd" value="yes" checked="checked">
                                            <label for="yes-same-service"></label>
                                        </div>
                                        <label for="yes-same-service" class="ffhd-check-text" style="margin-right: 50px">Yes</label>

                                        <div class="service-cta">
                                            <input type="radio" id="no-same-service" name="service-ffhd" value="no">
                                            <label for="no-same-service"></label>
                                        </div>
                                        <label for="no-same-service" class="ffhd-check-text">No</label>

                                    </div>

                                </div>

                                <div class="payment-info" id="primary-payment-info">

                                    <div class="billing-info-same">

                                        <div class="styled_checkbox">
                                            <input type="checkbox" value="" class="signup-checkbox" id="same-billing-check" name="same-billing-check" checked>
                                            <label for="same-billing-check"></label>
                                        </div>

                                        <p>
                                            <label for="same-billing-check" class="billing-text-info">
                                                Use the same billing and payment information for the both of you?
                                            </label>
                                        </p>

                                    </div>

                                    <h2>Billing Summary</h2>

                                    <div class="payment-box">

                                        <div class="billing-wrapper">

                                            <div class="billing-info">

                                                <div class="monthly-fee-box">
                                                    <div class="billing-name-summary primary-user"></div>
                                                    <div class="primary-service billing-label" id="primary-bill">
                                                        First Work Fee
                                                        <div class="billing-date">Starts <span></span></div>
                                                    </div>
                                                    <div class="primary-service billing-fee">$<span
                                                       id="packageprice" >119.95</span> 
                                                        <div class="tax-info">Tax +</div>
                                                    </div>
                                                </div>

                                                <div class="monthly-fee-box ffhd-yes">
                                                    <div class="billing-name-summary ffhd-user"></div>
                                                    <div class="ffhd-service billing-label" id="ffhd-bill">
                                                        First Work Fee
                                                        <div class="billing-date">Starts <span></span></div>
                                                    </div>
                                                    <div class="ffhd-service billing-fee">$129.95
                                                        <div class="tax-info">Tax +</div>
                                                    </div>
                                                </div>
<!-- 
                                                <div class="billing-address">
                                                    <div class="styled_checkbox" style="margin-bottom: 5px;">
                                                        <input type="checkbox" value="" class="signup-checkbox" id="quickstart-check" name="quickstart-check" data-quickstartPrice="$14.99" checked>
                                                        <label for="quickstart-check"></label>
                                                    </div>
                                                    <div class="billing-text" style="margin-left: 10px; font-size: 18px">
                                                        <label for="quickstart-check" class="billing-text-info">Quickstart My Case</label>
                                                        <div class="hint icon-hint">
                                                            ?
                                                            <div class="hint-text">
                                                                This feature automatically loads your TransUnion credit report into our system.
                                                                This
                                                                payment is made to Credit.com
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> -->

                                               <!--  <span class="billing-starting-fee">$14.99<div class="tax-info">Tax +</div></span> -->
                                            </div>

                                            <hr>

                                          <!--   <div class="billing-total-label">
                                                <p>Total due today</p>
                                                <span class="billing-total-fee primary-user">$14.99<div class="tax-info">Tax +</div></span>
                                            </div> -->
                                        </div>
                                    </div>

                                    <div class="payment-box">

                                        <div class="payment-wrapper">

                                            <p>Payment Method</p>
                                            <input type="hidden" name="payment_method" id="signup_primary_payment_method" value="CC">
                                            <input type="hidden" id="payment_method" name="payment_method" value="CC">
<select name="creditCardType" class="required" data-deselect="false" data-error-message="Please select valid card type!" style="display: none">
                    <option value="">Card Type</option>
                    <?php foreach($config['allowed_card_types'] as $key=>$value): ?>
                    <option value="<?= $key ?>"><?= ucfirst($value) ?></option>
                    <?php endforeach ?>
                </select>
                                            <div class="card-wrapper">
                                                <div class="block card_number floating_label ">
                                                     


                                                    <input type="tel" id="cc_num_0" required class="required input_cc_num" name="creditCardNumber" value="" autocomplete="off" maxlength="16" data-form-format-helper="formatCCard" aria-required="true" data-error-message="Please enter a valid credit card number!">
                                                    <label for="cc_num_0">Card Number</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                </div>

                                                <div class="card_logos"></div>

                                                <div class="block expiration floating_label ">
                                                    <select name="expmonth" class="input_cc_exp expmonth required" data-error-message="Please select a valid expiry month!">
                                                        

                                                        <?php get_months(); ?>
                                                    </select>
                                                    <label for="cc_exp">Exp month</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                </div>

                                                    <div class="block expiration floating_label ">
                                                     <select name="expyear" class="input_cc_exp expyear required" data-error-message="Please select a valid expiry year!">


                                                        <?php get_years(); ?>
                                                    </select>
                                                    <label for="cc_exp">Exp year</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                </div>

                                                <div class="block cvv floating_label ">
                                                    <input type="password" id="cc_cvv" required class="required input_cc_cvv" name="CVV" autocomplete="off" maxlength="3" data-form-format-helper="forceNumeric" aria-required="true" data-error-message="Please enter a valid CVV code!"  data-validate="cvv">


                                                    <label for="cc_cvv">CVV</label>
                                                    <em class="field-text"></em>
                                                    <a href="javascript:void(0);" onclick="javascript:openNewWindow('cvv.html','modal');" class="fancybox icon-hint">?</a>
                                                    <span class="field-icon"></span>
                                                    <!-- <div class="hint icon-hint">
                                                        ?
                                                        <div class="hint-text">
                                                            The Card Verification Value (CVV) is a unique three or four-digit security number
                                                            printed on
                                                            your debit/credit card.
                                                            <div style='margin-top:15px;'>Locating your CVV number</div>
                                                            <img class="lazy-loader" src="<?= $path['images'] ?>/cvv_visa.png" style='width:80%; height: 201px; margin:10px auto;' />
                                                            <div>The CVV is the last three-digit number printed in the signature strip on the
                                                                reverse
                                                                side of the card.
                                                            </div>
                                                        </div>
                                                    </div> -->
                                                </div>


                                            </div>

 <p id="loading-indicator" style="display:none;">Processing...</p>
                                            <div class="billing-address">

                                                <div class="billing-text">
                                                    <label class="billing-text-info" for="primary-user">Billing Address</label>
                                                    <div class="users_information">
                                                        <span id="name-info"><?= $customer['firstName'] ?>  <?= $customer['lastName'] ?></span>
                                                        <br>
                                                        <span id="street-info"><?= $customer['shippingAddress1'] ?></span>
                                                        <br>
                                                        <span id="city-info"><?= $customer['shippingCity'] ?></span>,
                                                        <span id="state-info"><?= $customer['shippingState'] ?></span>
                                                        <span id="zip-info"><?= $customer['shippingZip'] ?></span>
                                                    </div>
                                                </div>

                                           <!--      <div class="billing-text" style="float: right;">
                                                    <a class="billing-info-edit" id="primary-user">Edit</a>
                                                </div> -->

                                            </div>

                                            <div class="billing_info_wrapper">
                                                <div class="block cc_name floating_label ">
                                                    <input type="text" required id="cc-name" class="input_name_on_card" name="cc-name" maxlength="25" value="" data-rule-validchar="true" aria-required="true">
                                                    <label for="cc-name">Name on Card</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                </div>

                                                <div class="block cc_street floating_label ">
                                                    <input type="text" required id="cc-street" class="input_cc_street" name="cc-street" maxlength="60" value="" data-rule-validchar="true" aria-required="true">
                                                    <label for="cc-street">Address</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                </div>

                                                <div class="block cc_zip floating_label ">
                                                    <input type="tel" required id="cc-zip" class="input_zip" name="cc-zip" data-form-format-helper="forceNumeric" maxlength="5" value="98375" aria-required="true">
                                                    <label for="cc-zip">Zip Code</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                    <a style="display: none;color: #2d9ed7;text-decoration: underline;" class="california-policy-btn" href="/info/privacy-policy#california-policy" target="_blank">California
                                                        Privacy Rights</a>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>

                             <!--    <div class="payment-info ffhd" id="ffhd-payment-info">
                                    <div class="payment-box">

                                        <div class="billing-wrapper">

                                            <div class="billing-info">

                                                <div class="monthly-fee-box">
                                                    <div class="billing-name-summary ffhd-user">Jane Doe's Summary</div>
                                                    <div class="ffhd-service billing-label">
                                                        First Work Fee
                                                        <div class="billing-date">Starts <span></span></div>
                                                    </div>
                                                    <div class="ffhd-service billing-fee">$129.95
                                                        <div class="tax-info">Tax +</div>
                                                    </div>
                                                </div>

                                                <div class="billing-address">
                                                    <div class="styled_checkbox" style="margin-bottom: 5px;">
                                                        <input type="checkbox" value="" class="signup-checkbox" id="quickstart-check-ffhd" name="quickstart-check" data-quickstartPrice="$14.99" checked>
                                                        <label for="quickstart-check-ffhd"></label>
                                                    </div>
                                                    <div class="billing-text" style="margin-left: 10px; font-size: 18px">
                                                        <label for="quickstart-check-ffhd" class="billing-text-info">Quickstart My Case</label>
                                                        <div class="hint icon-hint">
                                                            ?
                                                            <div class="hint-text">
                                                                This feature automatically loads your TransUnion credit report into our system.
                                                                This
                                                                payment is made to Credit.com
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <span class="billing-starting-fee">$14.99<div class="tax-info">Tax +</div></span>
                                            </div>

                                            <hr>

                                            <div class="billing-total-label">
                                                <p>Total due today</p>
                                                <span class="billing-total-fee ffhd-user">$14.99<div class="tax-info">Tax +</div></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="payment-box">

                                        <div class="payment-wrapper">

                                            <p>Payment Method</p>
                                            <input type="hidden" name="payment_method" id="signup_primary_payment_method-ffhd" value="CC">
                                            <input type="hidden" id="payment_method-ffhd" name="payment_method" value="CC">

                                            <div class="card-wrapper">
                                                <div class="block card_number floating_label ">
                                                    <input type="tel" id="cc_num_0-ffhd" required class="input_cc_num" name="creditCardNumber" value="" autocomplete="off" maxlength="20" data-form-format-helper="formatCCard" aria-required="true">
                                                    <label for="cc_num_0-ffhd">Card Number</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                </div>

                                                <div class="card_logos"></div>

                                                <div class="block expiration floating_label ">
                                                    <input type="tel" id="cc_exp-ffhd" required class="input_cc_exp" name="card-expiration" value="" data-form-format-helper="formatCardExpirationDate" autocomplete="off" aria-required="true">
                                                    <label for="cc_exp-ffhd">Exp</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                </div>

                                                <div class="block cvv floating_label ">
                                                    <input type="password" id="cc_cvv-ffhd" required class="input_cc_cvv" name="card-cvv" autocomplete="off" maxlength="4" data-form-format-helper="forceNumeric" aria-required="true">
                                                    <label for="cc_cvv-ffhd">CVV</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                    <div class="hint icon-hint">
                                                        ?
                                                        <div class="hint-text">
                                                            The Card Verification Value (CVV) is a unique three or four-digit security number
                                                            printed on
                                                            your debit/credit card.
                                                            <div style='margin-top:15px;'>Locating your CVV number</div>
                                                            <img class="lazy-loader" data-src='/content/dam/credit-repair/common/assets/imgs/cvv_visa.png' style='width:80%; height: 201px; margin:10px auto;' />
                                                            <div>The CVV is the last three-digit number printed in the signature strip on the
                                                                reverse
                                                                side of the card.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>

                                            <div class="billing-address">

                                                <div class="billing-text">
                                                    <label class="billing-text-info" for="ffhd-user">Billing Address</label>
                                                    <div class="users_information">
                                                        <span id="name-info-ffhd"></span>
                                                        <br>
                                                        <span id="street-info-ffhd"></span>
                                                        <br>
                                                        <span id="city-info-ffhd"></span>,
                                                        <span id="state-info-ffhd"></span>
                                                        <span id="zip-info-ffhd"></span>
                                                    </div>
                                                </div>

                                                <div class="billing-text" style="float: right;">
                                                    <a class="billing-info-edit" id="ffhd-user">Edit</a>
                                                </div>
                                            </div>

                                            <div class="billing_info_wrapper">
                                                <div class="block cc_name floating_label ">
                                                    <input type="text" required id="cc-name-ffhd" class="input_name_on_card" name="cc-name" maxlength="25" value="" data-rule-validchar="true" aria-required="true">
                                                    <label for="cc-name-ffhd">Name on Card</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                </div>

                                                <div class="block cc_street floating_label ">
                                                    <input type="text" required id="cc-street-ffhd" class="input_cc_street" name="cc-street" maxlength="60" value="" data-rule-validchar="true" aria-required="true">
                                                    <label for="cc-street-ffhd">Address</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                </div>

                                                <div class="block cc_zip floating_label ">
                                                    <input type="tel" required id="cc-zip-ffhd" class="input_zip" name="cc-zip" data-form-format-helper="forceNumeric" maxlength="5" value="98375" aria-required="true">
                                                    <label for="cc-zip-ffhd">Zip Code</label>
                                                    <em class="field-text"></em>
                                                    <span class="field-icon"></span>
                                                    <a style="display: none;color: #2d9ed7;text-decoration: underline;" class="california-policy-btn" href="/info/privacy-policy#california-policy" target="_blank">California
                                                        Privacy Rights</a>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div> -->

                                <div style="clear: both"></div>

                                <div id="cancellation-notice" class="step-2">
                                    <div class="cancellation-box">
                                        <img class="lazy-loader mobile-icon" src="<?= $path['images']; ?>/information-image.gif" alt="information-icon">
                                        <h2 class="question-asked">Did You Know?</h2>
                                        <h2>You Can Cancel Anytime.</h2>
                                        <hr>
                                        <img class="lazy-loader" src="<?= $path['images']; ?>/information-image.gif" alt="information-icon" style="margin: 25px auto">
                                        <p>Providing the best service possible is a top priority at Credit Repair,
                                            and if we aren't meeting your expectations, you can cancel at any time.
                                            Just remember, credit repair isn't going to happen overnight.</p>
                                    </div>

                                    <div class="submit_button_wrap">
                                        <button type="submit" name="services-submit" class="submit-info submit_button" autocomplete="off">
                                            Continue
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
                                </div>
<input type="hidden" name="productprice" value="119.95" id="prodprice">
<input type="hidden" name="campaigns[1][id]" value="1" id="prodcamp">

                            </form>

                            <div class="cert_wrapper">
                                <span id="siteseal">
                                    <script async type="text/javascript" src="https://seal.godaddy.com/getSeal?sealID=dn0Hwvn3db1McpLGYLu6XHC20lORlGgebIAgbi3ZFOAuLvNaSehKIdCZq6rP"></script>
                                </span>
                            </div>

                        </section>
                        <!------------/* End of Sign Up Page - 2  */------------->





                        <!------------/*  Sign Up Page - Loading  */------------->
                        <div class="loading-box full-sized">
                            <div class="loading"></div>
                        </div>
                        <!------------/*  END of Sign Up Page - Loading  */------------->
                        <!------------/*  Modal Pop Up  */------------->
                        <div class="overlay-modal" style="display: none">
                            <div class="modal-box" >
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


   <?php include 'general/__sepa__.tpl'; ?>
            <?php include 'general/__directdebit__.tpl'; ?>
 <?php
        include 'general/__scripts__.tpl';
        include 'general/__analytics__.tpl';
        perform_body_tag_close_actions();
        ?>

                      <!--   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->
                        <!-- <script src="<?= $path['js']; ?>/jquery-validate-v1.13.1.min.js"></script> -->
                      <!--   <script type="text/javascript" src="<?= $path['js']; ?>/forms.js"></script> -->
                    <!--     <script type="text/javascript" src="<?= $path['js']; ?>/prod.min.js"></script>
                        <script type="text/javascript" src="<?= $path['js']; ?>/prod.min2.js"></script> -->

<script type="text/javascript">
    $(document).ready(function() {
     $(".package").on("click", function(){
              var camp=$(this).attr('data-campid');
              $('#prodcamp').val(camp);
              var price=$(this).attr('data-originalprice');
              $('#prodprice').val(price);
              $('#packageprice').text(price);
              $('.packageselect').text('Select');
              var pack =$(this).val();
             // alert(pack);
$("#label-"+pack).text('Selected');


 var sername=$(this).attr('data-servicename');
sername = sername.toLowerCase();

$(".service-option, .service-feature").removeClass("isselect");
$("."+sername).addClass("isselect");
});
     $(".package:first").trigger("click");
     });

   
</script> 








                    </div>
                </div>


            </div>

        </div>
    </div>







    <p class='site_bottom_copyright_ref_and_tid' style='display:none;' id='footer_tid'>(0)</p>
</body>

</html>
