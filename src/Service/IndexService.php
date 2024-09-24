<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Service;

use Dbm\Interfaces\TranslationInterface;

class IndexService
{
    private $translation;

    public function __construct(TranslationInterface $translation)
    {
        $this->translation = $translation;
    }

    public function getMetaIndex(): array
    {
        return [
            'meta.title' => $this->translation->trans('index.title'),
            'meta.description' => $this->translation->trans('index.description'),
            'meta.keywords' => $this->translation->trans('index.keywords'),
        ];
    }
}
