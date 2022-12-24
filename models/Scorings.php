<?php

class Scorings extends Core
{
    public function get_type_scoring($order_id, $type)
    {
		$query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE type = ?
            AND order_id = ?
        ", $type, $order_id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;    	
    }
    

	public function get_overtime_scorings($datetime)
    {
		$query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE status = 'process'
            AND start_date < ?
            ORDER BY id ASC
        ", $datetime);
        $this->db->query($query);
        $results = $this->db->results();
	
        return $results;    	
        
    }

    public function get_new_scoring()
    {
		$query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE status = 'new'
            ORDER BY id ASC
            LIMIT 1
        ");
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;    	
    }
        
    public function get_repeat_scoring()
    {
		$query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE status = 'repeat'
            AND repeat_count = 0
            ORDER BY id ASC
            LIMIT 1
        ");
        $this->db->query($query);
        if ($result = $this->db->result())
            return $result;    	

		$query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE status = 'repeat'
            AND repeat_count = 1
            ORDER BY id ASC
            LIMIT 1
        ");
        $this->db->query($query);
        if ($result = $this->db->result())
            return $result;    	

		$query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE status = 'repeat'
            AND repeat_count > 1
            ORDER BY id ASC
            LIMIT 1
        ");
        $this->db->query($query);
        $result = $this->db->result();
        
        return $result;    	
    }
        
	public function get_scorista_scoring_id($scorista_id)
	{
		$query = $this->db->placehold("
            SELECT id
            FROM __scorings
            WHERE scorista_id = ?
        ", (string)$scorista_id);
        $this->db->query($query);
        $result = $this->db->result('id');
	
        return $result;
    }
    
    public function get_scoring($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }

    public function get_idx_scoring($order_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE order_id = ?
            order by id desc
            limit 1
        ", $order_id);

        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }
    
	public function get_scorings($filter = array())
	{
		$id_filter = '';
 		$user_id_filter = '';
 		$order_id_filter = '';
 		$type_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
        
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type IN (?@)", (array)$filter['type']);
        
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
            FROM __scorings
            WHERE 1
                $id_filter
                $user_id_filter
                $type_filter
                $order_id_filter
                $keyword_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_scorings($filter = array())
	{
        $id_filter = '';
        $user_id_filter = '';
        $order_id_filter = '';
        $type_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
        
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type IN (?@)", (array)$filter['type']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __scorings
            WHERE 1
                $id_filter
                $user_id_filter
                $order_id_filter
                $type_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_scoring($scoring)
    {
		$query = $this->db->placehold("
            INSERT INTO __scorings SET ?%
        ", (array)$scoring);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_scoring($id, $scoring)
    {
		$query = $this->db->placehold("
            UPDATE __scorings SET ?% WHERE id = ?
        ", (array)$scoring, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_scoring($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __scorings WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    
    /** Scoring types **/
    public function get_type($id)
	{
        $where = is_int($id) ? $this->db->placehold("WHERE id = ?", (int)$id) : $this->db->placehold("WHERE name = ?", (string)$id);
        
		$query = $this->db->placehold("
            SELECT * 
            FROM __scoring_types
            $where
        ");
        $this->db->query($query);
        if ($result = $this->db->result())
            $result->params = unserialize($result->params);
	
        return $result;
    }
    
	public function get_types($filter = array())
	{
		$id_filter = '';
        $active_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (isset($filter['active']))
            $active_filter = $this->db->placehold("AND active = ?", (int)$filter['active']);
        
        $query = $this->db->placehold("
            SELECT * 
            FROM __scoring_types
            WHERE 1
                $id_filter
                $active_filter
            ORDER BY position ASC 
        ");
        $this->db->query($query);
        
        $scoring_types = array();
        if ($results = $this->db->results())
        {
            foreach ($results as $result)
            {
                $result->params = unserialize($result->params);
                $scoring_types[$result->name] = $result;
            }
        }
        
        return $scoring_types;
	}
    
	public function count_types($filter = array())
	{
        $id_filter = '';
        $active_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (isset($filter['active']))
            $active_filter = $this->db->placehold("AND active = ?", (int)$filter['active']);
        
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __scoring_types
            WHERE 1
                $id_filter
                $active_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_type($type)
    {
        $type = (array)$type;
        
        if (isset($type['params']))
            $type['params'] = serialize($type['params']);
        
		$query = $this->db->placehold("
            INSERT INTO __scoring_types SET ?%
        ", $type);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_type($id, $type)
    {
        $type = (array)$type;
        
        if (isset($type['params']))
            $type['params'] = serialize($type['params']);
            
		$query = $this->db->placehold("
            UPDATE __scoring_types SET ?% WHERE id = ?
        ", $type, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_type($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __scoring_types WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }


    /** Audit **/
    public function get_audit($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __audits
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        if ($result = $this->db->result())
            $result->types = unserialize($result->types);
	   
        return $result;
    }
    
	public function get_audits($filter = array())
	{
		$id_filter = '';
 		$user_id_filter = '';
 		$order_id_filter = '';
 		$status_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
            
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status = ?", (string)$filter['status']);
        
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
            FROM __audits
            WHERE 1
                $id_filter
                $user_id_filter
                $order_id_filter
                $status_filter
                $keyword_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        $this->db->query($query);
        if ($results = $this->db->results())
        {
            foreach ($results as $result)
                $result->types = unserialize($result->types);
        }
        return $results;
	}
    
	public function count_audits($filter = array())
	{
        $id_filter = '';
        $user_id_filter = '';
        $order_id_filter = '';
        $status_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
            
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status = ?", (string)$filter['status']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __audits
            WHERE 1
                $id_filter
                $user_id_filter
                $order_id_filter
                $status_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_audit($audit)
    {
        $audit = (array)$audit;
        
        if (isset($audit['types']))
            $audit['types'] = serialize($audit['types']);
        
		$query = $this->db->placehold("
            INSERT INTO __audits SET ?%
        ", $audit);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_audit($id, $audit)
    {
        $audit = (array)$audit;
        
        if (isset($audit['types']))
            $audit['types'] = serialize($audit['types']);
        
		$query = $this->db->placehold("
            UPDATE __audits SET ?% WHERE id = ?
        ", $audit, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_audit($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __audits WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

}