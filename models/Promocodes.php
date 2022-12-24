<?php

class Promocodes extends Core
{
    public function add($promocode)
    {
        $query = $this->db->placehold("
        INSERT INTO s_promocodes 
        SET ?%
        ", $promocode);

        $this->db->query($query);

        return $this->db->insert_id();
    }

    public function get($id)
    {
        $query = $this->db->placehold("
        SELECT * 
        FROM s_promocodes
        where id = ?
        ", $id);

        $this->db->query($query);
        return $this->db->result();
    }

    public function gets($filter = array())
    {
        $sort = $filter['sort'];

        $query = $this->db->placehold("
        SELECT * 
        FROM s_promocodes
        where 1
        ORDER BY $sort
        ");

        $this->db->query($query);
        return $this->db->results();
    }

    public function update($id)
    {
        $query = $this->db->placehold("
        UPDATE s_promocodes 
        SET ?%
        where id = ?
        ", $id);

        $this->db->query($query);
    }

    public function delete($id)
    {
        $query = $this->db->placehold("
        DELETE FROM s_promocodes
        where id = ?
        ", $id);

        $this->db->query($query);
    }
}