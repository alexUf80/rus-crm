<?php

class UserContactStatuses extends Core
{
    public function add_record($data)
    {
        $query = $this->db->placehold("
            INSERT INTO __user_contact_statuses SET ?%
        ", (array)$data);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function get_records($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __user_contact_statuses
            WHERE contract_id = ?
        ", (int)$id);
        $this->db->query($query);

        $result = $this->db->results();

        return $result;
    }
}
