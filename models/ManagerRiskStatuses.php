<?php

class ManagerRiskStatuses extends Core
{
    public function add_record($data)
    {
        $query = $this->db->placehold("
        INSERT INTO __manager_risk_statuses SET ?%
        ", $data);
        $this->db->query($query);
        $id = $this->db->insert_id();
        

        return $id;
    }

    public function get_record($manager_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __manager_risk_statuses
            WHERE manager_id = ?
        ", (int)$manager_id);
        $this->db->query($query);
        
        $result = $this->db->result();
	
        return $result;
    }

    public function update_record($manager_id, $operations)
    {
        $query = $this->db->placehold("
            UPDATE __manager_risk_statuses SET ?% WHERE manager_id = ?
        ", (array)$operations, (int)$manager_id);
        $result = $this->db->query($query);

        return $result;
    }
}
