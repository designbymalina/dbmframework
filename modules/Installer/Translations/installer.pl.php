<?php

declare(strict_types=1);

// Installer translation (Polish pl-PL)
return [
    'installer.lang' => 'pl',
    'installer.engine' => 'DbM Framework',
    'installer.navbar.home' => 'Strona główna (Utwórz Nowy Projekt)',
    'installer.navbar.extensions' => 'Rozszerzenia',
    'installer.navbar.download' => 'Pobierz',
    'installer.header.title' => 'Witamy w DbM CMS!',
    'installer.header.subtitle' => 'DbM Framework / Asystent instalacji platformy DbM CMS',
    'installer.content.title' => 'Asystent instalacji',
    'installer.progressbar.installation' => 'Postęp instalacji',
    'installer.progressbar.not_started' => 'Pasek postępu nie jest dołączony!',
    'installer.button.next_step' => 'Dalej',
    'installer.button.back' => 'Wstecz',
    'installer.step.start.title' => 'Rozpocznij instalację',
    'installer.step.start.content' => '
        <p><strong>DbM CMS</strong> to szybki i nowoczesny system zarządzania treścią, stworzony z myślą o prostocie użytkowania i instalacji. Gotowe rozwiązanie oparte na frameworku dla tych, którzy chcą szybko uruchomić witrynę lub aplikację bez konieczności kodowania. Obsługuje zarówno proste strony, jak i złożone projekty oparte na bazie danych. Jeśli nie masz czasu na tworzenie własnych modułów, możesz użyć gotowych narzędzi do zarządzania treścią, SEO i strukturą witryny. Dostępne są także gotowe moduły (wtyczki), takie jak CMS Lite, CMS Core, CMS Pro oraz inne, które możesz szybko zainstalować i dostosować do swoich potrzeb. Efektywne rozwiązanie, które przyspiesza rozwój projektu bez utraty na elastyczności frameworka.</p>
        <p>Proces instalacji składa się z kilku prostych kroków i zajmuje około 5 minut.</p>
        <p>Zanim zaczniesz korzystać z aplikacji, zapoznaj się z dokumentacją pod adresem: <a href="https://dbm.org.pl/tworzenie/dbmframework" class="link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover" target="_blank">DbM Framework</a>.</p>
        <ol>
            <li>Przejdź do sekcji &quot;<strong>Instalacja i konfiguracja</strong>&quot; i wykonaj opisane tam czynności.</li>
            <li>Uzupełnij dane konfiguracyjne w pliku <strong>.env</strong> oraz zweryfikuj pliki <strong>.htaccess</strong>.</li>
            <li>Po zakończeniu konfiguracji i wykonaniu kolejnych kroków Platforma będzie gotowa do pracy.</li>
        </ol>
        <p>Potrzebujesz pomocy? Sprawdź szczegółowe instrukcje lub skontaktuj się z autorem.</p>
    ',
    'installer.step.requirements.title' => 'Sprawdzanie wymagań',
    'installer.step.requirements.content' => '
        <p>Przed kontynuowaniem instalacji system zweryfikuje, czy środowisko serwera spełnia wszystkie niezbędne wymagania.</p>
        <p>Sprawdzone zostaną następujące elementy:</p>
        <ul>
        <li>Wersja PHP i wymagane rozszerzenia</li>
        <li>Uprawnienia plików i katalogów</li>
        <li>Zgodność konfiguracji serwera</li>
        <li>Dostępność wymaganych funkcji PHP</li>
        </ul>
        <p>W przypadku wykrycia jakichkolwiek problemów, przed kontynuowaniem zostaniesz poinformowany o szczegółach dotyczących ich rozwiązania.</p>
        <p>Ten krok zapewnia poprawne i bezpieczne działanie aplikacji po instalacji.</p>
    ',
    'installer.step.cmslite.title' => 'Instalowanie CMS Lite',
    'installer.step.cmslite.content' => '
        <p>W tym kroku zostanie zainstalowany i skonfigurowany moduł <strong>CMS Lite</strong>.</p>
        <p>CMS Lite zapewnia lekką i elastyczną warstwę zarządzania treścią, która umożliwia:</p>
        <ul>
        <li>Tworzenie i zarządzanie stronami</li>
        <li>Kontrolowanie strony głównej i struktury witryny</li>
        <li>Rozszerzanie funkcjonalności w późniejszym czasie o dodatkowe moduły CMS</li>
        </ul>
        <p>Moduł automatycznie zintegruje się z systemem routingu i stanie się głównym modułem obsługi treści w witrynie.</p>
        <p>Możesz później uaktualnić lub rozszerzyć CMS Lite bez ponownej instalacji systemu.</p>
    ',
    'installer.step.database.title' => 'Połączenie z bazą danych',
    'installer.step.database.content' => '
        <p>Ten krok weryfikuje połączenie z bazą danych i przygotowuje system do dalszych kroków instalacji.</p>
        <p>Instalator:</p>
        <ul>
        <li>Sprawdź dane uwierzytelniające i połączenie z bazą danych</li>
        <li>Sprawdź zgodność serwera bazy danych</li>
        <li>Przygotuj środowisko do migracji baz danych</li>
        </ul>
        <p>Na tym etapie żadne dane nie zostaną zmodyfikowane. Ten krok zapewnia jedynie gotowość bazy danych do użycia przez system.</p>
        <p>Rzeczywista struktura bazy danych zostanie utworzona w kolejnych krokach.</p>
    ',
    'installer.step.authentication.title' => 'Utwórz system uwierzytelniania',
    'installer.step.authentication.content' => '
        <p>W tym kroku zostanie przygotowany system uwierzytelniania.</p>
        <p>System skonfiguruje podstawową strukturę wymaganą dla:</p>
        <ul>
        <li>Kont użytkowników</li>
        <li>Mechanizmów logowania i wylogowywania</li>
        <li>Obsługi sesji i bezpieczeństwa</li>
        </ul>
        <p>Ta funkcjonalność jest wymagana do uzyskania dostępu do panelu administracyjnego i zarządzania chronionymi obszarami aplikacji.</p>
        <p>W razie potrzeby funkcje uwierzytelniania będzie można później rozszerzyć.</p>
    ',
    'installer.step.admin.title' => 'Utwórz panel administracyjny',
    'installer.step.admin.content' => '
        <p>Ten krok instaluje i konfiguruje panel administracyjny.</p>
        <p>Panel administracyjny umożliwia:</p>
        <ul>
        <li>Zarządzanie treścią witryny</li>
        <li>Konfigurowanie ustawień systemu</li>
        <li>Kontrolowanie użytkowników i uprawnień</li>
        </ul>
        <p>Po instalacji będziesz mógł zalogować się na konto administratora i zarządzać witryną za pomocą przyjaznego interfejsu użytkownika.</p>
    ',
    'installer.step.finish.title' => 'Gratulacje!',
    'installer.step.finish.content' => '
        <p>Instalacja <strong>DbM CMS Lite</strong> została pomyślnie zakończona.</p>
        <p>Twój system jest teraz gotowy do użycia. Możesz zacząć budować swoją witrynę, zarządzać treścią i rozszerzać funkcjonalność o dodatkowe moduły.</p>
        <p>Ze względów bezpieczeństwa upewnij się, że instalator nie jest już dostępny.</p>
        <p>Ciesz się pracą z DbM CMS!</p>
    ',
    'installer.requirements.msg.min_requirements' => 'Niezbędne wymagania dla CMS Lite',
    'installer.requirements.msg.php_ok' => 'Wersja PHP ≥ %s jest zgodna z wymaganiami',
    'installer.requirements.msg.php_fail' => 'Wersja PHP musi być ≥ %s',
    'installer.requirements.msg.directories_ok' => 'Wymagane katalogi są zapisywalne',
    'installer.requirements.msg.directories_fail' => 'Następujące katalogi nie są zapisywalne: `{files}`. Zmień uprawnienia.',
    'installer.requirements.msg.language_ok' => 'Konfiguracja języka jest prawidłowa',
    'installer.requirements.msg.language_fail' => '%s',
    'installer.alert.content_not_load' => 'Nie można załadować zawartości.',
    'installer.alert.no_payload' => 'Nie można zainstalować modułu.',
    'installer.alert.no_step' => 'Nie wybrano kroku.',
    'installer.alert.already_installed' => 'Moduł został już zainstalowany.',
    'installer.alert.invalid_package_structure' => 'Błąd wypakowywania pakietu. Sprawdź plik %s i ponów próbę.<br />%s',
    'installer.alert.archive_is_missing' => 'Brakuje pakietu `%s`.<br>Pobierz go z GitHuba lub ze strony <a href="https://dbm.org.pl/" target="_blank">DbM Framework</a>.',
    'installer.alert.installation_ready' => 'Pakiet gotowy do instalacji. Aby zainstalować, kliknij przycisk.',
    'installer.alert.installation_process' => 'Pakiet z trakcie instalacji...',
    'installer.alert.installation_error' => 'Wystąpił bład podczas instalacji!',
    'installer.alert.installation_success' => 'Instalacja pakietu `%s` zakończyła się pomyślnie.',
    'installer.alert.installation_success_cmslite' => 'Instalacja pakietu CMS Lite została pomyślnie zakończona. Możesz zobaczyć <a href="./" target="_blank">stronę główną</a>',
    'installer.alert.database_connection_failed' => 'Połączenie z bazą danych nie powiodło się. Sprawdź konfigurację w pliku .env.',
    'installer.alert.database_not_exists' => 'Baza danych `%s` nie istnieje. Uzupełnij konfigurację bazy danych w pliku .env',
    'installer.alert.database_name_missing' => 'Nazwa bazy danych jest wymagana. Uzupełnij konfigurację bazy danych w pliku .env.',
    'installer.alert.form_fill_fields' => 'Wypełnij pola formularza.',
];
