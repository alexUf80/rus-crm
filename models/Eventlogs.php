<?php

class Eventlogs extends Core
{
	private $events = array(
        1 => 'Переход в карточку',

        10 => 'Заявка принята',
        11 => 'Заявка перепринята',
        12 => 'Заявка одобрена',
        13 => 'Отказ по заявке',
        14 => 'Заявка подтверждена',
        15 => 'Договор закрыт',
        16 => 'Перевыдача',
        17 => 'Выдача по автоповтору',
        
        20 => 'Вкладка Персональная информация',
        21 => 'Вкладка Комментарии',
        22 => 'Вкладка Документы',
        23 => 'Вкладка Логирование',
        24 => 'Вкладка Операции',
        25 => 'Вкладка Кредитная история',
        
        30 => 'Редактирование ФИО',
        31 => 'Редактирование Сумма и срок',
        32 => 'Редактирование Контакты',
        33 => 'Редактирование Контактные лица',
        34 => 'Редактирование Адрес',
        35 => 'Редактирование Данные о работе',
        36 => 'Редактирование Услуги',
        37 => 'Редактирование Карта',

        40 => 'Сохранение ФИО',
        41 => 'Сохранение Сумма и срок',
        42 => 'Сохранение Контакты',
        43 => 'Сохранение Контактные лица',
        44 => 'Сохранение Адрес',
        45 => 'Сохранение Данные о работе',
        46 => 'Сохранение Услуги',
        47 => 'Сохранение Карта',
        
        50 => 'Открытие фото',
        51 => 'Фото принято',
        52 => 'Фото отклонено',
        53 => 'Фото удалено',
        
        60 => 'Звонок клиенту',
        61 => 'Звонок КЛ',
        62 => 'Звонок на работу',
        63 => 'Звонок директору',
        
        70 => 'Добавление комментария',
        
    );
    
    public function get_events()
    {
    	return $this->events;
    }
    
    public function get_log($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __eventlogs
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_logs($filter = array())
	{
		$id_filter = '';
        $order_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['order_id']))
            $order_filter = $this->db->placehold("AND order_id = ?", (int)$filter['order_id']);
        
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
            FROM __eventlogs
            WHERE 1
                $id_filter
                $order_filter
				$keyword_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_logs($filter = array())
	{
        $id_filter = '';
        $order_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['order_id']))
            $order_filter = $this->db->placehold("AND order_id = ?", (int)$filter['order_id']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __eventlogs
            WHERE 1
                $id_filter
                $order_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_log($eventlog)
    {
		$query = $this->db->placehold("
            INSERT INTO __eventlogs SET ?%
        ", (array)$eventlog);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_log($id, $eventlog)
    {
		$query = $this->db->placehold("
            UPDATE __eventlogs SET ?% WHERE id = ?
        ", (array)$eventlog, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_log($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __eventlogs WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}