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
 * Examples of usage in web.php
 * // Account routes (permission: 'authenticated' optional 'access_to_account')
 * $router->guard('authenticated', function () use ($router) {
 *     $router->get('/account', [AccountController::class, 'index'], 'account');
 *     $router->get('/account/profile', [AccountController::class, 'accountProfile'], 'account_profile');
 * });
 * // Admin routes (permission: 'access_to_admin_panel' with prefix '/admin')
 * $router->guard('access_to_admin_panel', function() use ($router) {
 *     $router->get('/dashboard', [PanelController::class, 'index'], 'admin_dashboard');
 *     $router->get('/settings', [PanelController::class, 'settings'], 'admin_settings');
 * }, '/admin');
 */

declare(strict_types=1);

namespace Dbm\Security;

use Dbm\Exceptions\UnauthorizedWebException;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Security\Contracts\AccessControlInterface;

final class AccessGuard
{
    public function __construct(
        private SessionManager $session,
        private AccessControlInterface $access
    ) {}

    public function checkPermission(string $permission): void
    {
        $userId = (int) $this->session->getSession(getenv('APP_SESSION_KEY'));

        if ($userId <= 0) {
            throw new UnauthorizedWebException();
        }

        if ($permission === 'authenticated') {
            return;
        }

        if (!$this->access->userCan($userId, $permission)) {
            throw new UnauthorizedWebException();
        }
    }
}
