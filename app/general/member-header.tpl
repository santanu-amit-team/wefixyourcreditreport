<link rel="stylesheet" href="<?= $config['offer_path'] ?>assets/member/css/bootstrap.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="<?= $config['offer_path'] ?>assets/member/css/custom.css" />

<div id="msg">
</div>
<?php
use Application\Session;
    if(Session::has('memberSessionData.member_token')) {
?>
<div class="header badge-primary" id="myHeader">
  <div class="clearfix">
    <div class="float-right">
        <a class="btn btn-primary mt-10" role="button" href="<?= $config['offer_path'] ?>member/dashboard.php">Dashboard</a>
        <a class="btn btn-primary" role="button" href="<?= $config['offer_path'] ?>member/orders.php">Orders</a>
        <a class="btn btn-primary" role="button" href="<?= $config['offer_path'] ?>member/order-details.php">Shipping & Billing</a>
        <a class="btn btn-primary" role="button" href="<?= $config['offer_path'] ?>member/order-tracking.php">Order Tracking</a>
        <a class="btn btn-primary mt-10" role="button" href="<?= $config['offer_path'] ?>member/logout.php">Logout</a>
    </div>
</div>
</div>

<?php } ?>
