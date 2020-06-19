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
<link rel="stylesheet" href="<?= $path['css'] ?>/site2.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/frequently-asked-questions.css" type="text/css" />
<link rel="stylesheet" href="<?= $path['css'] ?>/skin.css" type="text/css" />
<?php perfom_head_tag_close_actions(); ?>

   
</head>

<body>

<div id="header_wrapper">
    <div class="container">
        <a href="/"><img id="credit_repair_logo" src="<?= $path['images'] ?>/logo.png" alt="wefixyourcreditreport.com"></a>
        <ul id="top_nav" style="margin: 15px;">
            <li class="top_nav_member_login_wrapper"><a class="top_nav_member_login" href="#" style="font-size: 18px; height: 33px; width: 90px;display: none;">Log In<span class="top_nav_login_icon"></span></a></li>
            <li class="top_nav_sign_up_wrapper"><a class="top_nav_sign_up" href="signup.php" style="font-size: 18px; height: 35px; width: 125px;">Get Started</a></li>
            <li class="top_nav_phone_number_wrapper" style="display: none;"><a class="top_nav_phone_number" href="tel:+888-586-9913" style="font-size: 18px;"><span class="phone_number"><span class="creditRepairPhoneNumber">888-586-9913</span></span><span class="mobile_text">Call</span><span class="mobile_text_icon"></span></a></li>
        </ul>
        <div id="mobile_nav_toggle" class="">Menu</div>
        <ul id="navigation" class="">
            <li><a href="#">How it Works</a></li>
            <li><a href="#">Reviews</a></li>
            <li><a href="#">Who We Are</a></li>
            <li class="navigation_login_wrap" style="display: none"><a class="navigation_login_btn" href="#">Log In<span class="navigation_login_icon"></span></a></li>
            <li class="navigation_signup_wrap"><a class="navigation_signup_btn" href="signup.php">Get Started</a></li>
        </ul>
    </div>
</div>
