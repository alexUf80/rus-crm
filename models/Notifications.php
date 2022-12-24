<?php

class Notifications extends Core
{
    private $events = array(
        1 => array(
            'id' => 1,
            'name' => 'Возбуждено ИП',
            'action' => 'Получить информацию о ходе ИП',
        ),
        2 => array(
            'id' => 2,
            'name' => 'Направлено в Банк',
            'action' => 'Проверить  перечисления из банка',
        ),
        3 => array(
            'id' => 3,
            'name' => 'Направлено в ПФР',
            'action' => 'Проверить перечисления из ПФР',
        ),
        4 => array(
            'id' => 4,
            'name' => 'Окончено ИП',
            'action' => 'Контроль получения оригинала ИД',
        ),
        5 => array(
            'id' => 5,
            'name' => 'Отправлен запрос',
            'action' => 'Контроль ответа на запрос',
        ),
        6 => array(
            'id' => 6,
            'name' => 'Отправлена жалоба',
            'action' => 'Контроль ответа на жалобу',
        ),
        7 => array(
            'id' => 7,
            'name' => 'Отправлен иск',
            'action' => 'Контроль направленного иска',
        ),
        8 => array(
            'id' => 8,
            'name' => 'Отправлено ходатайство по ИП',
            'action' => 'Контроль направленного ходатайства',
        ),
        9 => array(
            'id' => 9,
            'name' => 'Получено постановление',
            'action' => 'Контроль исполнения постановления',
        ),
        10 => array(
            'id' => 10,
            'name' => 'Свободное напоминание',
            'action' => 'Свободное напоминание',
        ),
        
    );
    
    public function get_events()
    {
    	return array_map(function($var){
    	   return (object)$var;
    	}, $this->events);
    }
    
    public function get_event($id)
    {
    	if (isset($this->events[$id]))
            return (object)$this->events[$id];
        
        return NULL;
    }
    
    
	public function get_notification($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __notifications
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_notifications($filter = array())
	{
		$id_filter = '';
		$sudblock_contract_id_filter = '';
        $collection_contract_id_filter = '';
        $notification_date_filter = '';
        $done_filter = '';
        $sudblock_mode_filter = '';
        $collection_mode_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['sudblock_contract_id']))
            $sudblock_contract_id_filter = $this->db->placehold("AND sudblock_contract_id IN (?@)", array_map('intval', (array)$filter['sudblock_contract_id']));
        
        if (!empty($filter['collection_contract_id']))
            $collection_contract_id_filter = $this->db->placehold("AND collection_contract_id IN (?@)", array_map('intval', (array)$filter['collection_contract_id']));
        
        if (!empty($filter['sudblock_mode']))
            $sudblock_mode_filter = $this->db->placehold("AND sudblock_contract_id > 0");
        
        if (!empty($filter['collection_mode']))
            $collection_mode_filter = $this->db->placehold("AND collection_contract_id > 0");
        
        if (!empty($filter['notification_date']))
            $notification_date_filter = $this->db->placehold("AND DATE(notification_date) = ?", $filter['notification_date']);
        
        if (isset($filter['done']))
            $done_filter = $this->db->placehold("AND done = ?", (int)$filter['done']);
        
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
            FROM __notifications
            WHERE 1
                $id_filter
				$sudblock_contract_id_filter
                $collection_contract_id_filter
                $notification_date_filter
                $done_filter
                $sudblock_mode_filter
                $collection_mode_filter
                $keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
	}
    
	public function count_notifications($filter = array())
	{
        $id_filter = '';
        $sudblock_contract_id_filter = '';
        $collection_contract_id_filter = '';
        $sudblock_mode_filter = '';
        $collection_mode_filter = '';
        $notification_date_filter = '';
        $done_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['sudblock_contract_id']))
            $sudblock_contract_id_filter = $this->db->placehold("AND sudblock_contract_id IN (?@)", array_map('intval', (array)$filter['sudblock_contract_id']));
        
        if (!empty($filter['collection_contract_id']))
            $collection_contract_id_filter = $this->db->placehold("AND collection_contract_id IN (?@)", array_map('intval', (array)$filter['collection_contract_id']));
        
        if (!empty($filter['notification_date']))
            $notification_date_filter = $this->db->placehold("AND DATE(notification_date) = ?", $filter['notification_date']);
        
        if (isset($filter['done']))
            $done_filter = $this->db->placehold("AND done = ?", (int)$filter['done']);
        
        if (!empty($filter['sudblock_mode']))
            $sudblock_mode_filter = $this->db->placehold("AND sudblock_contract_id > 0");
        
        if (!empty($filter['collection_mode']))
            $collection_mode_filter = $this->db->placehold("AND collection_contract_id > 0");
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __notifications
            WHERE 1
                $id_filter
                $sudblock_contract_id_filter
                $collection_contract_id_filter
                $notification_date_filter
                $done_filter
                $sudblock_mode_filter
                $collection_mode_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_notification($notification)
    {
		$query = $this->db->placehold("
            INSERT INTO __notifications SET ?%
        ", (array)$notification);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_notification($id, $notification)
    {
		$query = $this->db->placehold("
            UPDATE __notifications SET ?% WHERE id = ?
        ", (array)$notification, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_notification($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __notifications WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}