<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Controller;

use App\Config\ConstantConfig;
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

        $queryGallery = $this->model->getGalleryPhotos(ConstantConfig::GALLERY_INDEX_ITEM_LIMIT);

        $this->render('gallery/index.phtml', [
            'meta' => $meta,
            'gallery' => $queryGallery,
        ]);
    }

    /* @Route: "/gallery/ajaxLoadData" */
    public function ajaxLoadDataMethod(): void
    {
        $limit = (int) $this->requestData('limit');
        $start = (int) $this->requestData('start');

        if (isset($limit, $start)) {
            $queryGallery = $this->model->getGalleryLoadData($limit, $start);

            if ($queryGallery) {
                echo '<script src="./assets/js/masonry.pkgd.min.js"></script>';

                foreach ($queryGallery as $item) {
                    echo '<div class="col text-center">';
                    echo '<a class="gallery-item" href="images/gallery/photo/' . $item->filename . '">';
                    echo '<img src="images/gallery/thumb/' . $item->filename . '" class="img-fluid" alt="' . $item->title . '">';
                    echo '</a>';
                    echo '</div>';
                }

                // TO DO! Lightbox reload?
                //echo '<script src="./assets/js/jquery.min.js"></script>';
                //echo '<script> $(document).ready(function() { $( "#jsLightbox" ).remove(); }); </script>'; // .lightbox-modal
                //echo '<script src="./assets/vendor/lightbox/script.js"></script>';
                /*echo '<div class="modal fade lightbox-modal" id="lightbox-modal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
                            <div class="modal-content">
                                <button type="button" class="btn-fullscreen-enlarge" aria-label="Enlarge fullscreen">
                                    <svg class="bi"><use href="#enlarge"></use></svg>
                                </button>
                                <button type="button" class="btn-fullscreen-exit d-none" aria-label="Exit fullscreen">
                                    <svg class="bi"><use href="#exit"></use></svg>
                                </button>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                <div class="modal-body">
                                    <div class="lightbox-content">
                                        <!-- JS content here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';*/
            }
        }
    }
}
