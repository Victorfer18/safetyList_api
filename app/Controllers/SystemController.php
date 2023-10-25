<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class SystemController extends BaseController
{
    public function validateJwt()
    {
        return $this->successResponse(INFO_SUCCESS, DATA_JWT);
    }
}
