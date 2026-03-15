<?php
declare(strict_types=1);

require_once __DIR__ . '/dbconnection.php';

obcs_require_user();

$uid = (int) $_SESSION['obcsuid'];
$query = $dbh->prepare('SELECT FirstName, LastName, MobileNumber FROM tbluser WHERE ID = :uid LIMIT 1');
$query->bindParam(':uid', $uid, PDO::PARAM_INT);
$query->execute();
$user = $query->fetch();
?>
<div class="content-inner-all">
    <div class="header-top-area">
        <div class="fixed-header-top">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-1 col-md-6 col-sm-6 col-xs-12">
                        <button type="button" id="sidebarCollapse" class="btn bar-button-pro header-drl-controller-btn btn-info navbar-btn">
                            <i class="fa fa-bars"></i>
                        </button>
                        <div class="admin-logo logo-wrap-pro">
                            <a href="profile.php"><img src="img/logo/log.png" alt="" />
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-1 col-sm-1 col-xs-12">
                        <div class="header-top-menu tabl-d-n">
                            <ul class="nav navbar-nav mai-top-nav">
                                <li class="nav-item"><a href="dashboard.php" class="nav-link">Home</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-6 col-xs-12">
                        <div class="header-right-info">
                            <ul class="nav navbar-nav mai-top-nav header-right-menu">
                                <li class="nav-item">
                                    <a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle">
                                        <span class="adminpro-icon adminpro-user-rounded header-riht-inf"></span>
                                        <span class="admin-name"><?php echo obcs_escape($user ? $user->FirstName : 'User'); ?> <?php echo obcs_escape($user ? $user->LastName : ''); ?></span>
                                        <span class="author-project-icon adminpro-icon adminpro-down-arrow"></span>
                                    </a>
                                    <ul role="menu" class="dropdown-header-top author-log dropdown-menu animated flipInX">
                                        <li><a href="profile.php"><span class="adminpro-icon adminpro-user-rounded author-log-ic"></span>My Profile</a>
                                        </li>
                                        <li><a href="change-password.php"><span class="adminpro-icon adminpro-settings author-log-ic"></span>Settings</a>
                                        </li>
                                        <li><a href="logout.php"><span class="adminpro-icon adminpro-locked author-log-ic"></span>Log Out</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
