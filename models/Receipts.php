<?php

class Receipts extends Core
{
    public function add_receipt($data)
    {
        $query = $this->db->placehold("
            INSERT INTO __receipts SET ?%
        ", (array)$data);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function get_receipts($user_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __receipts
            WHERE user_id = ?
        ", (int)$user_id);
        $this->db->query($query);
        $result = $this->db->results();

        return $result;
    }
}