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
use App\Service\GalleryService;
use Dbm\Classes\BaseController;
use Dbm\Interfaces\DatabaseInterface;

class GalleryController extends BaseController
{
    private $model;
    private $service;

    public function __construct(DatabaseInterface $database)
    {
        parent::__construct($database);

        $this->model = new GalleryModel($database);
        $this->service = new GalleryService($this->translation);
    }

    /* @Route: "/gallery" */
    public function index()
    {
        $queryGallery = $this->model->getGalleryPhotos(ConstantConfig::GALLERY_INDEX_ITEM_LIMIT);

        $this->render('gallery/index.phtml', [
            'meta' => $this->service->getMetaIndex(),
            'gallery' => $queryGallery,
        ]);
    }

    /* @Route: "/gallery/ajaxLoadData" */
    public function ajaxLoadDataMethod(): void
    {
        $limit = ConstantConfig::GALLERY_INDEX_ITEM_LIMIT;
        $start = (int) $this->requestData('parameters');

        if (isset($limit, $start)) {
            $queryGallery = $this->model->getGalleryLoadData($start, $limit);

            if ($queryGallery) {
                echo '<script src="./assets/js/masonry.pkgd.min.js"></script>';

                foreach ($queryGallery as $item) {
                    echo '<div class="col text-center">';
                    echo '<a class="gallery-item" href="./images/gallery/photo/' . $item->filename . '" data-fancybox="fancyGallery" 
                    data-captiontext="' . $item->title . '" data-captionlink="">';
                    echo '<img src="./images/gallery/thumb/' . $item->filename . '" class="img-fluid" alt="' . $item->title . '">';
                    echo '</a>';
                    echo '</div>';
                }

                echo "<script>
                $('[data-fancybox=\"fancyGallery\"]').fancybox({
                    protect: true,
                    caption: function(instance, item) {
                        var caption = $(this).data('captiontext') || '';
                        var captionLink = $(this).data('captionlink') || '';
                        if (item.type === 'image' && caption.length) {
                            caption = captionLink.length > 8 ? '<a href=\"' + captionLink + '\" target=\"_blank\">' + caption + '</a>' : caption + '<br />';
                        }
                        return caption;
                    }
                });
                </script>";
            }
        }
    }
}
