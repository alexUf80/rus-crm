<?php

class NbkiScoreballs extends Core
{
    public function add($score)
    {
        $query = $this->db->placehold("
            INSERT INTO s_nbki_scoreballs 
            SET ?%
        ", $score);

        $this->db->query($query);

        $id = $this->db->insert_id();

        return $id;
    }

    public function get($order_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_nbki_scoreballs 
            Where order_id = ?
            order by id desc 
            limit 1
        ", $order_id);

        $this->db->query($query);

        $result = $this->db->result();

        return $result;
    }
}