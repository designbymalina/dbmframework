<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Form;

use App\Service\DbmUploadImageService;

class UserForm
{
    public function validatePasswordForm(object $userData, array $formData): array
    {
        $data = [];

        if (empty($formData['password_old'])) {
            $data['error_password_old'] = 'Aktualne hasło jest wymagane!';
        } elseif (!password_verify($formData['password_old'], $userData->password)) {
            $data['error_password_old'] = 'Podano nieprawidłowe aktualne hasło!';
        }

        if (empty($formData['password'])) {
            $data['error_password'] = 'Hasło jest wymagane!';
        } elseif (!preg_match("/^(?=.*[0-9])(?=.*[A-Z]).{6,30}$/", $formData['password'])) {
            $data['error_password'] = 'Hasło musi składać się z liter, zawierać co najmniej jedną wielką literę i jedną cyfrę oraz mieć od 6 do 30 znaków!';
        }

        if (empty($formData['password_repeat'])) {
            $data['error_password_repeat'] = 'Wymagane jest potwierdzenie hasła!';
        } elseif ($formData['password'] !== $formData['password_repeat']) {
            $data['error_password_repeat'] = 'Hasło i jego potwtórzenie są różne!';
        }

        return $data;
    }

    public function validateProfileForm(array $formData): array
    {
        $errors = [];

        if ($this->doUploadImage()) {
            $errors['error_avatar'] = $this->doUploadImage();
        }

        if (!empty($formData['fullname']) && !preg_match('/^[\pL \'-]*$/u', $formData['fullname'])) {
            $errors['error_fullname'] = 'Proszę wprowadzić poprawnie imię i nazwisko.';
        }

        if (!empty($formData['phone']) && !preg_match('/^(\d{3}\s?\d{3}\s?\d{3}|\+?\d{2}\s?\d{3}\s?\d{3}\s?\d{3})$/', $formData['phone'])) {
            $errors['error_phone'] = 'Proszę wprowadzić poprawny numer telefonu: 9-cyfrowy lub 12-znakowy numer w formacie międzynarodowym.';
        }

        if (!empty($formData['website']) && filter_var($formData['website'], FILTER_VALIDATE_URL)) {
            $errors['error_website'] = 'Proszę wprowadzić poprawny adres strony internetowej.';
        }

        if (!empty($formData['profession']) && !preg_match('/^[\pL \'-]*$/u', $formData['profession'])) {
            $errors['error_profession'] = 'Proszę wprowadzić poprawnie profesje.';
        }

        return $errors;
    }

    /* TO ANALYZE! */
    private function doUploadImage(): ?string
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_FILES['dbm_avatar']) && !empty($_FILES['dbm_avatar']['name'])) {
            $uploadImage = new DbmUploadImageService();
            $uploadImage->setTargetDir('images/avatar/');
            $uploadImage->setAllowedTypes(['image/jpeg', 'image/png']);
            $uploadImage->setMaxFileSize(1048576);
            $uploadImage->setMaxWidth(520);
            $uploadImage->setMaxHeight(520);
            // $uploadImage->setRenameIfExist(false);

            $resultUpload = $uploadImage->uploadImage($_FILES['dbm_avatar']);

            if ($resultUpload === false) {
                return $uploadImage->getErrorsAsString(); // return array to string
            } // else { $resultUpload return string (filename) }
        }

        return null;
    }
}
