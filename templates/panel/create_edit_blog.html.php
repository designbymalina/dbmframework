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
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent">
                                <li class="breadcrumb-item"><h1 class="h3 mb-0 text-gray-800"><a href="<?php echo path('panel/manageBlog'); ?>" class="text-dark">Manage blog</a></h1></li>
                                <li class="breadcrumb-item active h3" aria-current="page"><?php echo $data['header']; ?></li>
                            </ol>
                        </nav>
                        <a href="<?php echo path('panel/createOrEditBlog'); ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm font-weight-bold"><i class="fas fa-plus text-white-50 mr-2"></i>Create article</a>
                    </div>
                    <?php
                        // Panel flash messages
                        include(BASE_DIRECTORY . 'templates/_include/panel_messages.html.php');
                        // Form data fields
                        !empty($data['id']) ? $formId = '?id=' . $data['id'] : $formId = null;
                    ?>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form action="<?php echo path('panel/'. $data['action']); ?>" method="POST">
                                <div class="accordion" id="accordionFields">
                                    <div id="collapseFieldsOne" class="collapse collapseOpen" data-parent="#accordionFields">
                                        <div class="form-group">
                                            <?php if (!empty($data['id'])) echo '<span class="float-right">Article ID: ' . $data['id'] . '</span>'; ?>
                                            <label for="form_title" class="font-weight-bold">Meta title</label>
                                            <input type="text" name="title" id="form_title" class="form-control" placeholder="write the website title" value="<?php if (!empty($data['fields']->title)) : echo $data['fields']->title; endif; ?>" minlength="5" maxlength="65" required>
                                            <div class="text-danger small"><?php if (!empty($data['errorTitle'])) : echo $data['errorTitle']; endif; ?></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="form_description" class="font-weight-bold">Meta description</label>
                                            <input type="text" name="description" id="form_description" class="form-control" placeholder="write the website description" value="<?php if (!empty($data['fields']->description)) : echo $data['fields']->description; endif; ?>" maxlength="250" required>
                                            <div class="text-danger small"><?php if (!empty($data['errorDescription'])) : echo $data['errorDescription']; endif; ?></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="form_keywords"><span class="font-weight-bold">Meta keywords</span>, separated by a comma</label>
                                            <input type="text" name="keywords" id="form_keywords" class="form-control" placeholder="write the website keywords" value="<?php if (!empty($data['fields']->keywords)) : echo $data['fields']->keywords; endif; ?>" maxlength="250" required>
                                            <div class="text-danger small"><?php if (!empty($data['errorKeywords'])) : echo $data['errorKeywords']; endif; ?></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="form_header" class="font-weight-bold">Title and article header</label>
                                            <input type="text" name="header" id="form_header" class="form-control" placeholder="write the article header" value="<?php if (!empty($data['fields']->header)) : echo $data['fields']->header; endif; ?>" minlength="10" maxlength="120" required>
                                            <div class="text-danger small"><?php if (!empty($data['errorHeader'])) : echo $data['errorHeader']; endif; ?></div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <label for="form_section" class="font-weight-bold">Article section</label>
                                                <?php
                                                    !empty($data['fields']->sid) ? $sid = $data['fields']->sid : $sid = null;
                                                    echo htmlSelect($data['sections'], 'section', $sid, 'asc', 'required');
                                                ?>
                                                <div class="text-danger small"><?php if (!empty($data['errorSection'])) : echo $data['errorSection']; endif; ?></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label for="form_user" class="font-weight-bold">Article user</label>
                                                <?php
                                                    !empty($data['fields']->uid) ? $uid = $data['fields']->uid : $uid = (int) $this->getSession('dbmUserId');
                                                    echo htmlSelect($data['users'], 'user', $uid, 'asc', 'required');
                                                ?>
                                                <div class="text-danger small"><?php if (!empty($data['errorUser'])) : echo $data['errorUser']; endif; ?></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="form_image"><span class="font-weight-bold">Article main image</span>, optionally select and insert one of the uploaded images, only the image name with extension.</label>
                                            <input type="text" name="image" id="formImage" class="form-control" placeholder="insert the image-name.jpg" value="<?php if (!empty($data['fields']->image)) : echo $data['fields']->image; endif; ?>" maxlength="40" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="formContent" class="font-weight-bold"><span class="mr-2" title="View remaining fields" data-toggle="tooltip" data-placement="top"><i id="iconUpDown" class="fas fa-angle-up text-primary" data-toggle="collapse" data-target="#collapseFieldsOne" aria-expanded="false" aria-controls="collapseFieldsOne"></i></span>HTML content</label>
                                    <textarea name="content" id="formContent" class="form-control" rows="15" minlength="1000" onKeyDown="if(event.keyCode===9){var v=this.value,s=this.selectionStart,e=this.selectionEnd;this.value=v.substring(0, s)+'\t'+v.substring(e);this.selectionStart=this.selectionEnd=s+1;return false;}" required><?php if (!empty($data['fields']->content)) : echo $data['fields']->content; endif; ?></textarea>
                                    <div class="text-danger small"><?php if (!empty($data['errorContent'])) : echo $data['errorContent']; endif; ?></div>
                                </div>
                                <div class="form-group">
                                    <a href="<?php echo path('panel/createOrEditBlog' . $formId); ?>" class="btn btn-primary mr-md-2">Reload</a>
                                    <button type="button" id="previewContent" class="btn btn-primary mr-md-2">Preview</button>
                                    <button type="button" class="btn btn-primary mr-md-2" data-toggle="modal" data-target="#imagesModal">Images</button>
                                    <button type="submit" class="btn btn-primary text-uppercase"><?php echo $data['submit']; ?></button>
                                    <?php if (!empty($data['id'])) : ?>
                                        <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                    <?php endif; ?>
                                </div>
                            </form>
                            <ul>
                                <li>You can use <span class="text-danger">HTML</span> code in content textarea (e.g. &lt;p&gt;, &lt;ul&gt; etc. and &lt;img src=&quot;{{url}}images/name.jpg&quot; class=&quot;img-fluid&quot; alt=&quot;Short description of the image&quot;&gt;).</li>
                                <li>You can use <span class="text-danger">{{url}}</span> code to add a direct url to the content (e.g. for an image address).</li>
                            </ul>
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
