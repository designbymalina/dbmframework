    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container d-flex flex-wrap justify-content-between">
            <ul class="col-md-4 nav">
                <li class="nav-item"><a href="<?php echo path('about.html'); ?>" class="nav-link px-2 text-muted"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.about'); ?></a></li>
                <li class="nav-item"><a href="<?php echo path('contact.html'); ?>" class="nav-link px-2 text-muted"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.contact'); ?></a></li>
                <li class="nav-item"><a href="<?php echo path('regulation.html'); ?>" class="nav-link px-2 text-muted"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.regulation'); ?></a></li>
            </ul>
            <p class="col-md-8 mt-2 justify-content-end text-center text-md-end text-muted"><?php Dbm\Classes\TemplateClass::trans('website.footer', [], [date('Y'), '<a href="https://dbm.org.pl/" class="link-secondary text-decoration-none" target="_blank">Design&nbsp;by&nbsp;Malina</a>']); ?></p>
        </div>
    </footer>
    <!-- JavaScript Body -->
    <script src="<?php echo path('assets/js/popper.min.js'); ?>"></script>
	<script src="<?php echo path('assets/js/bootstrap.min.js'); ?>"></script>
