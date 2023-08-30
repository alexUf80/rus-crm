<?php

class ManagerRiskStatuses extends Core
{
    public function add_record($data)
    {
        $query = $this->db->placehold("
        INSERT INTO __manager_risk_statuses SET ?%
        ", (array)$data);
        // file_put_contents('c:\OSPanel\sas.txt',$query);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function get_record($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __manager_risk_statuses
            WHERE manager_id = ?
        ", (int)$id);
        // file_put_contents('c:\OSPanel\sas.txt',$query);
        $this->db->query($query);
        
        $result = $this->db->result();

        return $result;
    }
}
