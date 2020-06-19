<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        <h1>Update Password</h1>
        <form method="post" name="update-password" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
         <div class="container">
            <label for="uname"><b>Email</b></label>

            <input type="text" placeholder="Enter Email" name="email" required>

            <label for="psw"><b>Current Password</b></label>

            <input type="password" placeholder="Enter Current Password" value="" name="current_member_password" required>

            <label for="psw"><b>New Password</b></label>

            <input type="password" placeholder="Enter New Password" value="" name="new_member_password" required>

            <div class="mb-4"></div>
            <button type="submit">Update Password</button>
            <div class="col-sm-12">
            </div>
          </div>
            
        </form>
        
                
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
