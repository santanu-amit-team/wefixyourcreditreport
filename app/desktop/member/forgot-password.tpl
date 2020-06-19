<html>
    <head>
        <?php require_once 'general/__header__.tpl' ?>
        <?php require_once 'general/member-header.tpl' ?>
    </head>
    <body>
        <h1>Forgot Password</h1>
        <form method="post" name="forgot-password" accept-charset="utf-8" enctype="application/x-www-form-urlencoded;charset=utf-8">
         <div class="container">
            <label for="email"><b>Email</b></label>

            <input type="text" placeholder="Enter Email" name="email" required>

            <div class="mb-4"></div>
            <button type="submit">Send Recovery Email</button>
            <div class="col-sm-12">
            </div>
          </div>
            
        </form>
        
                
        <?php require_once 'general/member-footer.tpl' ?>
    </body>
</html>
