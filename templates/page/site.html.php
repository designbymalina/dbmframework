<?php
    // Navigation
    include(BASE_DIRECTORY . 'templates/_include/navigation.html.php');
?>
    <!-- Breadcrumb -->
    <section class="container">
        <nav class="bg-light rounded-3 px-3 py-2 mb-4" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?php echo path(); ?>" class="link-secondary">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo path('page'); ?>" class="link-secondary">Landing pages</a></li>
                <li class="breadcrumb-item active"><?php echo $data['meta.title']; ?></li>
            </ol>
        </nav>
    </section>
    <!-- Main Content - Site -->
    <main>
        <?php echo $data['page_content']; ?>
    </main>
