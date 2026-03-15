<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/dbconnection.php';

obcs_session_start();

$errorMessage = '';
$oauthError = isset($_GET['oauth_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $mobileNumber = obcs_input_string($_POST, 'mobno');
    $password = (string) ($_POST['password'] ?? '');

    $query = $dbh->prepare('SELECT ID, Password FROM tbluser WHERE MobileNumber = :mobno LIMIT 1');
    $query->bindParam(':mobno', $mobileNumber, PDO::PARAM_STR);
    $query->execute();
    $user = $query->fetch();

    if ($user && obcs_verify_password($password, $user->Password)) {
        if (obcs_password_needs_upgrade($user->Password)) {
            $newPasswordHash = obcs_hash_password($password);
            $upgradeQuery = $dbh->prepare('UPDATE tbluser SET Password = :password WHERE ID = :id');
            $upgradeQuery->bindParam(':password', $newPasswordHash, PDO::PARAM_STR);
            $upgradeQuery->bindParam(':id', $user->ID, PDO::PARAM_INT);
            $upgradeQuery->execute();
        }

        obcs_login_user((int) $user->ID, $mobileNumber);
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
                                                <h3 style="font-weight: bold;color: blue">Online Birth Certificate System</h3>
                                                <hr />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="login-title">
                                                <h1 style="color: red">User Login Form</h1>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($oauthError): ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <p style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;padding:8px 12px;border-radius:4px;font-size:13px;margin-bottom:8px;">
                                          Social sign-in failed. Please try again or log in with your password.
                                            </p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
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
                                                <p>Mobile Number</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="login-input-area">
                                                <input type="text" name="mobno" maxlength="10" pattern="[0-9]+" required="true" />
                                                <i class="fa fa-mobile login-user" aria-hidden="true"></i>
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
                                                <input type="password" name="password" required="true" />
                                                <i class="fa fa-lock login-user"></i>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="forgot-password">
                                                        <a href="forgot-password.php">Forgot password?</a>

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
                                            <p><a href="register.php">Don't have an account ? Sign Up</a></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12" style="padding:4px 15px;">
                                            <hr style="margin:6px 0;" />
                                            <p style="text-align:center;color:#888;font-size:12px;margin:0;">OR</p>
                                            <hr style="margin:6px 0;" />
                                        </div>
                                    </div>
                                    <div class="row" style="padding:0 15px 10px;">
                                      <div class="col-lg-6 col-md-6 col-sm-12" style="padding:0 5px 10px;">
                                        <a href="facebook-callback.php"
                                           style="display:flex;align-items:center;justify-content:center;gap:10px;
                                              background:#1877f2;border:1px solid #1877f2;border-radius:4px;
                                              padding:9px 12px;color:#fff;text-decoration:none;
                                              font-size:14px;font-weight:600;">
                                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;flex-shrink:0;fill:#fff;">
                                            <path d="M22.675 0h-21.35C.595 0 0 .595 0 1.326v21.348C0 23.405.595 24 1.326 24H12.82v-9.294H9.692v-3.622h3.128V8.41c0-3.1 1.894-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.114C23.405 24 24 23.405 24 22.674V1.326C24 .595 23.405 0 22.675 0z"/>
                                          </svg>
                                          Sign in with Facebook
                                        </a>
                                      </div>
                                      <div class="col-lg-6 col-md-6 col-sm-12" style="padding:0 5px 10px;">
                                        <a href="google-callback.php"
                                           style="display:flex;align-items:center;justify-content:center;gap:10px;
                                              background:#fff;border:1px solid #dadce0;border-radius:4px;
                                              padding:9px 12px;color:#3c4043;text-decoration:none;
                                              font-size:14px;font-weight:600;">
                                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style="width:20px;height:20px;flex-shrink:0;">
                                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                                          </svg>
                                          Sign in with Google
                                        </a>
                                      </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                      <p style="text-align: center;"><a href="../index.php">Back Home!!!</a></p>
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