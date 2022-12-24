<?php

error_reporting(-1);
ini_set('display_errors', 'On');

class Integrations extends Core
{
    public function get_integrations()
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __integrations
        ");

        $this->db->query($query);
        $result = $this->db->results();

        return $result;
    }

    public function add_integration($integration)
    {
        $query = $this->db->placehold("
            INSERT INTO __integrations SET ?%
        ", (array)$integration);

        $this->db->query($query);

        $id = $this->db->insert_id();

        return $id;
    }

    public function delete_integration($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __integrations WHERE id = ?
        ", (int)$id);

        $this->db->query($query);
    }

    public function get_integration($id)
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __integrations
            WHERE id = ?
        ", (int)$id);

        $this->db->query($query);

        $result = $this->db->result();

        return $result;
    }

    public function update_integration($id, $integration)
    {
        $query = $this->db->placehold("
            UPDATE __integrations SET ?% WHERE id = ?
        ", (array)$integration, (int)$id);
        $this->db->query($query);

        return $id;
    }
}