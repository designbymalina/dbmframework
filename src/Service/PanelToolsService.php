<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Service;

class PanelToolsService
{
    public function toolsPath(?string $type): string
    {
        switch ($type) {
            case 'mailer':
                $filePath = BASE_DIRECTORY . 'var' . DS . 'log' . DS . 'mailer' . DS;
                break;
            case 'logger':
                $filePath = BASE_DIRECTORY . 'var' . DS . 'log' . DS . 'logger' . DS;
                break;
            default:
                $filePath = BASE_DIRECTORY . 'var' . DS . 'log'. DS;
        }

        return $filePath;
    }

    public function getTitleAndLink(?string $type): array
    {
        $title = 'Error Log';
        $link = '?';

        if (!empty($type)) {
            $link = '?type=' . $type . '&';

            if ($type == 'mailer') {
                $title = 'Mailing Error Log';
            } else if ($type == 'logger') {
                $title = 'Logger Error Log';
            }
        }

        return [$title, $link];
    }
}
