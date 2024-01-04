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
        $userID = json_decode(DATA_JWT)->user_id;
        $query = $this->db->table("client AS CLI")
            ->select("CLI.client_id,
            CLI.client_parent,
            CLI.client_created,
            CLITYP.client_type_name,
            CLITYP.client_type_image_path,
            INF.info_name,
            PAR.client_id AS parent_id")
            ->join("info AS INF", "INF.client_id = CLI.client_id")
            ->join("client_type AS CLITYP", "CLITYP.client_type_id = CLI.client_type_id")
            ->join("situation AS SIT", "SIT.situation_id = CLI.situation_id")
            ->join("client AS PAR", "PAR.client_id = CLI.client_parent AND PAR.client_level = 1")
            ->join("organization AS ORG", "ORG.client_id = PAR.client_id")
            ->join("ocupation AS OCU", "OCU.ocupation_id = INF.ocupation_id")
            ->join("user_access UA", "CLI.client_id = UA.client_id")
            ->join("(SELECT * FROM user WHERE user_id = $userID) U", "CLI.client_parent = U.client_id")
            ->where("CLI.client_level = 2")
            ->where("CLI.situation_id = 1")
            ->where("(CASE U.group_id
                    WHEN 3 THEN CLI.client_id = UA.client_id AND UA.user_id = $userID AND UA.situation_id = 1
                    WHEN 2 THEN CLI.client_parent = U.client_id
                END)")
            ->groupBy("CLI.client_id")
            ->orderBy("INF.info_name");

        $result = $query->get()->getResultArray();

        $payload = array_map(function ($item) {
            return [
                'client_id' => $item['client_id'],
                'info_name' => $item['info_name'],
                'client_type_name' => $item['client_type_name'],
                'parent_id' => $item['parent_id'],
                'client_created' => $item['client_created'],
                'image' => fileToURL($item['client_type_image_path'], '/images'),
            ];
        }, $result);

        return $this->successResponse(INFO_SUCCESS, $payload);
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
