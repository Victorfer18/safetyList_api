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
    public function getActiveClients()
    {
        $orgId = $this->request->getVar('org_id');
        $query = $this->db->table('client as CLI')
            ->select('CLI.client_id, CLI.client_level, CLI.client_parent, CLI.client_created, CLITYP.client_type_id, CLITYP.client_type_name, INF.info_name, INF.info_document, INF.info_responsable, INF.info_phone, INF.info_email, OCU.ocupation_id, OCU.ocupation_acronym, OCU.ocupation_name, SIT.situation_id, SIT.situation_acronym, SIT.situation_name, PAR.client_id as parent_id, ORG.organization_name')
            ->join('info as INF', 'INF.client_id = CLI.client_id')
            ->join('client_type as CLITYP', 'CLITYP.client_type_id = CLI.client_type_id')
            ->join('situation as SIT', 'SIT.situation_id = CLI.situation_id')
            ->join('client as PAR', 'PAR.client_id = CLI.client_parent AND PAR.client_level = 1', 'left')
            ->join('organization as ORG', 'ORG.client_id = PAR.client_id', 'left')
            ->join('ocupation as OCU', 'OCU.ocupation_id = INF.ocupation_id', 'left')
            ->where('CLI.client_level', 2)
            ->where('CLI.situation_id', 1)
            ->where('CLI.client_parent', $orgId);

        $result = $query->get()->getResult();

        if (empty($result)) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND);
        }
        return $this->successResponse('Deu certo', $result);
    }
}