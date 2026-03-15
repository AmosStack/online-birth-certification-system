<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/dbconnection.php';

obcs_session_start();

$errorMessage = '';

if (isset($_COOKIE['userpassword'])) {
  setcookie('userpassword', '', time() - 3600, '/', '', false, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $username = obcs_input_string($_POST, 'username');
  $password = (string) ($_POST['password'] ?? '');

  $query = $dbh->prepare('SELECT ID, Password FROM tbladmin WHERE UserName = :username LIMIT 1');
  $query->bindParam(':username', $username, PDO::PARAM_STR);
  $query->execute();
  $admin = $query->fetch();

  if ($admin && obcs_verify_password($password, $admin->Password)) {
    if (obcs_password_needs_upgrade($admin->Password)) {
      $newPasswordHash = obcs_hash_password($password);
      $upgradeQuery = $dbh->prepare('UPDATE tbladmin SET Password = :password WHERE ID = :id');
      $upgradeQuery->bindParam(':password', $newPasswordHash, PDO::PARAM_STR);
      $upgradeQuery->bindParam(':id', $admin->ID, PDO::PARAM_INT);
      $upgradeQuery->execute();
    }

    if (!empty($_POST['remember'])) {
      setcookie('user_login', $username, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    } else {
      setcookie('user_login', '', time() - 3600, '/', '', false, true);
    }

    obcs_login_admin((int) $admin->ID, $username);
    obcs_redirect('dashboard.php');
  }

  $errorMessage = 'Invalid details.';
}
?>
<!doctype html>
<html class="no-js" lang="en">

<head>
    
    <title>Login | Online Birth Certificate System</title>
   
    <!-- Google Fonts
		============================================ -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,700,700i,800" rel="stylesheet">
    <!-- Bootstrap CSS
		============================================ -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Bootstrap CSS
		============================================ -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <!-- adminpro icon CSS
		============================================ -->
    <link rel="stylesheet" href="css/adminpro-custon-icon.css">
    <!-- meanmenu icon CSS
		============================================ -->
    <link rel="stylesheet" href="css/meanmenu.min.css">
    <!-- mCustomScrollbar CSS
		============================================ -->
    <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
    <!-- animate CSS
		============================================ -->
    <link rel="stylesheet" href="css/animate.css">
    <!-- normalize CSS
		============================================ -->
    <link rel="stylesheet" href="css/normalize.css">
    <!-- form CSS
		============================================ -->
    <link rel="stylesheet" href="css/form.css">
    <!-- style CSS
		============================================ -->
    <link rel="stylesheet" href="style.css">
    <!-- responsive CSS
		============================================ -->
    <link rel="stylesheet" href="css/responsive.css">
    <!-- modernizr JS
		============================================ -->
    <script src="js/vendor/modernizr-2.8.3.min.js"></script>
</head>

<body class="materialdesign">
    <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
    <!-- Header top area start-->
    <div class="wrapper-pro">

      
            <!-- login Start-->
            <div class="login-form-area mg-t-30 mg-b-40">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-4"></div>
                        <form class="adminpro-form" method="post" name="login">
                            <div class="col-lg-4">
                                <div class="login-bg">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="logo">
                                                <h3 style="font-weight: bold;color: blue">Online Birth Certicate System</h3>
                                                <hr />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="login-title">
                                                <h1 style="color: red">Admin Login Form</h1>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($errorMessage !== ''): ?>
                                    <div class="row">
                                      <div class="col-lg-12">
                                        <p style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;padding:8px 12px;border-radius:4px;font-size:13px;margin-bottom:8px;">
                                          <?php echo obcs_escape($errorMessage); ?>
                                        </p>
                                      </div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="login-input-head">
                                                <p>User Name</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="login-input-area">
                                                <input type="text" placeholder="User Name" required="true" name="username" value="<?php if(isset($_COOKIE["user_login"])) { echo obcs_escape($_COOKIE["user_login"]); } ?>" >
                                                <i class="fa fa-user login-user" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="login-input-head">
                                                <p>Password</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="login-input-area">
                                                <input type="password" placeholder="Password" name="password" required="true">
                                                <i class="fa fa-lock login-user"></i>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="forgot-password">
                                                        <a href="forgot-password.php">Forgot password?</a>

                                                    </div>
                                                </div>
                                            </div>
                                             <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="login-keep-me">
                                                        <label class="checkbox">
                                                            <input type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE["user_login"])) { ?> checked <?php } ?>><i></i>Keep me logged in
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-4">

                                        </div>
                                        <div class="col-lg-8">
                                            <div class="login-button-pro">
                                               
                                                <button type="submit" class="login-button login-button-lg" name="login">Log in</button>

                                            </div>
                                           <p><a href="../index.php">Back Home!!!</a></p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="col-lg-4"></div>
                    </div>
                </div>
            </div>
            <!-- login End-->
        </div>
    </div>
    <?php include_once('includes/footer.php');?>
    <!-- jquery
		============================================ -->
    <script src="js/vendor/jquery-1.11.3.min.js"></script>
    <!-- bootstrap JS
		============================================ -->
    <script src="js/bootstrap.min.js"></script>
    <!-- meanmenu JS
		============================================ -->
    <script src="js/jquery.meanmenu.js"></script>
    <!-- mCustomScrollbar JS
		============================================ -->
    <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
    <!-- sticky JS
		============================================ -->
    <script src="js/jquery.sticky.js"></script>
    <!-- scrollUp JS
		============================================ -->
    <script src="js/jquery.scrollUp.min.js"></script>
    <!-- form validate JS
		============================================ -->
    <script src="js/jquery.form.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/form-active.js"></script>
    <!-- main JS
		============================================ -->
    <script src="js/main.js"></script>
</body>

</html>