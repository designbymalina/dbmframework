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

class GalleryService
{
    private $translation;

    public function __construct(TranslationInterface $translation)
    {
        $this->translation = $translation;
    }

    public function getMetaIndex(): array
    {
        return [
            'meta.keywords' => $this->translation->trans('gallery.keywords'),
            'meta.description' => $this->translation->trans('gallery.description'),
            'meta.title' => $this->translation->trans('gallery.title'),
        ];
    }
}
