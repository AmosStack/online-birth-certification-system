<?php
declare(strict_types=1);

require_once __DIR__ . '/dbconnection.php';

obcs_require_admin();

$aid = (int) $_SESSION['obcsaid'];
$query = $dbh->prepare('SELECT AdminName, Email FROM tbladmin WHERE ID = :aid LIMIT 1');
$query->bindParam(':aid', $aid, PDO::PARAM_INT);
$query->execute();
$admin = $query->fetch();
?>
  <div class="left-sidebar-pro">
            <nav id="sidebar">
                <div class="sidebar-header">
                    <a href="#"><img src="img/message/avatar.jpg" alt="" />
                    </a>
                                        <h3><?php echo obcs_escape($admin ? $admin->AdminName : 'Admin');?></h3>
                                        <p><?php echo obcs_escape($admin ? $admin->Email : '');?></p>
                </div>
                <div class="left-custom-menu-adp-wrap">
                    <ul class="nav navbar-nav left-sidebar-menu-pro">
                        <li class="nav-item">
                            <a href="dashboard.php" role="button" aria-expanded="false"><i class="fa big-icon fa-home"></i> <span class="mini-dn">Home</span> </a>
                            
                        </li>
                       
                       
                      
                       
                        <li class="nav-item"><a href="#" data-toggle="dropdown" role="button" aria-expanded="false" class="nav-link dropdown-toggle"><i class="fa big-icon fa-table"></i> <span class="mini-dn">Birth Certificate App.</span> <span class="indicator-right-menu mini-dn"><i class="fa indicator-mn fa-angle-left"></i></span></a>
                            <div role="menu" class="dropdown-menu left-menu-dropdown animated flipInX">
                                <a href="new-applications.php" class="dropdown-item">New Application</a>
                                <a href="verified-application.php" class="dropdown-item">Verified Applications</a>
                                <a href="rejected-applications.php" class="dropdown-item">Rejected Applications</a>
                                <a href="all-applications.php" class="dropdown-item">All Applications</a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a href="between-dates-report.php" role="button" aria-expanded="false"><i class="fa big-icon fa-files-o"></i> <span class="mini-dn">B/W Dates Reports</span> </a>
                            
                        </li>
                        <li class="nav-item">
                            <a href="search.php" role="button" aria-expanded="false"><i class="fa fa-search"></i> <span class="mini-dn">Search</span> </a>
                            
                        </li>
                             <li class="nav-item">
                            <a href="  registered-users.php" role="button" aria-expanded="false"><i class="fa fa-users"></i> <span class="mini-dn">Registered Users</span> </a>
                            
                        </li>
                   
                      
                    </ul>
                </div>
            </nav>
        </div>
