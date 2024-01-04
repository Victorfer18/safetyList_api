<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Database\Query;

class InspectionController extends BaseController
{
    private $db;
    private $DATA_JWT;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->DATA_JWT = json_decode(DATA_JWT);
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
            ->whereIn('INSP.status_inspection', [1, 2])
            ->where('INSP.client_id', $id_client)
            ->orderBy('INSP.date_estimated', 'ASC');

        $result = $query->get()->getResultArray();

        usort($result, function ($a, $b) {
            if ($a['date_estimated'] == '0000-00-00 00:00:00' && $b['date_estimated'] != '0000-00-00 00:00:00') {
                return 1;
            } elseif ($a['date_estimated'] != '0000-00-00 00:00:00' && $b['date_estimated'] == '0000-00-00 00:00:00') {
                return -1;
            } else {
                return 0;
            }
        });

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

        $id_user = $this->DATA_JWT->user_id;
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
    public function setIsClosedInspectable()
    {
        $rules = [
            'inspection_id' => 'required|numeric|is_natural_no_zero',
            'client_parent' => 'required|numeric|is_natural_no_zero',
            'system_type_id' => 'required|numeric|is_natural_no_zero',
            'sector_area_pavement_id' => 'required|numeric|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }

        $inspection_id = $this->request->getVar('inspection_id');
        $client_parent = $this->request->getVar('client_parent');
        $system_type_id = $this->request->getVar('system_type_id');
        $sector_area_pavement_id = $this->request->getVar('sector_area_pavement_id');

        $fields = [
            'inspection_id' => $inspection_id,
            'client_id' => $client_parent,
            'system_type_id' => $system_type_id,
            'sector_area_pavement_id' => $sector_area_pavement_id,
        ];
        $query = $this->db->table('sys_inspection');
        $getInspectionById = $query->where($fields)->get()->getResultArray();

        if (empty($getInspectionById)) {
            $query
                ->set('is_closed', 1)
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

    public function setIsClosedSectors()
    {
        $rules = [
            'inspection_id' => 'required|numeric|is_natural_no_zero',
            'sector_area_pavement_id' => 'required|numeric|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }

        $inspection_id = $this->request->getVar('inspection_id');
        $sector_area_pavement_id = $this->request->getVar('sector_area_pavement_id');

        $fields = [
            'inspection_id' => $inspection_id,
            'sector_area_pavement_id' => $sector_area_pavement_id,
        ];
        $query = $this->db->table('inspection_sector');
        $getInspectionById = $query->where($fields)->get()->getResultArray();
        if (empty($getInspectionById)) {
            $query
                ->set('is_closed', 1)
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

    public function getInspecTableList()
    {
        $validationRules = [
            'inspection_id' => 'required|numeric|is_natural_no_zero',
            'client_id' => 'required|numeric|is_natural_no_zero',
            'sector_area_pavement_id' => 'required|numeric|is_natural_no_zero',
        ];

        if (!$this->validate($validationRules)) {
            return $this->validationErrorResponse();
        }

        $inspectionId = $this->request->getVar('inspection_id');
        $clientId = intval($this->request->getVar('client_id'));
        $sectorAreaPavementId = intval($this->request->getVar('sector_area_pavement_id'));
        $clientIdsQuery = $this->db->table('client CLI')
            ->select('CLICHL.client_id')
            ->join('client CLICHL', 'CLICHL.client_parent = CLI.client_id')
            ->where('CLI.client_id', $clientId)
            ->get();

        $clientIds = array_column($clientIdsQuery->getResultArray(), 'client_id');

        $systemsQuery = $this->db->table('sys SYS')
            ->select('SYS.system_id, SYS.client_id, CLI.client_level, CLI.client_parent, SYS.situation_id, SYS.system_type_id, TYP.system_type_name, TYP.system_type_icon, GRP.system_group_id, GRP.system_group_name, SAM.sys_app_maintenances_id, SAM.sector_area_pavement_id')
            ->join('client CLI', 'CLI.client_id = SYS.client_id')
            ->join('system_type TYP', 'SYS.system_type_id = TYP.system_type_id')
            ->join('system_group GRP', 'GRP.system_group_id = TYP.system_group_id')
            ->join('sys_app_maintenances SAM', 'SAM.system_type_id = SYS.system_type_id AND SAM.client_id = SYS.client_id')
            ->whereIn('SYS.client_id', $clientIds)
            ->where('SAM.sector_area_pavement_id', $sectorAreaPavementId)
            ->where('SYS.situation_id', 1)
            ->where('TYP.situation_id', 1)
            ->where('TYP.is_safetyList', 1)
            ->get();

        $systems = $systemsQuery->getResultArray();
        $inspectablesQuery = $this->db->table('sys_inspection')
            ->select('system_type_id, client_id, is_closed')
            ->where(['inspection_id' => $inspectionId, 'client_id' => $clientId, 'sector_area_pavement_id' => $sectorAreaPavementId])
            ->get();

        $inspectables = $inspectablesQuery->getResultArray();

        $inspectablesMap = [];
        foreach ($inspectables as $inspectable) {
            $key = $inspectable['system_type_id'] . '_' . $inspectable['client_id'];
            $inspectablesMap[$key] = $inspectable['is_closed'];
        }

        foreach ($systems as &$system) {
            $key = $system['system_type_id'] . '_' . $system['client_parent'];
            $system['is_closed'] = $inspectablesMap[$key] ?? 0;
        }

        $formattedSystems = array_map(function ($item) {
            return [
                "system_id" => intval($item["system_id"]),
                "client_id" => intval($item["client_id"]),
                "client_level" => intval($item["client_level"]),
                "client_parent" => intval($item["client_parent"]),
                "sector_area_pavement_id" => intval($item["sector_area_pavement_id"]),
                "situation_id" => intval($item["situation_id"]),
                "system_type_id" => intval($item["system_type_id"]),
                "system_type_name" => $item["system_type_name"],
                "system_type_icon" => "https://safety2u.com.br/painelhomolog/assets/img/" . $item["system_type_icon"],
                "system_group_id" => intval($item["system_group_id"]),
                "system_group_name" => $item["system_group_name"],
                "is_closed" => intval($item["is_closed"]),
            ];
        }, $systems);
        $closedCount = array_reduce($formattedSystems, function ($acc, $sector) {
            return $acc + ($sector['is_closed'] === 1 ? 1 : 0);
        }, 0);
        $allClosed = ($closedCount === count($formattedSystems));
        $formattedSystems = array_values(array_unique($formattedSystems, SORT_REGULAR));
        return $this->successResponse(INFO_SUCCESS, [
            'allClosed' => empty($formattedSystems) ? false : $allClosed,
            'inspecTables' => $formattedSystems,
        ]);
    }

    public function registerMaintenance()
    {
        $rules = [
            'system_type_id' => 'required|numeric|is_natural_no_zero',
            'maintenance_type_id' => 'required|numeric|is_natural_no_zero',
            'consistency_status' => 'required|in_list[1, 0]',
            'observation' => 'required',
            'client_parent' => 'required|numeric',
            'inspection_id' => 'required|numeric',
            'image' => 'uploaded[image]|mime_in[image,image/jpg,image/jpeg,image/png]',
            'sys_app_maintenances_id' => 'required|numeric|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }

        $system_type_id = $this->request->getVar('system_type_id');
        $maintenance_type_id = $this->request->getVar('maintenance_type_id');
        $user_id = $this->DATA_JWT->user_id;
        $client_parent = $this->request->getVar('client_parent');
        $inspection_id = $this->request->getVar('inspection_id');
        $consistency_status = $this->request->getVar('consistency_status');
        $observation = $this->request->getVar('observation');
        $action = "";
        $image = $this->request->getFile('image');
        $sys_app_maintenances_id = $this->request->getVar('sys_app_maintenances_id');

        $status_maintenance_according = 1;
        $status_maintenance = 0;

        // if (intval($consistency_status) == $status_maintenance) {
        //     if (!$this->validate(['action' => 'required'])) {
        //         return $this->validationErrorResponse();
        //     }
        // }
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
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND . " - " . $client_parent);
        }
        $client_parent = $result->client_id;


        $subquery = $this->db->table('sys');
        $subquery->select('system_id');
        $subquery->where('client_id', $client_parent);
        $subquery->where('system_type_id', $system_type_id);

        $system_id = $subquery->get()->getResultArray();
        if (empty($system_id)) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND . " - " . $maintenance_type_id . " - " . $system_type_id);
        }
        $system_id = $system_id[0]["system_id"];
        $consistency_status = intval($consistency_status);

        switch ($consistency_status) {
            case $status_maintenance_according:
                $typeTableSystem = 'system_maintenance_according';
                $typeTableFille = 'maintenance_file_according';
                $data = [
                    'system_maintenance_according_text' => $observation,
                    'system_maintenance_according_created' => date('Y-m-d H:i:s'),
                    'user_id' => $user_id,
                    'system_id' => $system_id,
                    'maintenance_type_id' => $maintenance_type_id,
                    'inspection_id' => $inspection_id,
                    'sys_app_maintenances_id' => $sys_app_maintenances_id,
                ];
                break;
            case $status_maintenance:
                $typeTableSystem = 'system_maintenance';
                $typeTableFille = 'maintenance_file';
                $data = [
                    'system_maintenance_text' => $observation,
                    'system_maintenance_created' => date('Y-m-d H:i:s'),
                    'system_maintenance_expiration' => date('Y-m-d', strtotime('+30 days')),
                    'user_id' => $user_id,
                    'system_id' => $system_id,
                    'maintenance_type_id' => $maintenance_type_id,
                    'inspection_id' => $inspection_id,
                    'system_maintenance_action' => $action,
                    'sys_app_maintenances_id' => $sys_app_maintenances_id,
                ];
                break;
            default:
                break;
        }
        $uploadFile = uploadFile($image, time() . "/");
        if (!$uploadFile) {
            return $this->errorResponse(ERROR);
        }
        $systemData = $this->db->table($typeTableSystem);
        $systemData->insert($data);
        $system_maintenance_id = $this->db->insertID();
        $dataFile = [
            'system_maintenance_id' => $system_maintenance_id,
            'maintenance_file_path' => $uploadFile,
        ];
        $conditions = [
            'system_maintenance_id' => $system_maintenance_id,
        ];
        if ($consistency_status === $status_maintenance_according) {
            $dataFile['system_maintenance_according_id'] = $dataFile['system_maintenance_id'];
            unset($dataFile['system_maintenance_id']);
            $conditions['system_maintenance_according_id'] = $conditions['system_maintenance_id'];
            unset($conditions['system_maintenance_id']);
        }
        $queryInsertFile = $this->db->table($typeTableFille);
        $existFille = $queryInsertFile->where($conditions)->get()->getResultArray();
        if (empty($existFille)) {
            $queryInsertFile->insert($dataFile);
        } else {
            $queryInsertFile->set($dataFile)->where($conditions)->update();
        }
        return $this->successResponse(INFO_SUCCESS);
    }

    public function getMaintenance()
    {
        $rules = [
            'system_type_id' => 'required|numeric|is_natural_no_zero',
            'client_id' => 'required|numeric|is_natural_no_zero',
            'sector_area_pavement_id' => 'required|numeric|is_natural_no_zero',
            'inspection_id' => 'required|numeric|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse();
        }

        $system_type_id = $this->request->getVar('system_type_id');
        $client_id = $this->request->getVar('client_id');
        $user_id = $this->DATA_JWT->user_id;
        $sector_area_pavement_id = $this->request->getVar('sector_area_pavement_id');
        $inspection_id = $this->request->getVar('inspection_id');

        $query_maintenance_type = $this->db->table('sys_app_maintenances')
            ->where('client_id', $client_id)
            ->where('system_type_id', $system_type_id)
            ->where('sector_area_pavement_id', $sector_area_pavement_id)
            ->where('inspection_id', $inspection_id)
            ->orderBy('maintenance_order', 'ASC')
            ->orderBy('maintenance_type_name', 'ASC')
            ->get();
        $results = $query_maintenance_type->getResultArray();
        $maintenanceTypes = array_map(function ($item) {
            return [
                'id' => intval($item['sys_app_maintenances_id']) ?? 0,
                'maintenance_type_id' => intval($item['maintenance_type_id']) ?? 0,
                'maintenance_type_name' => $item['maintenance_type_name'] ?? "",
                'sector_area_pavement_id' => intval($item['sector_area_pavement_id'] ?? 0),
            ];
        }, $results);
        $system_id = $results[0]['system_id'] ?? 0;
        $query1 = $this->db->table('system_maintenance_according n')
            ->select('n.system_maintenance_according_id as n_maintenance_id, n.user_id as n_user_id, n.system_id as n_system_id, n.maintenance_type_id as n_maintenance_type_id, n.system_maintenance_according_text as system_maintenance_according_text, n.sys_app_maintenances_id as sys_app_maintenances_according_id, 
        n.system_maintenance_according_created as system_maintenance_according_created, "" as system_maintenance_action, "" as system_maintenance_expiration, mt.maintenance_type_name, f.*')
            ->join('maintenance_file_according f', 'n.system_maintenance_according_id = f.system_maintenance_according_id')
            ->join('maintenance_type mt', 'n.maintenance_type_id = mt.maintenance_type_id', 'left')
            ->where('n.user_id', $user_id)
            ->where('n.system_id', $system_id);

        $query2 = $this->db->table('system_maintenance m')
            ->select('m.system_maintenance_id as m_maintenance_id, m.user_id as m_user_id, m.system_id as m_system_id, m.maintenance_type_id as m_maintenance_type_id, m.system_maintenance_text as system_maintenance_text, m.sys_app_maintenances_id as sys_app_maintenances_id, 
        m.system_maintenance_created as system_maintenance_created, m.system_maintenance_action as system_maintenance_action, m.system_maintenance_expiration as system_maintenance_expiration, mt.maintenance_type_name,
         f.*')
            ->join('maintenance_file f', 'm.system_maintenance_id = f.system_maintenance_id')
            ->join('maintenance_type mt', 'm.maintenance_type_id = mt.maintenance_type_id', 'left')
            ->where('m.user_id', $user_id)
            ->where('m.system_id', $system_id);

        $query1->union($query2);
        $results = $query1->get()->getResultArray();
        $results = array_map(
            function ($item) {
                return [
                    'maintenance_id' => intval($item['n_maintenance_id'] ?? $item['m_maintenance_id']),
                    'observation' => $item['system_maintenance_according_text'] ?? $item['system_maintenance_text'],
                    'action' => $item['system_maintenance_action'] ?? "",
                    'date_created' => $item['system_maintenance_according_created'] ?? $item['system_maintenance_created'],
                    'date_expiration' => $item['system_maintenance_expiration'] ?? '',
                    'user_id' => intval($item['n_user_id'] ?? $item['m_user_id']),
                    'system_id' => intval($item['n_system_id'] ?? $item['m_system_id']),
                    'maintenance_type_id' => intval($item['n_maintenance_type_id'] ?? $item['m_maintenance_type_id']),
                    'sys_app_maintenances_id' => intval($item['sys_app_maintenances_id'] ?? $item['sys_app_maintenances_according_id'] ?? 0),
                    'maintenance_type_name' => $item['maintenance_type_name'],
                    'file_id' => intval($item['maintenance_file_id']),
                    'file_url' => fileToURL($item['maintenance_file_path'], "/uploads"),
                ];
            },
            $results
        );
        foreach ($maintenanceTypes as &$maintenanceType) {
            $correspondingAnswer = null;
            foreach ($results as $item) {
                if ($item['sys_app_maintenances_id'] == $maintenanceType['id']) {
                    $correspondingAnswer = [
                        'sys_app_maintenances_id' => intval($item['sys_app_maintenances_id']),
                        'is_according' => empty($item['date_expiration']) ? 0 : 1,
                        'is_closed' => 1,
                        'maintenance_id' => intval($item['maintenance_id']),
                        'observation' => $item['observation'] ?? "",
                        'action' => $item['action'] ?? "",
                        'date_created' => $item['date_created'] ?? "",
                        'user_id' => intval($item['user_id']),
                        'system_id' => intval($item['system_id']),
                        'maintenance_type_id' => intval($item['maintenance_type_id']),
                        'maintenance_type_name' => $item['maintenance_type_name'] ?? "",
                        'file_id' => intval($item['file_id']),
                        'file_url' => $item['file_url'] ?? "",
                    ];
                    break;
                }
            }
            if (!$correspondingAnswer) {
                $correspondingAnswer = [
                    'sys_app_maintenances_id' => intval($maintenanceType['id']),
                    'is_closed' => 0,
                    'is_according' => 0,
                    'maintenance_id' => null,
                    'observation' => null,
                    'action' => null,
                    'date_created' => null,
                    'user_id' => intval($user_id),
                    'system_id' => intval($system_id) ?? null,
                    'maintenance_type_id' => intval($maintenanceType['maintenance_type_id']),
                    'maintenance_type_name' => $maintenanceType['maintenance_type_name'],
                    'file_id' => null,
                    'file_url' => null,
                ];
            }
            $maintenanceType = $correspondingAnswer;
        }
        $closedCount = array_reduce($maintenanceTypes, function ($acc, $sector) {
            return $acc + ($sector['is_closed'] === 1 ? 1 : 0);
        }, 0);
        $allClosed = ($closedCount === count($maintenanceTypes));
        return $this->successResponse(INFO_SUCCESS, [
            'allClosed' => empty($maintenanceTypes) ? false : $allClosed,
            'maintenances' => $maintenanceTypes,
        ]);
    }
    public function getSectorsByIdInspection(int $id_inspection)
    {
        $query = $this->db->table('inspection i')
            ->select([
                'i.inspection_id',
                'sap.sector_area_pavement_id',
                'sp.sector_pavement_id',
                'sa.sector_area_id',
                "CONCAT(sp.sector_pavement_name, ' - ', sa.sector_area_name,
                 (CASE WHEN (sap.sector_area_pavement_section is not null and sap.sector_area_pavement_section <> '') THEN CONCAT(' - ', sap.sector_area_pavement_section)
                  ELSE '' END)) AS fullSectorName",
                'ins.is_closed'
            ])
            ->join('inspection_sector ins', 'i.inspection_id = ins.inspection_id')
            ->join('sector_area_pavement sap', 'ins.sector_area_pavement_id = sap.sector_area_pavement_id AND i.client_id = sap.client_id')
            ->join('sector_pavement sp', 'sap.sector_pavement_id = sp.sector_pavement_id')
            ->join('sector_area sa', 'sap.sector_area_id = sa.sector_area_id')
            ->where('sap.situation_id', 1)
            ->where('i.inspection_id', $id_inspection)
            ->get()->getResultArray();
        $sectors = array_map(function ($item) {
            return [
                'inspection_id' => intval($item['inspection_id']),
                'sector_area_pavement_id' => intval($item['sector_area_pavement_id']),
                'sector_pavement_id' => intval($item['sector_pavement_id']),
                'sector_area_id' => intval($item['sector_area_id']),
                'fullSectorName' => $item['fullSectorName'],
                'is_closed' => intval($item['is_closed']),
            ];
        }, $query);
        $closedCount = array_reduce($sectors, function ($acc, $sector) {
            return $acc + ($sector['is_closed'] === 1 ? 1 : 0);
        }, 0);
        $allClosed = ($closedCount === count($sectors));
        return $this->successResponse(INFO_SUCCESS, [
            'allClosed' => empty($sectors) ? false : $allClosed,
            'sectors' => $sectors,
        ]);
    }
}
