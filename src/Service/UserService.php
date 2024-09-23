<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 * Module: DbMPayments
 */

declare(strict_types=1);

namespace App\Service;

use App\Form\UserForm;
use App\Model\UserModel;

class UserService
{
    private $model;
    private $form;

    public function __construct(UserModel $model)
    {
        $this->model = $model;
        $this->form = new UserForm();
    }

    public function isPostRequest()
    {
        return strtolower($_SERVER['REQUEST_METHOD']) === 'post';
    }

    public function prepareProfileFormData(object $that, object $userAccount): array
    {
        return [
            'fullname' => $that->requestData('dbm_fullname') ?? $userAccount->fullname,
            'phone' => $that->requestData('dbm_phone') ?? $userAccount->phone,
            'website' => $that->requestData('dbm_website') ?? $userAccount->website,
            'profession' => $that->requestData('dbm_profession') ?? $userAccount->profession,
            'business' => $that->requestData('dbm_business') ?? $userAccount->business,
            'address' => $that->requestData('dbm_address') ?? $userAccount->address,
            'biography' => $that->requestData('dbm_biography') ?? $userAccount->biography,
            'avatar' => $that->requestData('dbm_avatar') ?? $userAccount->avatar,
            'login' => $userAccount->login,
            'email' => $userAccount->email,
        ];
    }

    public function doValidateProfile(array $dataForm): array
    {
        return $this->form->validateProfileForm($dataForm);
    }

    public function doUpdateUserProfile(int $id, array $dataForm, object $userAccount): bool
    {
        $sqlUpdateDetails = [
            'id' => $id,
            'fullname' => $dataForm['fullname'],
            'phone' => $dataForm['phone'],
            'website' => $dataForm['website'],
            'profession' => $dataForm['profession'],
            'business' => $dataForm['business'],
            'address' => $dataForm['address'],
            'biography' => $dataForm['biography'],
        ];

        if (!empty($dataForm['avatar'])) {
            $sqlUpdateDetails['avatar'] = $dataForm['avatar'];
        }

        $updateResult = $this->model->updateUserDetails($sqlUpdateDetails);

        if ($updateResult && !empty($dataForm['avatar']) && $dataForm['avatar'] != $userAccount->avatar) {
            $this->deleteFile($userAccount->avatar);
        }

        return $updateResult;
    }

    public function preparePasswordFormData(object $that): array
    {
        return [
            'password' => $that->requestData('dbm_password'),
            'password_old' => $that->requestData('dbm_password_old'),
            'password_repeat' => $that->requestData('dbm_password_repeat'),
        ];
    }

    public function doValidatePassword(int $id, array $dataForm): array
    {
        $user = $this->model->getUser($id);

        return $this->form->validatePasswordForm($user, $dataForm);
    }

    public function doUpdatePassword(int $id, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sqlUpdate = [':password' => $hashedPassword, ':id' => $id];

        return $this->model->updatePassword($sqlUpdate);
    }

    private function deleteFile(?string $file): void
    {
        $filePath = 'images/avatar/' . $file;

        if (is_file($filePath) && file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
