<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        <h1>Login</h1>
        <form method="post" name="login" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
         <div class="container">
            <label for="uname"><b>Email</b></label>

            <input type="text" class="required" placeholder="Enter Email" value="" name="email" data-error-message="Please enter your email address!">


            <label for="psw"><b>Password</b></label>

            <input type="password" class="required" placeholder="Enter Password" value="" name="member_password" data-error-message="Please enter your password!">

            
            <div class="mb-4"></div>
            <button type="submit">Login</button>
            <div class="row">
                <div class="col-sm-6">
                    <a href="<?= $config['offer_path'] ?>member/update-password.php">Update Password</a>
                </div>
                <div class="col-sm-6">
                    <a class="right" href="<?= $config['offer_path'] ?>member/forgot-password.php">Forgot Password ?</a>
                </div>
            </div>
          </div>
            
        </form>
        
                
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
