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
use Dbm\Http\Message\Request;
use Psr\Http\Message\ResponseInterface;

class ExampleApiController extends BaseApiController
{
    public function __construct(
        ?DatabaseInterface $database = null
    ) {
        parent::__construct($database);
    }

    /**
     * @routing GET '/api/example' name: api_example_list
     */
    public function list(Request $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $data = json_encode(['params' => $params]);

        return $this->jsonResponse($data);
    }
}
