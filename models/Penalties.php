<?php

class Penalties extends Core
{
	private $statuses = array(
        1 => 'На исправление',
        2 => 'Исправлен',
        3 => 'Отменен',
        4 => 'Штраф',
        5 => '',
    );
    
    public function get_statuses()
    {
    	return $this->statuses;
    }
    
    
    public function get_penalty($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __penalties
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_penalties($filter = array())
	{
		$id_filter = '';
		$order_id_filter = '';
        $manager_id_filter = '';
        $date_from_filter = '';
        $date_to_filter = '';
        $status_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        $sort = 'id DESC';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
		
        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id IN (?@)", array_map('intval', (array)$filter['manager_id']));
		
        if (!empty($filter['date_from']))
            $date_from_filter = $this->db->placehold("AND DATE (created) >= ?", $filter['date_from']);
        if (!empty($filter['date_to']))
            $date_to_filter = $this->db->placehold("AND DATE (created) <= ?", $filter['date_to']);
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status IN (?@)", (array)$filter['status']);
        
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
        
        if (!empty($filter['sort']))
        {
            switch ($filter['sort']):
                
                case 'created_asc':
                    $sort = 'created ASC';
                break;
                
                case 'created_desc':
                    $sort = 'created DESC';
                break;
                
            endswitch;
            
        }
        
        $query = $this->db->placehold("
            SELECT * 
            FROM __penalties
            WHERE 1
                $id_filter
                $order_id_filter
                $manager_id_filter
                $date_from_filter
                $date_to_filter
				$status_filter
                $keyword_filter
            ORDER BY $sort
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';        
        return $results;
	}
    
	public function count_penalties($filter = array())
	{
        $id_filter = '';
        $order_id_filter = '';
        $manager_id_filter = '';
        $date_from_filter = '';
        $date_to_filter = '';
        $status_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
		
        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id IN (?@)", array_map('intval', (array)$filter['manager_id']));
		
        if (!empty($filter['date_from']))
            $date_from_filter = $this->db->placehold("AND DATE (created) >= ?", $filter['date_from']);
        if (!empty($filter['date_to']))
            $date_to_filter = $this->db->placehold("AND DATE (created) <= ?", $filter['date_to']);
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status IN (?@)", (array)$filter['status']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __penalties
            WHERE 1
                $id_filter
                $order_id_filter
                $manager_id_filter
                $date_from_filter
                $date_to_filter
                $status_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
	public function sum_penalties($filter = array())
	{
        $id_filter = '';
        $order_id_filter = '';
        $manager_id_filter = '';
        $date_from_filter = '';
        $date_to_filter = '';
        $status_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
		
        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id IN (?@)", array_map('intval', (array)$filter['manager_id']));
		
        if (!empty($filter['date_from']))
            $date_from_filter = $this->db->placehold("AND DATE (created) >= ?", $filter['date_from']);
        if (!empty($filter['date_to']))
            $date_to_filter = $this->db->placehold("AND DATE (created) <= ?", $filter['date_to']);

        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status IN (?@)", (array)$filter['status']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT SUM(cost) AS count
            FROM __penalties
            WHERE 1
                $id_filter
                $order_id_filter
                $manager_id_filter
                $date_from_filter
                $date_to_filter
                $status_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_penalty($penalty)
    {
		$query = $this->db->placehold("
            INSERT INTO __penalties SET ?%
        ", (array)$penalty);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_penalty($id, $penalty)
    {
		$query = $this->db->placehold("
            UPDATE __penalties SET ?% WHERE id = ?
        ", (array)$penalty, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_penalty($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __penalties WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }



	public function get_type($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __penalty_types
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_types($filter = array())
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
            FROM __penalty_types
            WHERE 1
                $id_filter
				$keyword_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_types($filter = array())
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
            FROM __penalty_types
            WHERE 1
                $id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_type($penalty_type)
    {
		$query = $this->db->placehold("
            INSERT INTO __penalty_types SET ?%
        ", (array)$penalty_type);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_type($id, $penalty_type)
    {
		$query = $this->db->placehold("
            UPDATE __penalty_types SET ?% WHERE id = ?
        ", (array)$penalty_type, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_type($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __penalty_types WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}