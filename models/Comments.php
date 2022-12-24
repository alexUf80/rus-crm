<?php

class Comments extends Core
{
	public function get_comment($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __comments
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_comments($filter = array())
	{
		$id_filter = '';
		$order_id_filter = '';
		$user_id_filter = '';
		$official_filter = '';
		$manager_id_filter = '';
		$not_sent_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        $sort = 'id DESC';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));
        
        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id = ?", (int)$filter['manager_id']);
        
        if (!empty($filter['not_sent']))
            $not_sent_filter = $this->db->placehold("AND (status = 0 OR status = 1)");
                
        if (!empty($filter['official']))
            $official_filter = $this->db->placehold("AND official = 1");
        
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
            switch($filter['sort']):
                
                case 'id_asc':
                    $sort = 'id ASC';
                break;
                
            endswitch;
        }
        
        $query = $this->db->placehold("
            SELECT * 
            FROM __comments
            WHERE 1
                $id_filter
				$keyword_filter
        		$order_id_filter
        		$user_id_filter 
        		$manager_id_filter 
                $not_sent_filter
                $official_filter 
            ORDER BY $sort 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
        return $results;
	}
    
	public function count_comments($filter = array())
	{
        $id_filter = '';
        $keyword_filter = '';
		$order_id_filter = '';
		$user_id_filter = '';
		$manager_id_filter = '';
        $not_sent_filter = '';
        $official_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);
        
        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id = ?", (int)$filter['order_id']);
        
        if (!empty($filter['manager_id']))
            $manager_id_filter = $this->db->placehold("AND manager_id = ?", (int)$filter['manager_id']);
        
        if (!empty($filter['not_sent']))
            $not_sent_filter = $this->db->placehold("AND (status = 0 OR status = 1)");
        
        if (isset($filter['official']))
            $official_filter = $this->db->placehold("AND official = ?", (int)$filter['official']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __comments
            WHERE 1
                $id_filter
                $keyword_filter
          		$order_id_filter
        		$user_id_filter 
        		$manager_id_filter 
                $not_sent_filter
                $official_filter 
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_comment($comment)
    {
		$query = $this->db->placehold("
            INSERT INTO __comments SET ?%
        ", (array)$comment);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_comment($id, $comment)
    {
		$query = $this->db->placehold("
            UPDATE __comments SET ?% WHERE id = ?
        ", (array)$comment, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_comment($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __comments WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}