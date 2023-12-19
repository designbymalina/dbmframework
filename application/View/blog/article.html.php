    <?php
        // Navigation
        include(BASE_DIRECTORY . 'application/View/_include/navigation.html.php');
        // Flash messages
        include(BASE_DIRECTORY . 'application/View/_include/messages.html.php');
        // Code
        if (!empty($data['article'])) : $article = $data['article'];
    ?>
<!-- Breadcrumb -->
    <section class="container">
        <nav class="bg-light rounded-3 px-3 py-2 mb-4" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?php echo path(); ?>" class="link-secondary">Blog</a></li>
                <li class="breadcrumb-item"><a href="<?php echo path('blog/sections'); ?>" class="link-secondary">Article categories</a></li>
                <li class="breadcrumb-item"><a href="<?php echo linkSEO('sec', $article->sid, $article->section_name); ?>" class="link-secondary"><?php echo $article->section_name; ?></a></li>
                <li class="breadcrumb-item active"><?php echo $article->page_header; ?></li>
            </ol>
        </nav>
    </section>
    <!-- Main Content - Blog article -->
    <main class="container">
        <div class="row">
            <div class="col-md-8">
                <h1 class="pb-4 mb-4 fst-italic border-bottom"><?php echo truncate($article->page_header, 45); ?></h1>
                <article class="blog-post mb-3">
                    <?php echo outputHTML($article->page_content); ?>
    			</article>
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
    <?php else: ?>

    <!-- Main Content - Blog article -->
    <main class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger">An unexpected error occurred, required data is missing.</div>
            </div>
        </div>
    </main>
    <?php endif; ?>
