    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php
            include('../application/View/_include/panel_sidebar.html.php');
        ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php
                    include('../application/View/_include/panel_topbar.html.php');
                ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent">
                                <li class="breadcrumb-item active" aria-current="page"><h1 class="h3 mb-0 text-gray-800">Manage pages on text files</h1></li>
                            </ol>
                        </nav>
                        <a href="<?php echo path('panel/createOrEditPage'); ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm font-weight-bold"><i class="fas fa-plus text-white-50 mr-2"></i>Create page</a>
                    </div>
                    <?php
                        // Panel flash messages
                        include('../application/View/_include/panel_messages.html.php');
                    ?>
                    <!-- DataTales -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 text-primary"><span class="font-weight-bold mr-1">Path to folder files txt:</span><?php echo $data['dir']; ?></h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dataTableOne" class="table table-striped" width="100%" cellspacing="0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="65%">Filename / data for page-name.txt</th>
                                            <th width="20%">Created</th>
                                            <th width="10%" class="text-right noSort">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        <?php foreach ($data['files'] as $item): ?>
                                        <tr>
                                            <td class="font-weight-bold"><?php echo $i++; ?></td>
                                            <td class="font-weight-bold"><?php echo $item ?></td>
                                            <td><?php echo date("Y-m-d H:i:s", filemtime($data['dir'].$item)); ?></td>
                                            <td class="text-right"><div class="btn-group"><button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-h mr-1"></i></button><div class="dropdown-menu dropdown-menu-right"><a href="<?php echo 'createOrEditPage?file=' . $item; ?>" class="dropdown-item text-primary" type="button">Edit</a><button type="button" class="dropdown-item text-danger deleteFile" data-toggle="modal" data-target=".deleteModal" data-file="<?php echo $item ?>">Delete</button></div></div></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- End of Main Content -->
            <?php
                include('../application/View/_include/panel_footer.html.php');
            ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    <?php
        include('../application/View/_include/panel_logout.html.php');
    ?>
