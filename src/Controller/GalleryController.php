<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Model\GalleryModel;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class GalleryController extends BaseController
{
    private $model;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $model = new GalleryModel($database);
        $this->model = $model;
    }

    /* @Route: "/gallery" */
    public function index()
    {
        $translation = $this->translation;

        $meta = [
            'meta.keywords' => $translation->trans('gallery.keywords'),
            'meta.description' => $translation->trans('gallery.description'),
            'meta.title' => $translation->trans('gallery.title'),
        ];

        $queryGallery = $this->model->getGalleryPhotos();

        $this->render('gallery/index.phtml', [
            'meta' => $meta,
            'gallery' => $queryGallery,
        ]);
    }
}
