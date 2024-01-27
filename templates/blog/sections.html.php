    <?php
        // Navigation
        include(BASE_DIRECTORY . 'templates/_include/navigation.html.php');
        // Flash messages
        include(BASE_DIRECTORY . 'templates/_include/messages.html.php');
    ?>
<!-- Main Content - Blog sections -->
    <main class="container">
        <div class="row">
            <div class="col">
                <nav class="bg-light rounded-3 px-3 py-2 mb-4" aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="<?php echo path(); ?>" class="link-secondary">Blog</a></li>
                        <li class="breadcrumb-item active">Article categories</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <?php 
                        foreach($data['sections'] as $object) {
                            ($object->image_thumb != null) ? $image = $object->image_thumb : $image = 'no-image.jpg';
                            $link = linkSEO('sec', $object->id, $object->section_name);
                    ?>

                    <div class="col-md-6 d-flex align-items-stretch mb-4 aos-init aos-animate" data-aos="zoom-in" data-aos-delay="200">
                        <div class="dbm-box-image">
                            <div class="image">
                                <img src="<?php echo path('images/blog/category/thumb/' . $image); ?>" alt="<?php echo output($object->section_name); ?>" class="img-fluid img-thumbnail" style="width:100%;max-height:215px">
                            </div>
                            <h4 class="title"><a href="<?php echo path($link); ?>" title="<?php echo output($object->section_name); ?>"><?php echo truncate($object->section_name, 40); ?></a></h4>
                            <p class="description"><?php echo truncate($object->section_description, 300); ?></p>
                        </div>
                    </div>
                    <?php }; ?>

                </div>
            </div>
            <div class="col-md-4">
                <div class="position-sticky" style="top: 2rem;">
                    <?php
                        // Box right about
                        include(BASE_DIRECTORY . 'templates/_include/box_right_about.html.php');
                        // Box right about
                        include(BASE_DIRECTORY . 'templates/_include/box_right_images.html.php');
                    ?>
                </div>
            </div>
        </div>
    </main>
