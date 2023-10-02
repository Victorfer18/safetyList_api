<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class InspectionController extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getInspectionsByClientIdAndStatus(int $id_client)
    {

        $query = $this->db->table('inspection AS INSP')
            ->select([
                'INSP.inspection_id',
                'INSP.inspection_name',
                'INSP.client_id',
                'INFO.info_name',
                'INSP.date_estimated',
                'INSP.date_init',
                'INSP.date_end',
                'INSP.date_created',
                'INSP.user_id',
                'USR.user_name',
                'INSP.status_inspection',
                'STTS_INSP.status_inspection_desc',
            ])
            ->join('status_inspection STTS_INSP', 'INSP.status_inspection = STTS_INSP.status_inspection_id', 'inner')
            ->join('info INFO', 'INSP.client_id = INFO.client_id', 'inner')
            ->join('user USR', 'INSP.user_id = USR.user_id', 'left')
            ->where('INSP.client_id', $id_client);

        $result = $query->get()->getResultArray();
        return $this->successResponse(INFO_SUCCESS, $result);
    }
    public function updateInspectionStatusById(int $id_inspection)
    {
        $rules = [
            'user_id' => 'required|numeric',
            'status_inspection' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }

        $id_user = $this->request->getVar('user_id');
        $status = $this->request->getVar('status_inspection');
        $date = date('Y-m-d H:i:s');

        $query = $this->db->table('inspection');
        $getInspectionById = $query->where('inspection_id', $id_inspection)->get()->getResultArray();
        if (empty($getInspectionById)) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND);
        }

        $query->set('user_id', $id_user)
            ->set('status_inspection', $status);

        if ($status === 2) {
            $query->set('date_init', $date);
        }

        if ($status === 3) {
            $query->set('date_end', $date);
        }

        $query->where('inspection_id', $id_inspection)
            ->update();
        return $this->successResponse(INFO_SUCCESS);
    }
}
