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
            ->select('CLI.client_id, CLI.client_level, CLI.client_parent, CLI.client_created, CLITYP.client_type_id, CLITYP.client_type_name, INF.info_name, INF.info_document, INF.info_responsable, INF.info_phone, INF.info_email, OCU.ocupation_id, OCU.ocupation_acronym, OCU.ocupation_name, SIT.situation_id, SIT.situation_acronym, SIT.situation_name, PAR.client_id as parent_id, ORG.organization_name, (CASE WHEN sub1.rowColor1 = \'#ffcbcb\' THEN 1 WHEN sub1.rowColor1 = \'#fffdc0\' THEN 2 ELSE 3 END) AS rowColor')
            ->join('info as INF', 'INF.client_id = CLI.client_id')
            ->join('client_type as CLITYP', 'CLITYP.client_type_id = CLI.client_type_id')
            ->join('situation as SIT', 'SIT.situation_id = CLI.situation_id')
            ->join('client as PAR', 'PAR.client_id = CLI.client_parent AND PAR.client_level = 1', 'left')
            ->join('organization as ORG', 'ORG.client_id = PAR.client_id', 'left')
            ->join('ocupation as OCU', 'OCU.ocupation_id = INF.ocupation_id', 'left')
            ->where('CLI.client_level', 2)
            ->where('CLI.situation_id', 1)
            ->where('CLI.client_parent', $orgId);

        $subquery1 = $this->db->table('document as DOC')
            ->select('CASE WHEN DOC.document_expiration IS NULL THEN \'#caeaca\' WHEN DATEDIFF(DOC.document_expiration, CURDATE()) > DOCTYPE.document_type_days THEN \'#caeaca\' WHEN DATEDIFF(DOC.document_expiration, CURDATE()) > 0 THEN \'#fffdc0\' ELSE \'#ffcbcb\' END AS rowColor1') // Alterado o alias para 'rowColor1'
            ->join('document_type as DOCTYPE', 'DOCTYPE.document_type_id = DOC.document_type_id')
            ->join('client as DOCCLI', 'DOCCLI.client_id = DOC.client_id')
            ->where('DOCCLI.client_id', $this->db->escape('CLI.client_id'), false)
            ->getCompiledSelect();

        $subquery2 = $this->db->table('client as PER')
            ->select('CASE WHEN calculated = 100 THEN \'#caeaca\' WHEN calculated > 70 THEN \'#fffdc0\' ELSE \'#ffcbcb\' END AS rowColor2')
            ->select('(CASE WHEN MAI.calculated < 0 THEN 0 ELSE MAI.calculated END) AS calculated')
            ->join('sys as SYS', 'SYS.client_id = PER.client_id', 'left')
            ->join('system_type as TYP', 'TYP.system_type_id = SYS.system_type_id', 'left')
            ->join('(SELECT 100 - SUM(MAITYP.maintenance_type_number) calculated, TYP.system_type_name FROM system_maintenance MAI INNER JOIN sys SYS ON SYS.system_id = MAI.system_id INNER JOIN system_type TYP ON TYP.system_type_id = SYS.system_type_id INNER JOIN maintenance_type MAITYP ON MAITYP.maintenance_type_id = MAI.maintenance_type_id INNER JOIN client CLI ON CLI.client_id = SYS.client_id INNER JOIN client PAR ON PAR.client_id = CLI.client_parent WHERE PAR.client_id = CLI.client_id AND MAI.system_maintenance_concluded IS NULL GROUP BY TYP.system_type_name) MAI', 'MAI.system_type_name = TYP.system_type_name', 'left')
            ->where('PER.client_parent', $this->db->escape('CLI.client_id'), false)
            ->getCompiledSelect();

        $query->join("($subquery1) sub1", '1=1', 'left')
            ->join("($subquery2) sub2", '1=1', 'left');           

        $result = $query->get()->getResult();

        if (empty($result)) {
            return $this->errorResponse(ERROR_SEARCH_NOT_FOUND);
        }    
        return $this->successResponse('Deu certo',$result);
    }
}
