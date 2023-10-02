<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

require APPPATH . 'Helpers/helpers.php';

class User extends BaseController
{
    private function SECRET_KEY(): string
    {
        return formatSecretKey(getenv('encryption.key'));
    }

    public function login()
    {
        $rules = [
            'user_email' => 'required|valid_email',
            'user_password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }

        $userModel = new UserModel();
        $userEntity = $userModel->where([
            'user_email' => $this->request->getVar('user_email'),
            'group_id' => 4,
        ])->first();

        if (!$userEntity) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND);
        }

        if (!password_verify($this->request->getVar('user_password'), $userEntity->getUserPassword())) {
            return $this->errorResponse(ERROR_INVALID_USER_OR_PASSWORD);
        }

        if ($userEntity->getSituationId() === 0) {
            return $this->errorResponse(ERROR_ACCOUNT_INACTIVE);
        }

        $token = generateJWT([$userEntity->getUserId()], self::SECRET_KEY());

        return $this->successResponse(INFO_SUCCESS, $token);
    }
}
