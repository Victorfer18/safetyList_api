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
    public function saveInspectableIsClosed()
    {
        $rules = [
            'inspection_id' => 'required|numeric',
            'client_id' => 'required|numeric',
            'system_type_id' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }

        $inspection_id = $this->request->getVar('inspection_id');
        $client_id = $this->request->getVar('client_id');
        $system_type_id = $this->request->getVar('system_type_id');

        $fields = [
            'inspection_id' => $inspection_id,
            'client_id' => $client_id,
            'system_type_id' => $system_type_id,
        ];
        $query = $this->db->table('sys_inspection');
        $getInspectionById = $query->where($fields)->get()->getResultArray();
        if (empty($getInspectionById)) {
            $query
                ->set('is_closed', 0)
                ->insert($fields);
            return $this->successResponse(INFO_SUCCESS);
        }
        $query
            ->set('is_closed', 1)
            ->set($fields)
            ->where($fields)
            ->update();
        return $this->successResponse(INFO_SUCCESS);
    }

    public function getInspectableList(int $client_id)
    {
        $client_id = intval($client_id);
        $query = $this->db->table('client CLI')
            ->select('CLICHL.client_id')
            ->join('client CLICHL', 'CLICHL.client_parent = CLI.client_id')
            ->where('CLI.client_id', $client_id)
            ->get();

        $clientIds = $query->getResultArray();
        $clientIds = array_column($clientIds, 'client_id');

        $query = $this->db->table('sys SYS')
            ->select('SYS.system_id, SYS.client_id, CLI.client_level, CLI.client_parent, SYS.situation_id, SYS.system_type_id, TYP.system_type_name, TYP.system_type_icon, GRP.system_group_id, GRP.system_group_name, SYSP.is_closed')
            ->join('client CLI', 'CLI.client_id = SYS.client_id')
            ->join('system_type TYP', 'SYS.system_type_id = TYP.system_type_id')
            ->join('system_group GRP', 'GRP.system_group_id = TYP.system_group_id')
            ->join('sys_inspection SYSP', 'SYSP.system_type_id = SYS.system_type_id and SYSP.client_id = CLI.client_parent')
            ->whereIn('SYS.client_id', $clientIds)
            ->where('SYS.situation_id', 1)
            ->where('TYP.situation_id', 1)
            ->where('TYP.is_safetyList', 1)
            ->where('SYSP.is_closed', 1)
            ->get();

        $inspectables = $query->getResultArray();

        $query = $this->db->table('sys_inspection')
            ->select()
            ->where('is_closed', 1)
            ->get();
        return $this->successResponse(INFO_SUCCESS, $inspectables);
    }
}
