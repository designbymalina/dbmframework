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
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent">
                                <li class="breadcrumb-item"><h1 class="h3 mb-0 text-gray-800"><a href="<?php echo path('panel/manageBlogSections'); ?>" class="text-dark">Manage blog categories</a></h1></li>
                                <li class="breadcrumb-item active h3" aria-current="page"><?php echo $data['header']; ?></li>
                            </ol>
                        </nav>
                        <a href="<?php echo path('panel/createOrEditBlogSection'); ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm font-weight-bold"><i class="fas fa-plus text-white-50 mr-2"></i>Create section</a>
                    </div>
                    <?php
                        // Panel flash messages
                        include('../application/View/_include/panel_messages.html.php');
                        // Form data fields
                        !empty($data['id']) ? $formId = '?id=' . $data['id'] : $formId = null;
                    ?>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form action="<?php echo path('panel/'. $data['action']); ?>" method="POST" novalidate>
                                <div class="form-group">
                                    <?php if (!empty($data['id'])) echo '<span class="float-right">Section ID: ' . $data['id'] . '</span>'; ?>
                                    <label for="form_name" class="font-weight-bold">Section name</label>
                                    <input type="text" name="name" id="form_name" class="form-control" placeholder="write section name" value="<?php if (!empty($data['fields'])) : echo $data['fields']->name; endif; ?>" minlength="3" maxlength="100" required>
                                    <div class="text-danger small"><?php if (!empty($data['errorName'])) : echo $data['errorName']; endif; ?></div>
                                </div>
                                <div class="form-group">
                                    <label for="form_keywords"><span class="font-weight-bold">Keywords</span>, separated by a comma</label>
                                    <input type="text" name="keywords" id="form_keywords" class="form-control" placeholder="write keywords" value="<?php if (!empty($data['fields'])) : echo $data['fields']->keywords; endif; ?>" maxlength="250" required>
                                    <div class="text-danger small"><?php if (!empty($data['errorKeywords'])) : echo $data['errorKeywords']; endif; ?></div>
                                </div>
                                <div class="form-group">
                                    <label for="form_image"><span class="font-weight-bold">Section main image</span>, optionally select and insert one of the uploaded images, only the image name with extension.</label>
                                    <input type="text" name="image" id="formImage" class="form-control" placeholder="insert the image-name.jpg" value="<?php if (!empty($data['fields'])) : echo $data['fields']->image; endif; ?>" maxlength="40" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="form_description" class="font-weight-bold">Description</label>
                                    <textarea name="description" id="form_description" class="form-control" rows="3" minlength="300" onKeyDown="if(event.keyCode===9){var v=this.value,s=this.selectionStart,e=this.selectionEnd;this.value=v.substring(0, s)+'\t'+v.substring(e);this.selectionStart=this.selectionEnd=s+1;return false;}" required><?php if (!empty($data['fields'])) : echo $data['fields']->description; endif; ?></textarea>
                                    <div class="text-danger small"><?php if (!empty($data['errorDescription'])) : echo $data['errorDescription']; endif; ?></div>
                                </div>
                                <div class="form-group">
                                    <a href="<?php echo path('panel/createOrEditBlogSection' . $formId); ?>" class="btn btn-primary mr-md-2">Reload</a>
                                    <button type="button" class="btn btn-primary mr-md-2" data-toggle="modal" data-target="#imagesModal">Images</button>
                                    <button type="submit" class="btn btn-primary text-uppercase"><?php echo $data['submit']; ?></button>
                                    <?php if (!empty($data['id'])) : ?>
                                        <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                    <?php endif; ?>
                                </div>
                            </form>
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
