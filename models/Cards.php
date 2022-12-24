<?php

class Cards extends Core
{
    public function find_duplicates($user_id, $pan, $expdate)
    {
    	$query = $this->db->placehold("
            SELECT *
            FROM __cards
            WHERE user_id != ?
            AND expdate = ?
            AND pan = ?
        ", $user_id, $expdate, $pan);
        $this->db->query($query);
        
        return $this->db->results();
    }
    
    
	public function get_card($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __cards
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_cards($filter = array())
	{
		$id_filter = '';
        $user_id_filter = '';
        $sent_status_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        $sort = 'id DESC';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);
        
        if (isset($filter['sent_status']))
            $sent_status_filter = $this->db->placehold('AND sent_status = ?', (int)$filter['sent_status']);
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
        if (!empty($filter['sort']))
            switch ($filter['sort']):
                case 'id_asc':
                    $sort = 'id ASC';
                break;
                case 'id_desc':
                    $sort = 'id DESC';
                break;
            endswitch;
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __cards
            WHERE 1
                $id_filter
                $user_id_filter
                $sent_status_filter
 	           $keyword_filter
            ORDER BY $sort 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_cards($filter = array())
	{
        $id_filter = '';
        $sent_status_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (isset($filter['sent_status']))
            $sent_status_filter = $this->db->placehold('AND sent_status = ?', (int)$filter['sent_status']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __cards
            WHERE 1
                $id_filter
                $sent_status_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_card($card)
    {
		$query = $this->db->placehold("
            INSERT INTO __cards SET ?%
        ", (array)$card);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_card($id, $card)
    {
		$query = $this->db->placehold("
            UPDATE __cards SET ?% WHERE id = ?
        ", (array)$card, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_card($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __cards WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}