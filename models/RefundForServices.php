<?php

class RefundForServices extends Core
{
    public function add($operation)
    {
        $query = $this->db->placehold("
            INSERT INTO s_refund_for_services 
            SET ?%
        ", $operation);

        $this->db->query($query);

        $id = $this->db->insert_id();

        return $id;
    }

    public function get($contract_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_refund_for_services 
            WHERE contract_id = ?
            AND done = 0
            ORDER BY id DESC 
            LIMIT 1
        ", $contract_id);

        $this->db->query($query);

        $result = $this->db->result();

        return $result;
    }

    public function get_done($contract_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_refund_for_services 
            WHERE contract_id = ?
            AND done = 1
            ORDER BY id DESC 
        ", $contract_id);

        $this->db->query($query);

        $result = $this->db->results();

        return $result;
    }

    public function update_by_contract($id, $data)
    {
        $query = $this->db->placehold("
            UPDATE s_refund_for_services  SET ?% WHERE contract_id = ?
        ", (array)$data, (int)$id);
        $this->db->query($query);

        return $id;
    }
}