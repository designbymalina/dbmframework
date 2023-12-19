    <!-- Header -->
    <h1 class="visually-hidden"><?php Dbm\Classes\TemplateClass::trans('website.name'); ?></h1>
    <header class="navbar navbar-expand-md shadow py-3 dbm-header">
        <nav class="container-md flex-wrap flex-md-nowrap position-relative" aria-label="<?php Dbm\Classes\TemplateClass::trans('navbar.main_navigation'); ?>">
            <a href="<?php echo path(); ?>" class="navbar-brand text-white text-decoration-none"><img src="<?php echo path('images/logo.png'); ?>" class="img-fluid align-middle me-3" alt="<?php Dbm\Classes\TemplateClass::trans('website.name'); ?>"><h2 class="d-inline"><?php Dbm\Classes\TemplateClass::trans('index.header'); ?></h2></a>
            <div class="position-absolute top-0 end-0">
                <?php Dbm\Classes\TemplateClass::htmlLanguage(path()); ?>
            </div>
        </nav>
    </header>
