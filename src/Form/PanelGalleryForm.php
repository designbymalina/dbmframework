<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Form;

class PanelGalleryForm
{
    public function validatePanelGalleryForm(string $title, ?string $photoStatus, ?string $photoMessage): array
    {
        $data = [];

        if (empty($title)) {
            $data['errorTitle'] = "The title field is required!";
        } elseif ((mb_strlen($title) < 3) || (mb_strlen($title) > 65)) {
            $data['errorTitle'] = "The header must contain from 3 to 65 characters!";
        }

        if ($photoStatus == 'danger') {
            $data['errorPhoto'] = $photoMessage;
        }

        return $data;
    }
}
