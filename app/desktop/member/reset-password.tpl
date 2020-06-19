<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        <h1>Reset Password</h1>
        <form method="post" name="reset-password" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
         <div class="container">

            <label for="member_temp_password"><b>Temporary Password</b></label>

            <input type="password" placeholder="Enter Current Password" value="" name="member_temp_password" required>

            <label for="member_new_password"><b>New Password</b></label>

            <input type="password" placeholder="Enter New Password" value="" name="member_new_password" required>

            <div class="mb-4"></div>
            <button type="submit">Update Password</button>
            <div class="col-sm-12">
            </div>
          </div>
            
        </form>
        
                
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
