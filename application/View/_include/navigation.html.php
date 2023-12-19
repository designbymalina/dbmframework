<!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark dbm-bg-navbar" aria-label="Navbar Top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo path('home'); ?>"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.start'); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTop" aria-controls="navbarTop" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarTop">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo path(); ?>"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.home'); ?></a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownPage" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.pages'); ?></a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownPage">
                            <li><a class="dropdown-item" href="<?php echo path('page'); ?>">Landing pages</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo path('the-best-offer,offer.html'); ?>">The best offer</a></li>
                            <li><a class="dropdown-item" href="<?php echo path('itaque-ad-tempus-ad-pisonem-omnes,offer.html'); ?>">Itaque ad tempus...</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownBlog" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.blog'); ?></a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownBlog">
                            <li><a class="dropdown-item" href="<?php echo path('blog'); ?>">Blog</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo path('blog/sections'); ?>">Categories<i class="bi bi-caret-right float-end"></i></a>
                                <ul class="dropdown-menu dropdown-submenu">
                                    <li><a class="dropdown-item" href="<?php echo path('3d-graphics,sec,2.html'); ?>">3D Graphics</a></li>
                                    <li><a class="dropdown-item" href="<?php echo path('internet-marketing,sec,3.html'); ?>">Internet marketing</a></li>
                                    <li><a class="dropdown-item" href="<?php echo path('lifestyle,sec,4.html'); ?>">Lifestyle</a></li>
                                    <li><a class="dropdown-item" href="<?php echo path('web-design,sec,1.html'); ?>">Web design</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo path('index/link.html'); ?>"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.link'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.disabled'); ?></a>
                    </li>
                </ul>
                <div class="d-flex text-white">
                    <!-- TODO! echo htmlUserNavigation($this->getSession('dbmUserId')) -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="width:100%">
                        <li class="nav-item dropdown<?php if ($this->getSession('dbmUserId')) { echo ' no-arrow'; } ?>">
                            <a class="nav-link dropdown-toggle ?link-light" href="#" id="navbarAccount" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php 
                                    if ($this->getSession('dbmUserId')) { 
                                        Dbm\Classes\TemplateClass::temp_htmlUser($this->getSession('dbmUserId'));
                                    } else { 
                                        echo '<span>' . Dbm\Classes\TemplateClass::trans('navbar.nav.account') . '</span><i class="bi bi-person ms-2"></i>';
                                    }
                                ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarAccount">
                                <?php if ($this->getSession('dbmUserId')) { ?>
                                    <?php if ($this->userPermissions($this->getSession('dbmUserId')) === 'ADMIN') { ?>
                                        <li><a class="dropdown-item" href="<?php echo path('panel'); ?>">Panel administracyjny</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                    <?php } ?>

                                    <li><a class="dropdown-item" href="<?php echo path('account'); ?>">Twój profil</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo path('login/logout'); ?>">Wyloguj się</a></li>

                                <?php } else { ?>

                                <li><a class="dropdown-item" href="<?php echo path('login'); ?>"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.login'); ?></a></li>
                                <li><a class="dropdown-item" href="<?php echo path('register'); ?>"><?php Dbm\Classes\TemplateClass::trans('navbar.nav.register'); ?></a></li>

                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                </div>
                <a href="https://dbm.org.pl/script-php-dbmframework.html" class="btn btn-sm btn-outline-light float-end mt-3 mt-md-0 ms-3 me-md-0" target="_blank">Download</a>
            </div>
        </div>
    </nav>
