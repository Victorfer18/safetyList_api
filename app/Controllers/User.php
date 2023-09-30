<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DatabaseWithSSH;

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
        $userModel = new \App\Models\UserModel();
        $userEntity = new \App\Entities\UserEntity();
        $userEntity->setUserEmail($this->request->getVar('user_email'));
        $userEntity->setUserPassword($this->request->getVar('user_password'));
        $conditions = [
            "user_email" => $userEntity->getUserEmail(),
            "group_id" => 4,
        ];
        $getUser = $userModel->where($conditions)->first();

        if (empty($getUser)) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND);
        }

        if ((sha1($userEntity->getUserPassword()) != $getUser["user_password"])) {
            return $this->errorResponse(ERROR_INVALID_USER_OR_PASSWORD);
        }

        if ($getUser["situation_id"] == 0) {
            return $this->errorResponse(ERROR_ACCOUNT_INACTIVE);
        }

        $token = generateJWT([$getUser["user_id"]], self::SECRET_KEY());
        return $this->successResponse(INFO_SUCCESS, $token);
    }
}
