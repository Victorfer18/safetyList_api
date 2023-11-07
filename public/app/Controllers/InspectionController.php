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

    public function getInspectionsByClientId(int $id_client)
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
    public function updateInspectionStatusById()
    {
        $rules = [
            'inspection_id' => 'required|numeric|is_natural_no_zero',
            'status_inspection' => 'required|numeric|in_list[2,3]',
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }

        $id_user = DATA_JWT->user_id;
        $status = $this->request->getVar('status_inspection');
        $inspection_id = $this->request->getVar('inspection_id');
        $date = date('Y-m-d H:i:s');

        $query = $this->db->table('inspection');
        $getInspectionById = $query->where('inspection_id', $inspection_id)->get()->getResultArray();
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

        $query->where('inspection_id', $inspection_id)
            ->update();
        return $this->successResponse(INFO_SUCCESS);
    }
    public function saveInspectableIsClosed()
    {
        $rules = [
            'inspection_id' => 'required|numeric|is_natural_no_zero',
            'client_id' => 'required|numeric|is_natural_no_zero',
            'system_type_id' => 'required|numeric|is_natural_no_zero',
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

    public function getInspectableList()
    {
        $rules = [
            'inspection_id' => 'required|numeric|is_natural_no_zero',
            'client_id' => 'required|numeric|is_natural_no_zero',
        ];
        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }
        $inspection_id = $this->request->getVar('inspection_id');
        $client_id = $this->request->getVar('client_id');
        $client_id = intval($client_id);
        $query = $this->db->table('client CLI')
            ->select('CLICHL.client_id')
            ->join('client CLICHL', 'CLICHL.client_parent = CLI.client_id')
            ->where('CLI.client_id', $client_id)
            ->get();

        $clientIds = $query->getResultArray();

        $clientIds = array_column($clientIds, 'client_id');
        $query = $this->db->table('sys SYS')
            ->select('SYS.system_id, SYS.client_id, CLI.client_level, CLI.client_parent, SYS.situation_id, SYS.system_type_id, TYP.system_type_name, TYP.system_type_icon, GRP.system_group_id, GRP.system_group_name, SYSP.is_closed, SYSP.inspection_id')
            ->join('client CLI', 'CLI.client_id = SYS.client_id')
            ->join('system_type TYP', 'SYS.system_type_id = TYP.system_type_id')
            ->join('system_group GRP', 'GRP.system_group_id = TYP.system_group_id')
            ->join('sys_inspection SYSP', 'SYSP.system_type_id = SYS.system_type_id and SYSP.client_id = CLI.client_parent')
            ->whereIn('SYS.client_id', $clientIds)
            ->where('SYSP.inspection_id', $inspection_id)
            ->where('SYS.situation_id', 1)
            ->where('TYP.situation_id', 1)
            ->where('TYP.is_safetyList', 1)
            ->get();

        $inspectables = $query->getResultArray();
        $inspectables = array_map(function ($item) {
            return [
                "system_id" => intval($item["system_id"]),
                "client_id" => intval($item["client_id"]),
                "client_level" => intval($item["client_level"]),
                "client_parent" => intval($item["client_parent"]),
                "situation_id" => intval($item["situation_id"]),
                "system_type_id" => intval($item["system_type_id"]),
                "system_type_name" => $item["system_type_name"],
                "system_type_icon" => "https://safety2u.com.br/painelhomolog/assets/img/" . $item["system_type_icon"],
                "system_group_id" => intval($item["system_group_id"]),
                "system_group_name" => $item["system_group_name"],
                "is_closed" => intval($item["is_closed"]),
                "inspection_id" => intval($item["inspection_id"])
            ];
        }, $inspectables);

        return $this->successResponse(INFO_SUCCESS, $inspectables);
    }

    public function registerMaintenance()
    {
        $rules = [
            'system_type_id' => 'required|numeric|is_natural_no_zero',
            'maintenance_type_id' => 'required|numeric|is_natural_no_zero',
            'consistency_status' => 'required|in_list[true, false]',
            'observation' => 'required',
            'client_parent' => 'required|numeric',
            'image' => 'uploaded[image]|mime_in[image,image/jpg,image/jpeg,image/png]'
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }

        $system_type_id = $this->request->getVar('system_type_id');
        $maintenance_type_id = $this->request->getVar('maintenance_type_id');
        $user_id = DATA_JWT->user_id;
        $client_parent = $this->request->getVar('client_parent');
        $consistency_status = $this->request->getVar('consistency_status');
        $observation = $this->request->getVar('observation');
        $action = $this->request->getVar('action');
        $image = $this->request->getFile('image');
        if (!$consistency_status) {
            if (!$this->validate(['action' => 'required'])) {
                return $this->validationErrorResponse();
            }
        }
        $query = $this->db->table('client AS CLI')
            ->select('CLI.client_id, CLI.client_parent, CLI.client_level, ADDR.address_street, ADDR.address_number, ADDR.address_zipcode, ADDR.address_district, ADDR.address_complement, STA.state_id, STA.state_acronym, STA.state_name, CIT.city_id, CIT.city_name, SIT.situation_id, SIT.situation_acronym, SIT.situation_name')
            ->select('(SELECT COUNT(*) FROM client BDG WHERE BDG.client_parent = CLI.client_id AND BDG.client_level = 4) as building_number', false)
            ->join('address AS ADDR', 'ADDR.client_id = CLI.client_id', 'left')
            ->join('situation AS SIT', 'SIT.situation_id = CLI.situation_id', 'left')
            ->join('state AS STA', 'STA.state_id = ADDR.state_id', 'left')
            ->join('city as CIT', 'CIT.city_id = ADDR.city_id', 'left')
            ->where('CLI.client_parent', $client_parent)
            ->where('CLI.client_level', 3)
            ->where('SIT.situation_id', 1)
            ->orderBy('CLI.client_parent, CLI.client_id', 'ASC');

        $result = $query->get()->getRow();
        if (empty($result)) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND);
        }
        $client_parent = $result->client_id;


        $subquery = $this->db->table('sys');
        $subquery->select('system_id');
        $subquery->where('client_id', $client_parent);
        $subquery->where('system_type_id', $system_type_id);

        $system_id = $subquery->get()->getResultArray();
        if (empty($system_id)) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND);
        }
        $system_id = $system_id[0]["system_id"];

        $query = $this->db->table('system_maintenance_according');
        $data = [
            'system_maintenance_according_text' => $observation,
            'system_maintenance_according_created' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'system_id' => $system_id,
            'maintenance_type_id' => $maintenance_type_id
        ];

        if (!$consistency_status) {
            $query = $this->db->table('system_maintenance');
            $data = [
                'system_maintenance_text' => $observation,
                'system_maintenance_created' => date('Y-m-d H:i:s'),
                'system_maintenance_expiration' => date('Y-m-d', strtotime('+30 days')),
                'user_id' => $user_id,
                'system_id' => $system_id,
                'maintenance_type_id' => $maintenance_type_id,
                'system_maintenance_action' => $action ?? ""
            ];
        }

        uploadFile($image, $image->store() . "/");
        $query->insert($data);

        return $this->successResponse(INFO_SUCCESS);
    }

    public function getMaintenanceType()
    {
        $validation = $this->validate([
            'system_type_id' => 'required|numeric|is_natural_no_zero',
            'client_id' => 'required|numeric|is_natural_no_zero',
        ]);

        if ($validation === false) {
            return $this->validationErrorResponse();
        }

        $system_type_id = $this->request->getVar('system_type_id');
        $client_id = $this->request->getVar('client_id');

        $query = $this->db->table('maintenance_type mt')
            ->select('mt.maintenance_type_id, mt.maintenance_type_name, s.qtd_total')
            ->join('sys s', 'mt.system_type_id = s.system_type_id', 'left')
            ->where('mt.situation_id', 1)
            ->where('mt.is_safetyList', 1)
            ->where('mt.system_type_id', $system_type_id)
            ->where('s.client_id', $client_id)
            ->get();

        if (!$query) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND);
        }

        $results = $query->getResult();

        $maintenanceTypes = [];

        foreach ($results as $result) {
            $maintenanceType = [
                'maintenance_type_id' => $result->maintenance_type_id,
                'maintenance_type_name' => $result->maintenance_type_name,
            ];

            if ($result->qtd_total !== null && $result->qtd_total > 0) {
                $modifiedResults = [];

                for ($count = 1; $count <= $result->qtd_total; $count++) {
                    $maintenanceType['maintenance_type_name'] = $count . ' - ' . $result->maintenance_type_name;
                    $modifiedResults[] = $maintenanceType;
                }

                $maintenanceTypes = array_merge($maintenanceTypes, $modifiedResults);
            } else {
                $maintenanceTypes[] = $maintenanceType;
            }
        }

        $faker = \Faker\Factory::create();
        $maintenanceTypes = array_map(function ($item) use ($faker) {
            return [
                'id' => $faker->uuid(),
                'maintenance_type_id' => $item['maintenance_type_id'],
                'maintenance_type_name' => $item['maintenance_type_name'],
            ];
        }, $maintenanceTypes);

        return $this->successResponse(INFO_SUCCESS, $maintenanceTypes);
    }

    public function getMaintenance()
    {
        $validation = $this->validate([
            'system_id' => 'required|numeric|is_natural_no_zero',
            'maintenance_type_id' => 'required|numeric|is_natural_no_zero',
        ]);

        if ($validation === false) {
            return $this->validationErrorResponse();
        }
        $user_id = DATA_JWT->user_id;
        $system_id = $this->request->getVar('system_id');
        $maintenance_type_id = $this->request->getVar('maintenance_type_id');
        $query1 = $this->db->table('system_maintenance_according n')
            ->select('n.system_maintenance_according_id as n_maintenance_id, n.user_id as n_user_id, n.system_id as n_system_id, n.maintenance_type_id as n_maintenance_type_id, n.system_maintenance_according_text as system_maintenance_according_text, 
            n.system_maintenance_according_created as system_maintenance_according_created,  inspection_id as system_maintenance_action,  f.*')
            ->join('maintenance_file f', 'n.system_maintenance_according_id = f.system_maintenance_id')
            ->where('n.user_id', $user_id)
            ->where('n.system_id', $system_id)
            ->where('n.maintenance_type_id', $maintenance_type_id);

        $query2 = $this->db->table('system_maintenance m')
            ->select('m.system_maintenance_id as m_maintenance_id, m.user_id as m_user_id, m.system_id as m_system_id, m.maintenance_type_id as m_maintenance_type_id, m.system_maintenance_text as system_maintenance_text, 
            m.system_maintenance_created as system_maintenance_created, m.system_maintenance_action as system_maintenance_action,
             f.*')
            ->join('maintenance_file f', 'm.system_maintenance_id = f.system_maintenance_id')
            ->where('m.user_id', $user_id)
            ->where('m.system_id', $system_id)
            ->where('m.maintenance_type_id', $maintenance_type_id);

        $query1->union($query2);
        $results = $query1->get()->getResultArray();
        $faker = \Faker\Factory::create();
        $results = array_map(
            function ($item) use ($faker) {
                return [
                    'id' => $faker->uuid(),
                    'maintenance_id' => intval($item['n_maintenance_id'] ?? $item['m_maintenance_id']),
                    'observation' => $item['system_maintenance_according_text'] ?? $item['system_maintenance_text'],
                    'action' => $item['system_maintenance_action'] ?? "",
                    'date_created' => $item['system_maintenance_according_created'] ?? $item['system_maintenance_created'],
                    'user_id' => intval($item['n_user_id'] ?? $item['m_user_id']),
                    'system_id' => intval($item['n_system_id'] ?? $item['m_system_id']),
                    'maintenance_type_id' => intval($item['n_maintenance_type_id'] ?? $item['m_maintenance_type_id']),
                    'file_id' => intval($item['maintenance_file_id']),
                    'file_url' => fileToURL($item['maintenance_file_path']),
                    'is_according' => intval($item['is_according']),
                ];
            },
            $results
        );
        return $this->successResponse(INFO_SUCCESS, $results);
    }
}
