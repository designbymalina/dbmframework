<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Form;

class PanelBlogForm
{
    public function validateArticleForm(string $keywords, string $description, string $title, string $header, string $content, string $section, ?string $user): array
    {
        $data = [];

        if (empty($keywords)) {
            $data['errorKeywords'] = "The keywords field is required!";
        }

        if (empty($description)) {
            $data['errorDescription'] = "The description field is required!";
        }

        if (empty($title)) {
            $data['errorTitle'] = "The title field is required!";
        } elseif ((mb_strlen($title) < 3) || (mb_strlen($title) > 65)) {
            $data['errorTitle'] = "The header must contain from 3 to 65 characters!";
        }

        if (empty($header)) {
            $data['errorHeader'] = "The header field is required!";
        } elseif ((mb_strlen($header) < 10) || (mb_strlen($header) > 120)) {
            $data['errorHeader'] = "The header must contain from 10 to 120 characters!";
        }

        if (empty($content)) {
            $data['errorContent'] = "The content field is required!";
        } elseif (mb_strlen($content) < 1000) {
            $data['errorContent'] = "The content must contain minimum 1000 characters!";
        }

        if (empty($section)) {
            $data['errorSection'] = "The section field is required!";
        }

        if (empty($user)) {
            $data['errorUser'] = "The user field is required!";
        }

        return $data;
    }

    public function validateFormBlogSection(array $formData): array
    {
        $data = [];

        if (empty($formData['name'])) {
            $data['errorName'] = "The name field is required!";
        } elseif ((mb_strlen($formData['name']) < 3) || (mb_strlen($formData['name']) > 100)) {
            $data['errorName'] = "The header must contain from 3 to 100 characters!";
        }

        if (empty($formData['keywords'])) {
            $data['errorKeywords'] = "The keywords field is required!";
        }

        if (empty($formData['description'])) {
            $data['errorDescription'] = "The description field is required!";
        }

        return $data;
    }
}
