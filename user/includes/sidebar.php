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
<div class="left-sidebar-pro">
    <nav id="sidebar">
        <div class="sidebar-header">
            <a href="#"><img src="img/message/avatar.jpg" alt="" />
            </a>
            <h3><?php echo obcs_escape($user ? $user->FirstName : 'User'); ?> <?php echo obcs_escape($user ? $user->LastName : ''); ?></h3>
            <p><?php echo obcs_escape($user ? $user->MobileNumber : ''); ?></p>
        </div>
        <div class="left-custom-menu-adp-wrap">
            <ul class="nav navbar-nav left-sidebar-menu-pro">
                <li class="nav-item">
                    <a href="dashboard.php" role="button" aria-expanded="false"><i class="fa big-icon fa-home"></i> <span class="mini-dn">Home</span> </a>
                </li>
                <li class="nav-item"><a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle"><i class="fa big-icon fa-envelope"></i> <span class="mini-dn">Birth Reg Form</span> <span class="indicator-right-menu mini-dn"><i class="fa indicator-mn fa-angle-left"></i></span></a>
                    <div role="menu" class="dropdown-menu left-menu-dropdown animated flipInX">
                        <a href="certificate-form.php" class="dropdown-item">Add Details</a>
                        <a href="manage-forms.php" class="dropdown-item">Manage Details</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="certificates-list.php" role="button" aria-expanded="false"><i class="fa big-icon fa-user"></i> <span class="mini-dn">Certificates</span> </a>
                </li>
            </ul>
        </div>
    </nav>
</div>
