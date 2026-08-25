<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * Centralny punkt dostępu do zmiennych środowiskowych aplikacji.
 *
 * Zmienne środowiskowe ładowane przez DotEnv są udostępniane poprzez
 * $_ENV i $_SERVER i powinny być dostępne za pośrednictwem tej klasy.
 *
 * Framework historycznie używał getenv() bezpośrednio w wielu miejscach.
 * Te zastosowania są nadal obsługiwane w celu zapewnienia wstecznej kompatybilności.
 *
 * Strategia migracji:
 *
 * 1. Nowy framework i kod aplikacji powinny używać Environment::get(), Environment::has() lub Environment::bool().
 * 2. Istniejące wywołania getenv() nie powinny być globalnie usuwane. Można je stopniowo migrować w kolejnych wersjach frameworka.
 * 3. DotEnv musi nadal wywoływać putenv(), dopóki starsze użycia getenv() istnieją w frameworku lub aplikacji.
 * 4. AppConfig to warstwa kompatybilności, która powinna delegować dostęp do środowiska do Environment zamiast bezpośredniego odczytu $_ENV, $_SERVER lub getenv().
 * 5. W przyszłej głównej wersji frameworka bezpośrednie użycie getenv() może zostać usunięte po zakończeniu migracji.
 *
 * Stan obecny i nowy kierunek:
 * Environment jest jedynym niskopoziomowym odczytem $_ENV / $_SERVER;
 * getenv() przestajemy stosować w nowym kodzie;
 * AppConfig może pozostać fasadą konfiguracji frameworka, ale nie powinien implementować własnej logiki ENV;
 * DotEnv nadal może wykonywać putenv() dla kompatybilności ze starym kodem, który jeszcze używa getenv();
 * nowe klasy powinny używać Environment::get()/has()/bool().
 *
 * Docelowo środowisko powinno udostępniać ujednoliconą warstwę dostępu do konfiguracji zamiast bezpośredniego korzystania z getenv().
 * Można przenieść DotEnv / zmienić namespace na: Dbm\Core\Environment\DotEnv.
 * Przenieść AppConfig tylko do aplikacji o ile nie będzie potrzebne.
 * Uporządkować, ujednolicić framework do jednego źródła ENV.
 *
 * ==========
 * Nowy kod MUSI używać tej klasy zamiast getenv(), $_ENV lub $_SERVER bezpośrednio.
 *
 * Framework obsługuje obecnie starszy kod za pomocą getenv().
 * W związku z tym DotEnv nadal wypełnia środowisko procesów za pomocą putenv(), synchronizując jednocześnie $_ENV i $_SERVER.
 *
 * @TODO Migracja środowiska
 *
 * Przyszła wersja frameworka:
 * - migracja pozostałych wywołań getenv() do Environment;
 * - migracja klas konfiguracji frameworka do Environment;
 * - zachowanie AppConfig wyłącznie jako fasady konfiguracji specyficznej dla frameworka;
 * - rozważenie usunięcia putenv() po wyeliminowaniu użycia starszej klasy getenv();
 * - rozważenie przeniesienia DotEnv do przestrzeni nazw/pakietu Dbm\Core\Environment.
 *
 * Ta klasa jest celowo niezależna od kluczy konfiguracyjnych specyficznych dla aplikacji. Zapewnia ona jedynie ogólny dostęp do środowiska.
*/

declare(strict_types=1);

namespace Dbm\Environment;

final class Environment
{
    public static function get(
        string $name,
        string $default = ''
    ): string {
        $value = $_ENV[$name]
            ?? $_SERVER[$name]
            ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }

    public static function has(string $name): bool
    {
        return self::get($name, '') !== '';
    }

    public static function bool(
        string $name,
        bool $default = false
    ): bool {
        $value = self::get($name);

        if ($value === '') {
            return $default;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        );
    }
}
