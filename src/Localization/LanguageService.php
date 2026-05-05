<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\Localization;

use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Dbm\Infrastructure\Cookie\CookieManager;

class LanguageService
{
    private ExtendedRequestInterface $request;
    private CookieManager $cookie;

    public function __construct(ExtendedRequestInterface $request, CookieManager $cookie)
    {
        $this->request = $request;
        $this->cookie = $cookie;
    }

    /**
     * Get language code or null
     */
    public function detectLanguage(): ?string
    {
        $cookieLang = 'dbmLanguage';
        $default = LanguageHelper::getDefaultLanguage();

        $getLang = $this->request->getQuery('lang');

        if ($getLang !== null) {
            $getLang = strtolower(trim($getLang));

            if ($getLang === 'off') {
                $this->cookie->unsetCookie($cookieLang);
                return $default;
            }

            if (LanguageHelper::isSupported(strtoupper($getLang))) {
                $this->cookie->setCookie($cookieLang, $getLang);
                return $getLang;
            }

            return $default;
        }

        $cookieValue = $this->cookie->getCookie($cookieLang);

        if ($cookieValue && LanguageHelper::isSupported(strtoupper($cookieValue))) {
            return $cookieValue;
        }

        return $default;
    }
}
