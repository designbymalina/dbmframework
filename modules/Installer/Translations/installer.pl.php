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
    'installer.header.subtitle' => 'DbM Framework / Asystent instalacji Platformy DbM CMS',
    'installer.content.title' => 'Asystent instalacji',
    'installer.progressbar.installation' => 'Postęp instalacji',
    'installer.progressbar.not_started' => 'Pasek postępu nie jest dołączony!',
    'installer.button.next_step' => 'Dalej',
    'installer.button.home_page' => 'Przejdź do strony głównej',
    'installer.button.add_modules' => 'Rozszerz system o moduły',
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
    ', // not used
    'installer.step.cmslite.title' => 'Instalowanie CMS Lite',
    'installer.step.cmslite.content' => '
        <p>W tym kroku zostanie zainstalowany i skonfigurowany moduł <strong>CMS Lite</strong>.</p>
        <p>CMS Lite zapewnia lekką i elastyczną warstwę zarządzania treścią, która umożliwia:</p>
        <ul>
            <li>Tworzenie i zarządzanie stronami</li>
            <li>Kontrolowanie strony głównej i struktury witryny</li>
            <li>Rozszerzanie funkcjonalności o dodatkowe moduły CMS</li>
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
        <p>System skonfiguruje podstawową strukturę potrzebną do:</p>
        <ul>
            <li>Obsługi kont użytkowników</li>
            <li>Mechanizmów logowania i wylogowywania</li>
            <li>Zarządzania sesjami i bezpieczeństwem</li>
        </ul>
        <p>System uwierzytelniania umożliwia dostęp do panelu administracyjnego oraz chronionych części aplikacji.</p>
        <p>W przyszłości funkcje uwierzytelniania można łatwo rozszerzyć o dodatkowe moduły.</p>
    ',
    'installer.step.admin.title' => 'Utwórz panel administracyjny',
    'installer.step.admin.content' => '
        <p>Ten krok instaluje i konfiguruje panel administracyjny.</p>
        <p>Panel administracyjny umożliwia:</p>
        <ul>
            <li>Zarządzanie zawartością stron</li>
            <li>Kontrolowanie użytkowników i uprawnień</li>
            <li>Obsługę modułów i pluginów</li>
        </ul>
        <p>Podczas instalacji system automatycznie utworzy konta startowe:</p>
        <ul>
            <li><strong>Admin</strong> (login lub e-mail) - hasło: <strong>Admin123</strong></li>
            <li><strong>Lucy</strong> - hasło: <strong>Test123</strong></li>
            <li><strong>John</strong> - hasło: <strong>Test123</strong></li>
        </ul>
        <p>Po instalacji będziesz mógł zalogować się na konto administratora i zarządzać witryną za pomocą przyjaznego interfejsu użytkownika.</p>
        <p><strong>Zalecenie bezpieczeństwa:</strong> po pierwszym logowaniu zmień domyślne hasła do kont.</p>
    ',
    'installer.step.finish.title' => 'Gratulacje!',
    'installer.step.finish.content' => '
        <p>Instalacja <strong>DbM CMS Platform</strong> została zakończona.</p>
        <p>System jest już gotowy do działania z modułem strony startowej. Jeśli w katalogu <strong>packages</strong> znajdują się także paczki modułów <strong>Uwierzytelniania</strong> i <strong>Panelu administracyjnego</strong>, instalator może je automatycznie wykryć i dodać do systemu.</p>
        <p>Pełną wygodę pracy z systemem zapewnia zestaw trzech podstawowych modułów:</p>
        <ul>
            <li>Strona startowa</li>
            <li>Uwierzytelnianie</li>
            <li>Panel administracyjny</li>
        </ul>
        <p>Razem tworzą kompletną konfigurację DbM CMS, umożliwiając wygodne zarządzanie treścią, użytkownikami oraz instalowanie kolejnych modułów z poziomu panelu.</p>
        <p><em>DbM CMS został zaprojektowany w sposób modułowy - możesz rozpocząć od podstaw i rozwijać system wraz ze wzrostem swojego projektu.</em></p>
        <p>Jeśli posiadasz dodatkowe moduły: `authentication.zip` i `admin.zip`, skopiuj ich archiwa do katalogu <strong>packages</strong>, a następnie w kolejnym kroku wybierz opcję <strong>dodaj moduły</strong>. W przeciwnym razie możesz przejść do strony głównej.</p>
        <p>Jeśli właśnie zainstalowałeś dodatkowe moduły, przejdź dalej, aby wrócić do strony głównej.</p>
        <p>Ze względów bezpieczeństwa upewnij się, że instalator nie jest już dostępny.</p>
        <p>Dziękujemy za korzystanie z DbM CMS.</p>
    ',
    'installer.requirements.msg.core_requirements' => 'Niezbędne wymagania systemowe',
    'installer.requirements.msg.cms_requirements' => 'Niezbędne wymagania dla CMS Lite',
    'installer.requirements.msg.admin_requirements' => 'Wymagania instalacji uwierzytelniania i panelu administracyjnego',
    'installer.requirements.msg.php_ok' => 'Wersja PHP ≥ %s jest zgodna z wymaganiami',
    'installer.requirements.msg.php_fail' => 'Wersja PHP musi być ≥ %s',
    'installer.requirements.msg.directories_ok' => 'Wymagane katalogi są zapisywalne',
    'installer.requirements.msg.directories_fail' => 'Następujące katalogi nie są zapisywalne: `{files}`. Zmień uprawnienia.',
    'installer.requirements.msg.extension_ok' => 'Rozszerzenie `%s` jest załadowane',
    'installer.requirements.msg.extension_fail' => 'Brakujące rozszerzenia `%s`',
    'installer.database.msg.host_missing' => 'Nazwa hosta jest wymagana. Uzupełnij konfigurację bazy danych w pliku .env.',
    'installer.database.msg.name_missing' => 'Nazwa bazy danych jest wymagana.',
    'installer.database.msg.user_missing' => 'Nazwa użytkownika jest wymagana.',
    'installer.database.msg.connection_failed' => 'Połączenie z bazą danych nie powiodło się. Sprawdź konfigurację w pliku .env.',
    'installer.database.msg.not_exists' => 'Baza danych nie istnieje. Uzupełnij konfigurację bazy danych w pliku .env',
    'installer.database.msg.table_exists' => 'W bazie danych istnieją już tabele modułu. Przed instalacją baza musi zostać wyczyszczona.',
    'installer.database.msg.table_not_exists' => 'W bazie danych brakuje tabel modułu, które powinny być zainstalowane w module uwierzytelniania.',
    'installer.alert.already_installed' => 'Moduł został już zainstalowany.',
    'installer.alert.invalid_package_structure' => 'Błąd wypakowywania pakietu. Sprawdź plik %s i ponów próbę.<br />%s',
    'installer.alert.archive_is_missing' => 'Brakuje pakietu `%s`.<br>Pobierz go z GitHuba lub ze strony <a href="https://dbm.org.pl/" target="_blank">DbM Framework</a>.',
    'installer.alert.module_verification_failed' => 'Weryfikacja modułu nie powiodła się. Sprawdź moduł, wyczyść pamięć podręczną i spróbuj ponownie.',
    'installer.alert.installation_error' => 'Wystąpił bład podczas instalacji!', // not used
    'installer.alert.installation_process' => 'Pakiet z trakcie instalacji... przygotuj archiwum lub usuń pozostałości, jeśli instalujesz ponownie!',
    'installer.alert.installation_ready' => 'Instalacja byłą już wykonywana... wyczyść cache i cookies przeglądarki jeśli chcesz ponowić proces instalacji!',
    'installer.alert.installation_success' => 'Instalacja zakończyła się pomyślnie. Możesz teraz dodać opcjonalne rozszerzenia lub przejść do strony głównej.',
    'installer.alert.installation_completed' => 'Instalacja ukończona.', // not used
];
