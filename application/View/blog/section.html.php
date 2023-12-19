    <?php
        // Navigation
        include(BASE_DIRECTORY . 'application/View/_include/navigation.html.php');
        // Flash messages
        include(BASE_DIRECTORY . 'application/View/_include/messages.html.php');
    ?>
<!-- Main Content - Blog section -->
    <main class="container">
        <div class="row">
            <div class="col">
                <nav class="bg-light rounded-3 px-3 py-2 mb-4" aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?php echo path(); ?>" class="link-secondary">Blog</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo path('blog/sections'); ?>" class="link-secondary">Article categories</a></li>
                        <li class="breadcrumb-item active"><?php if (!empty($data['section'])) : echo $data['section']['section_name']; endif; ?></li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <?php if (is_array($data['articles'])) : ?>
                    <?php foreach($data['articles'] as $object) : ?>
                    <?php ($object->image_thumb != null) ? $image = $object->image_thumb : $image = 'no-image.jpg'; ?>
                    
                    <div class="col-md-6 d-flex align-items-stretch mb-4 aos-init aos-animate" data-aos="zoom-in" data-aos-delay="200">
                        <div class="dbm-box-image">
                            <div class="image">
                                <img src="<?php echo path('images/blog/thumb/' . $image); ?>" alt="<?php echo output($object->page_header); ?>" class="img-fluid img-thumbnail" style="width:100%;max-height:215px">
                            </div>
                            <h4 class="title"><a href="<?php echo linkSEO('art', $object->aid, $object->page_header); ?>" title="<?php echo output($object->page_header); ?>"><?php echo truncate($object->page_header, 40); ?></a></h4>
                            <h6 class="details"><span class="me-1">By</span><a href="<?php echo linkSEO('user', $object->uid); ?>" class="link-dark"><?php echo $object->fullname; ?></a><span class="mx-1">in</span><a href="<?php echo linkSEO('sec', $object->sid, $object->section_name); ?>" class="link-dark"><?php echo $object->section_name; ?></a></h6>
                            <p class="description"><?php echo truncate($object->page_content, 300); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>

                    <div class="col-md-12">
                        <div class="alert alert-info"><?php echo $data['articles']; ?></div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <div class="col-md-4">
                <div class="position-sticky" style="top: 2rem;">
                    <?php
                        // Box right about
                        include(BASE_DIRECTORY . 'application/View/_include/box_right_about.html.php');
                        // Box right about
                        include(BASE_DIRECTORY . 'application/View/_include/box_right_images.html.php');
                    ?>
                </div>
            </div>
        </div>
    </main>
