    <!-- Main Content -->
    <main>
        <div class="container mt-5 px-4">
            <p class="fs-5"><?php Dbm\Classes\TemplateClass::trans('index.lead'); ?></p>
            <div class="row row-cols-1 row-cols-lg-2 g-4 py-4">
                <div class="feature col">
                    <h2><?php Dbm\Classes\TemplateClass::trans('index.content_landingpage_header'); ?></h2>
                    <p><?php Dbm\Classes\TemplateClass::trans('index.content_landingpage_description'); ?></p>
                    <p class="p-3 bg-info bg-opacity-10 border border-info rounded rounded-3">Zapoznaj się z instrukcją w pliku README.md i skonfiguruj poprawnie aplikacje.</p>
                    <ul>
                        <li>
                            <a href="home" class="fw-bold">Home</a> - home (index in HomeController)
                            <p class="small ms-3 mb-0">Starter aplikacji dla DbMFramework.</p>
                        </li>
                        <li>
                            <a href="page" class="fw-bold">Page</a> - page (index in PageController)
                            <p class="small ms-3 mb-0">Adres: 'page' - przykład przekierowania do strony głównej ustawionej na wersje blog/portal.</p>
                        </li>
                        <li>
                            <a href="page/site" class="fw-bold">Page -> site (name)</a> - SEO friendly (simple without rules)
                            <p class="small ms-3 mb-0">Przykładowy adres: <b>page/site</b> (.html) i kolejne <b>page/site</b><b class="text-success">-website-title</b><b>.html</b> - zawartość strony (content) należy utworzyć odpowiednio dla nazwy w pliku page-site-website-title.txt.</p>
                        </li>
                        <li>
                            <a href="your-website-title,site.html" class="fw-bold">Your website title</a> - SEO friendly (with RewriteRule)
                            <p class="small ms-3 mb-0">Przykładowy adres: <b class="text-success">website-title</b><b>,site.html</b> - zawartość strony (content) należy utworzyć odpowiednio dla nazwy w pliku page-website-title.txt.</p>
                        </li>
                    </ul>
                </div>
                <div class="feature col">
                    <h2><?php Dbm\Classes\TemplateClass::trans('index.content_blog_header'); ?></h2>
                    <p><?php Dbm\Classes\TemplateClass::trans('index.content_blog_description'); ?></p>
                    <p class="p-3 bg-info bg-opacity-10 border border-info rounded rounded-3">W celu rejestracji, logowania użytkownika na konto oraz uruchomienia bardziej zaawansowanych funkcjonalności utwórz bazę danych. Przykładowa baza, którą możesz zaimportować dla stworzonego projektu - za pomocą narzędzia (phpMyAdmin) służącego do zarządzania bazą danych - znajduje się w dokumentacji w katalogu Database plik dbm_cms.sql.</p>
                    <ul>
                        <li><a href="./" class="fw-bold">Blog / Portal</a></li>
                        <li><a href="panel" class="fw-bold">Demo Administration Panel</a></li>
                    </ul>
                    <h3>Rozszerzenia</h3>
                    <ul>
                        <li><a href="#" class="fw-bold" target="_blank">System sprzedaży</a></li>
                        <li><a href="#" class="fw-bold" target="_blank">Skrypt płatności</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
