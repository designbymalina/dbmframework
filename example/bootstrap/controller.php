<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

use Dbm\Http\Message\Response;

class HelloController
{
    public function index(): Response
    {
        return Response::text('Hello World');
    }
}
