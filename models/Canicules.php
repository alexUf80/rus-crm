<?php

class Canicules extends Core
{
        
	public function get_canicule($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __canicules
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_canicules($filter = array())
	{
		$id_filter = '';
        $type_filter = '';
		$user_id_filter = '';
		$order_id_filter = '';
        $from_filter = '';
        $to_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
		
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
		
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type IN (?@)", array_map('strval', (array)$filter['type']));
		
        if (!empty($filter['from']))
            $from_filter = $this->db->placehold("AND from_date <= ?", $filter['from']);
        
        if (!empty($filter['to']))
            $to_filter = $this->db->placehold("AND to_date >= ?", $filter['to']);
        
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __canicules
            WHERE 1
                $id_filter
                $type_filter
                $user_id_filter
                $order_id_filter
                $from_filter
                $to_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_canicules($filter = array())
	{
        $id_filter = '';
        $type_filter = '';
        $user_id_filter = '';
        $order_id_filter = '';
        $from_filter = '';
        $to_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type IN (?@)", array_map('strval', (array)$filter['type']));
		
        if (!empty($filter['from']))
            $from_filter = $this->db->placehold("AND from_date <= ?", $filter['from']);
        
        if (!empty($filter['to']))
            $to_filter = $this->db->placehold("AND to_date >= ?", $filter['to']);
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));

        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
		
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __canicules
            WHERE 1
                $id_filter
                $type_filter
                $user_id_filter
                $order_id_filter
                $from_filter
                $to_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_canicule($communication)
    {
		$query = $this->db->placehold("
            INSERT INTO __canicules SET ?%
        ", (array)$communication);
        $this->db->query($query);

        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_canicule($id, $communication)
    {
		$query = $this->db->placehold("
            UPDATE __canicules SET ?% WHERE id = ?
        ", (array)$communication, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_canicules($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __canicules WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}