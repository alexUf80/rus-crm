<?php

class Communications extends Core
{
    /**
     * Communications::check_user()
     * 
     * а) не более двух раз в сутки;
     * б) не более четырех раз в неделю;
     * в) не более шестнадцати раз в месяц
     * 
     * @param integer $user_id
     * @return boolean true - звонить можно, false - звонить нельзя
     */
    public function check_user($user_id)
    {
        if ($communications = $this->get_communications(array('user_id' => $user_id)))
        {
            $limit_communications_day = $this->settings->limit_communications['day'];
            $limit_communications_week = $this->settings->limit_communications['week'];
            $limit_communications_month = $this->settings->limit_communications['month'];

            $day_time = strtotime(date('Y-m-d 00:00:00'));
            $week_time = (time() - 7*86400);
            $month_time = (time() - 30*86400);
            
            $day = 0;
            $week = 0;
            $month = 0;

            foreach ($communications as $c)
            {
                if (strtotime($c->created) >= $day_time)
                    $day++;
                if (strtotime($c->created) >= $week_time)
                    $week++;
                if (strtotime($c->created) >= $month_time)
                    $month++;            
            }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($day, $week, $month);echo '</pre><hr />';            

            if (!empty($day) && $day >= $limit_communications_day)
                return false;
            if (!empty($week) && $week >= $limit_communications_week)
                return false;
            if (!empty($month) && $month >= $limit_communications_month)
                return false;
        
        }
        
        return true;
    }
    
	public function get_communication($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __communications
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_communications($filter = array())
	{
		$id_filter = '';
        $type_filter = '';
		$user_id_filter = '';
        $from_filter = '';
        $to_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
		
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type IN (?@)", array_map('strval', (array)$filter['type']));
		
        if (!empty($filter['from']))
            $from_filter = $this->db->placehold("AND created >= ?", $filter['from']);
        
        if (!empty($filter['to']))
            $to_filter = $this->db->placehold("AND created <= ?", $filter['to']);
        
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
            FROM __communications
            WHERE 1
                $id_filter
                $type_filter
                $user_id_filter
                $from_filter
                $to_filter
				$keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_communications($filter = array())
	{
        $id_filter = '';
        $type_filter = '';
        $user_id_filter = '';
        $from_filter = '';
        $to_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type IN (?@)", array_map('strval', (array)$filter['type']));
		
        if (!empty($filter['from']))
            $from_filter = $this->db->placehold("AND created >= ?", $filter['from']);
        
        if (!empty($filter['to']))
            $to_filter = $this->db->placehold("AND created <= ?", $filter['to']);
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
		
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __communications
            WHERE 1
                $id_filter
                $type_filter
                $user_id_filter
                $from_filter
                $to_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_communication($communication)
    {
		$query = $this->db->placehold("
            INSERT INTO __communications SET ?%
        ", (array)$communication);
        $this->db->query($query);

        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_communication($id, $communication)
    {
		$query = $this->db->placehold("
            UPDATE __communications SET ?% WHERE id = ?
        ", (array)$communication, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_communication($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __communications WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}