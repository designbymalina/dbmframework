<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use Dbm\Http\Controller\BaseApiController;
use Psr\Http\Message\ResponseInterface;

class HelloApiController extends BaseApiController
{
    /**
     * JSON API Response
     *
     * This method demonstrates a direct JSON response using the BaseApiController helper.
     * It bypasses the template engine entirely, making it suitable for APIs, lightweight
     * services, webhooks, or quick framework connectivity tests.
     */
    public function index(): ResponseInterface
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => 'Hello World',
        ]);
    }
}
