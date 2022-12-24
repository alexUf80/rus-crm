<?php

class ManagersRoles extends Core
{
    public function gets()
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_managers_roles
        ");

        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function get($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_managers_roles
            where id = ?
        ", $id);

        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }
}