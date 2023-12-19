<!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a href="<?php echo path(); ?>" class="sidebar-brand d-flex align-items-center justify-content-center">
                <div class="sidebar-brand-icon ?rotate-n-15"><i class="fas fa-home"></i></div>
                <div class="sidebar-brand-text ml-2">DbM Framework</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item active">
                <a class="nav-link" href="<?php echo path('panel'); ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Interfejs</div>
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne"><i class="fas fa-fw fa-cog"></i><span>Management</span></a>
                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionSidebar">
                    <div class="collapse-inner bg-white rounded py-2">
                        <h6 class="collapse-header">Pages on text files</h6>
                        <a class="collapse-item" href="<?php echo path('panel/managePage'); ?>">Manage pages</a>
                        <h6 class="collapse-header">Blog and articles</h6>
                        <a class="collapse-item" href="<?php echo path('panel/manageBlog'); ?>">Manage blog</a>
                        <a class="collapse-item" href="<?php echo path('panel/manageBlogSections'); ?>">Manage blog sections</a>
                    </div>
                </div>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Accessories</div>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo path('panel/tabels.html'); ?>"><i class="fas fa-fw fa-table"></i><span>Tables example</span></a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Tools</div>
            <li class="nav-item">
                <a class="nav-link" href="http://localhost/phpmyadmin/" target="_blank"><i class="fas fa-fw fa-database"></i><span>SQL Database</span></a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
            <div class="sidebar-card d-none d-lg-flex">
                <img class="sidebar-card-illustration mb-2" src="<?php echo path('admin/img/undraw_rocket.svg'); ?>" alt="...">
                <p class="text-center mb-2"><strong>DbM Framework</strong> check if an upgrade is available.</p>
                <a href="https://dbm.org.pl" class="btn btn-success btn-sm" target="_blank">Upgrade</a>
            </div>
        </ul>
        <!-- End of Sidebar -->
