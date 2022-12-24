<?php

class Collections extends Core
{
	public function get_collection($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __collections
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_collections($filter = array())
	{
		$id_filter = '';
        $status_filter = '';
        $keyword_filter = '';
        $date_from = '';
        $date_to = '';
        $limit = 100000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND collection_status IN (?@)", array_map('intval', (array)$filter['status']));
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
        if (!empty($filter['date_from']))
            $date_from = $this->db->placehold("AND DATE(created) >= ?", date('Y-m-d', strtotime($filter['date_from'])));
        if (!empty($filter['date_to']))
            $date_to = $this->db->placehold("AND DATE(created) <= ?", date('Y-m-d', strtotime($filter['date_to'])));

		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __collections
            WHERE 1
                $id_filter
                $date_from
                $date_to
				$status_filter
				$keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';        
        return $results;
	}
    
	public function count_collections($filter = array())
	{
        $id_filter = '';
        $date_from = '';
        $date_to = '';
        $status_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND collection_status IN (?@)", array_map('intval', (array)$filter['status']));
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
        if (!empty($filter['date_from']))
            $date_from = $this->db->placehold("AND DATE(created) >= ?", date('Y-m-d', strtotime($filter['date_from'])));
        if (!empty($filter['date_to']))
            $date_to = $this->db->placehold("AND DATE(created) <= ?", date('Y-m-d', strtotime($filter['date_to'])));
        
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __collections
            WHERE 1
                $id_filter
                $date_from
                $date_to
                $status_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_collection($collection)
    {
		$query = $this->db->placehold("
            INSERT INTO __collections SET ?%
        ", (array)$collection);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_collection($id, $collection)
    {
		$query = $this->db->placehold("
            UPDATE __collections SET ?% WHERE id = ?
        ", (array)$collection, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_collection($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __collections WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }



	public function get_last_moving($contract_id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __collector_movings
            WHERE contract_id = ?
            ORDER BY id DESC
            LIMIT 1
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_moving($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __collector_movings
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_movings($filter = array())
	{
		$id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __collector_movings
            WHERE 1
                $id_filter
				$keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_movings($filter = array())
	{
        $id_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __collector_movings
            WHERE 1
                $id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_moving($collector_moving)
    {
		$query = $this->db->placehold("
            INSERT INTO __collector_movings SET ?%
        ", (array)$collector_moving);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_moving($id, $collector_moving)
    {
		$query = $this->db->placehold("
            UPDATE __collector_movings SET ?% WHERE id = ?
        ", (array)$collector_moving, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_moving($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __collector_movings WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}