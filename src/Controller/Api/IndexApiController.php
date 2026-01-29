<?php

/**
 * Application: DbM Framework
 * Library: DataTables PHP; designed for the DbM Framework and for use in any PHP application.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller\Api;

use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Http\Controller\BaseApiController;
use Psr\Http\Message\ResponseInterface;

class IndexApiController extends BaseApiController
{
    public function __construct(
        ?DatabaseInterface $database = null
    ) {
        parent::__construct($database);
    }

    /**
     * @routing GET '/api' name: api_index
     */
    public function index(): ResponseInterface
    {
        $data = json_encode([
            'status' => 'OK',
            'name' => 'DBM Framework API',
        ], JSON_UNESCAPED_UNICODE);

        return $this->jsonResponse($data);
    }
}
