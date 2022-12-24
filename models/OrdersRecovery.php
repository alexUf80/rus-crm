<?php

class OrdersRecovery extends Core
{
    public function add($order)
    {
        $query = $this->db->placehold("
            INSERT INTO s_orders_recovery 
            SET ?%
        ", $order);

        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function gets()
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM s_orders_recovery 
        ");

        $this->db->query($query);

        $results = $this->db->results();

        return $results;
    }
}