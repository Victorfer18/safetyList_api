<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ClientController extends BaseController
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getClientsByIdParent(int $id_parent)
    {
        $query = $this->db->table('client as CLI')
            ->select([
                'CLI.client_id',
                'CLI.client_parent',
                'CLI.client_created',
                'CLITYP.client_type_name',
                'INF.info_name',
                'PAR.client_id as parent_id',
            ])
            ->join('info as INF', 'INF.client_id = CLI.client_id')
            ->join('client_type as CLITYP', 'CLITYP.client_type_id = CLI.client_type_id')
            ->join('situation as SIT', 'SIT.situation_id = CLI.situation_id')
            ->join('client as PAR', 'PAR.client_id = CLI.client_parent AND PAR.client_level = 1', 'left')
            ->join('organization as ORG', 'ORG.client_id = PAR.client_id', 'left')
            ->where('CLI.client_level', 2)
            ->where('CLI.situation_id', 1)
            ->where('CLI.client_parent', $id_parent)
            ->orderBy('INF.info_name', 'ASC')
            ->get();

        return $this->successResponse(INFO_SUCCESS, $query->getResultArray());
    }

    public function getLogosInspectables(int $id_client)
    {
        $query = $this->db->table('organization_logo')
            ->select('organization_logo_path')
            ->where('client_id', $id_client)
            ->where('situation_id', 1)
            ->get()
            ->getResultArray();

        if (empty($query)) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND);
        }

        $logo = $query[0];

        if (!file_exists($logo['organization_logo_path'] ?? "")) {
            return;
        }
        $data = file_get_contents($logo['organization_logo_path']);
        $this->response->setContentType('image/jpeg');
        $this->response->setHeader('Content-Length', strlen($data));
        $this->response->setBody($data);
        return $this->response;
    }
}
