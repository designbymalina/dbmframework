    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php
            include(BASE_DIRECTORY . 'templates/_include/panel_sidebar.html.php');
        ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php
                    include(BASE_DIRECTORY . 'templates/_include/panel_topbar.html.php');
                ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent">
                                <li class="breadcrumb-item active" aria-current="page"><h1 class="h3 mb-0 text-gray-800">Manage blog categories</h1></li>
                            </ol>
                        </nav>
                        <a href="<?php echo path('panel/createOrEditBlogSection'); ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm font-weight-bold"><i class="fas fa-plus text-white-50 mr-2"></i>Create section</a>
                    </div>
                    <?php
                        // Panel flash messages
                        include(BASE_DIRECTORY . 'templates/_include/panel_messages.html.php');
                    ?>

                    <!-- DataTales -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dataTableOne" class="table table-striped" width="100%" cellspacing="0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">ID</th>
                                            <th width="40%">Section name</th>
                                            <th width="5%" class="text-right noSort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($data['sections'] != null): ?>
                                        <?php foreach ($data['sections'] as $item): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?php echo $item->id; ?></td>
                                            <td class="font-weight-bold"><?php echo $item->section_name; ?></td>
                                            <td class="text-right"><div class="btn-group"><button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-h mr-1"></i></button><div class="dropdown-menu dropdown-menu-right"><a href="<?php echo 'createOrEditBlogSection?id=' . $item->id; ?>" class="dropdown-item text-primary" type="button">Edit</a><button type="button" class="dropdown-item text-danger deleteArticle" data-toggle="modal" data-target=".deleteModal" data-id="<?php echo $item->id; ?>">Delete</button></div></div></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- End of Main Content -->
            <?php
                include(BASE_DIRECTORY . 'templates/_include/panel_footer.html.php');
            ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    <?php
        include(BASE_DIRECTORY . 'templates/_include/panel_logout.html.php');
    ?>
