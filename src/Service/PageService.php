<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Service;

use App\Model\PageModel;

class PageService
{
    private $model;

    public function __construct(PageModel $model)
    {
        $this->model = $model;
    }

    public function getMetaPage(): array
    {
        return [
            'meta.title' => $this->model->Title(),
            'meta.description' => $this->model->Description(),
            'meta.keywords' => $this->model->Keywords(),
        ];
    }
}
