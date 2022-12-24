<?php

class UsersRisksOperations extends Core
{
    public function get_record($user_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_users_risks_operations
            WHERE user_id = ?
        ", (int)$user_id);
        $this->db->query($query);

        $result = $this->db->result();

        return $result;
    }

    public function get_records()
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_users_risks_operations
        ");

        $this->db->query($query);

        $result = $this->db->results();

        return $result;
    }

    public function add_record($data)
    {
        $query = $this->db->placehold("
            INSERT INTO s_users_risks_operations SET ?%
        ", (array)$data);

        $this->db->query($query);

        $id = $this->db->insert_id();

        return $id;
    }

    public function update_record($user_id, $operations)
    {
        $query = $this->db->placehold("
            UPDATE s_users_risks_operations SET ?% WHERE user_id = ?
        ", (array)$operations, (int)$user_id);

        $result = $this->db->query($query);

        return $result;
    }

    public function delete_record($user_id)
    {
        $query = $this->db->placehold("
            DELETE FROM s_users_risks_operations WHERE user_id = ?
        ", (int)$user_id);
        $result =  $this->db->query($query);

        return $result;
    }
}