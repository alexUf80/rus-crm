<?php

class Statistics extends Core
{
    public function get_operative_report($date_from, $date_to)
    {
        $query = $this->db->placehold("
            SELECT
                COUNT(o.id)
            FROM __orders AS o
            LEFT JOIN __contracts AS c
            ON c.order_id = o.id
            WHERE o.date <= ?
            AND o.date >= ?
        ", $date_from, $date_to);
        $this->db->query($query);
        
        $result = $this->db->result();
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($result);echo '</pre><hr />';
    }
}