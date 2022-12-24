<?php

class Contracts extends Core
{
    private $statuses = array(
        0 => 'Новый',
        1 => 'Подписан',
        2 => 'Выдан',
        3 => 'Закрыт',
        4 => 'Просрочен',
        5 => 'Истек срок подписания',
        6 => 'Не удалось выдать займ',
        7 => 'Цессия',
        8 => 'Отменен',
        9 => 'Идет выплата',
        10 => 'Подготовлена реструктуризация',
        11 => 'Реструктуризирован'
    );
    
    private $collection_statuses = array(
        2 => '0-2 дни',
        3 => 'Ожидание-1',
        4 => 'Предсофт',
        5 => 'Ожидание-2', // (11-13 день просрочки включительно)
        6 => 'Софт', // софт (14-30 включительно) / лонг-софт 14-44
        7 => 'Ожидание-3', // ожидание 3 (31-33 включительно) / лонг-софт 45-47
        8 => 'Хард', // хард (34-63 включительно) / лонг-софт 48-77
        9 => 'Ожидание-4', // ожидание 4 (64 и так далее пока шеф-коллектор не перетащит сам в хард2 на какого то менеджера руками) / лонг-софт 78+
        10 => 'Хард-2',
    );
    
    public function get_statuses()
    {
    	return $this->statuses;
    }
    
    public function get_collection_statuses()
    {
    	return $this->collection_statuses;
    }
    
    public function get_number_contract($number)
    {
    	$query = $this->db->placehold("
            SELECT *
            FROM __contracts
            WHERE number = ?
            ORDER BY id DESC
            LIMIT 1
        ", $number);
        $this->db->query($query);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';        
        return $this->db->result();        
    }
    
    public function check_expiration_contracts()
    {
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET status = 4
            WHERE status = 2
            AND return_date < ?
        ", date('Y-m-d'));
        $this->db->query($query);
    }
    
    public function distribute_contracts()
    {
        // отключил автоматическое распределение
        return false;
        
        foreach ($this->collection_statuses as $status_id => $status_name)
        {
            if ($status_managers = $this->managers->get_managers(array('collection_status' => $status_id, 'role' => 'collector', 'blocked' => 0)))
            {
                $status_contracts = $this->get_contracts(array('collection_status' => $status_id, 'collection_manager_id' => 0, 'inssuance_date_from' => '2021-06-01'));
                if ($status_contracts)
                {
                    $current_contract = reset($status_contracts);
                    $current_manager =  reset($status_managers);
                    while ($current_contract)
                    {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($current_contract, $current_manager);echo '</pre><hr />';                
                        $this->update_contract($current_contract->id, array('collection_manager_id' => $current_manager->id));
                        
                        $this->collections->add_moving(array(
                            'manager_id' => $current_manager->id,
                            'contract_id' => $current_contract->id,
                            'from_date' => date('Y-m-d H:i:s'),
                        ));
                        
//                        $this->soap1c->send_collector($current_contract->number, $current_manager->id);
                        
                        $current_contract = next($status_contracts);
                        if (!($current_manager = next($status_managers)))
                            $current_manager = reset($status_managers);
                    }
                    
                }
            }
        }    
    }
    
    public function check_sold_contracts()
    {
        return false;
        
        $cess_numbers = array();

        // Более 11 дней просрочки
        $date_minus11 = date('Y-m-d', time() - 11 * 86400);
        $query = $this->db->placehold("
            SELECT *
            FROM __contracts
            WHERE (status = 4 OR status = 2)
            AND DATE(return_date) <= ?
            AND collection_handchange = 0
            AND sold = 0
        ", $date_minus11);
        $this->db->query($query);
        $results = $this->db->results();
        foreach ($results as $cess)
        {
            $cess_numbers[] = $cess->number;
            $this->update_contract($cess->id, array(
                'sold' => 1,
                'sold_date' => date('Y-m-d H:i:s')
            ));
        }


        // 15 день после пятой пролонгации
        $date_minus15 = date('Y-m-d', time() - 15 * 86400);
        $query = $this->db->placehold("
            SELECT 
                c.id,
                c.return_date,
                c.number,
                COUNT(o.id) AS op_count
            FROM s_operations AS o
            LEFT JOIN s_contracts AS c
            ON o.contract_id = c.id
            LEFT JOIN s_transactions AS t
            ON o.transaction_id = t.id
            WHERE (c.status = 4 OR c.status = 2)
            AND DATE(return_date) <= ?
            AND c.sold = 0
            AND o.type = 'PAY'
            AND t.prolongation = 1
            GROUP BY c.id
        ", $date_minus15);
        $this->db->query($query);
        $results = $this->db->results();
        foreach ($results as $cess)
        {
            $cess_numbers[] = $cess->number;
            $this->update_contract($cess->id, array(
                'sold' => 1,
                'sold_date' => date('Y-m-d H:i:s')
            ));
        }

        

        // выплачено более 150% ОД    	

        $this->soap1c->send_cessions($cess_numbers);
    }
    
    public function check_collection_contracts()
    {
        $collection_periods = $this->settings->collection_periods;
        $shift = 0;
    	foreach ($this->collection_statuses as $cs_id => $cs)
        {
            if (is_numeric($shift))
            {
                if (empty($shift))
                    $from = date('Y-m-d', time() - $shift * 86400);
                else
                    $from = date('Y-m-d', time() - ($shift + 1) * 86400);
                $shift += $collection_periods[$cs_id];
                $to = date('Y-m-d', time() - $shift * 86400);
                
                $query = $this->db->placehold("
                    SELECT *
                    FROM __contracts
                    WHERE status = 4
                    AND collection_status != ?
                    AND collection_status != 10
                    AND DATE(return_date) <= ?
                    AND DATE(return_date) >= ?
                    AND collection_handchange = 0
                ", $cs_id, $from, $to);
                $this->db->query($query);

                if ($results = $this->db->results())
                {
                    foreach ($results as $result)
                    {
                        $this->contracts->update_contract($result->id, array(
                            'collection_status' => $cs_id,
                            'collection_manager_id' => 0,
                            'collection_workout' => 0
                        ));
                        $this->users->update_user($result->user_id, array('contact_status' => 0));
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('move to '.$cs_id, $result);echo '</pre><hr />';                        
                    }
                }
                
            }
        }
    }
    
    
    
    public function check_collection_contracts_old()
    {
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump();echo '</pre><hr />';
        // просрочка 0-2
        $date_minus2 = date('Y-m-d', time() - 2 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 2,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 2
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
        ", date('Y-m-d'), $date_minus2);
//        $this->db->query($query);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
        
        //Ожидание-1
        $date_minus3 = date('Y-m-d', time() - 3 * 86400);
        $date_minus5 = date('Y-m-d', time() - 5 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 3,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 3
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
        ", $date_minus3, $date_minus5);
//        $this->db->query($query);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
                
        // Предсофт        
        $date_minus6 = date('Y-m-d', time() - 6 * 86400);
        $date_minus10 = date('Y-m-d', time() - 10 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 4,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 4
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
        ", $date_minus6, $date_minus10);
//        $this->db->query($query);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

        // Ожидание-2
        $date_minus11 = date('Y-m-d', time() - 11 * 86400);
        $date_minus13 = date('Y-m-d', time() - 13 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 5,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 5
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
        ", $date_minus11, $date_minus13);
//        $this->db->query($query);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
        
        // Софт 14-30 включительно
        $date_minus14 = date('Y-m-d', time() - 14 * 86400);
        $date_minus30 = date('Y-m-d', time() - 30 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 6,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 6
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
            AND short_soft = 1
        ", $date_minus14, $date_minus30);
//        $this->db->query($query);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

        // лонг-софт 14-44 включительно
        $date_minus14 = date('Y-m-d', time() - 14 * 86400);
        $date_minus44 = date('Y-m-d', time() - 44 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 6,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 6
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
            AND short_soft = 0
        ", $date_minus14, $date_minus44);
//        $this->db->query($query);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';


        // ожидание 3 (31-33 включительно)
        $date_minus31 = date('Y-m-d', time() - 31 * 86400);
        $date_minus33 = date('Y-m-d', time() - 33 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 7,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 7
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
            AND short_soft = 1
        ", $date_minus31, $date_minus33);
//        $this->db->query($query);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
                
        // ожидание 3 (лонг-софт 45-47)
        $date_minus45 = date('Y-m-d', time() - 45 * 86400);
        $date_minus47 = date('Y-m-d', time() - 47 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 7,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 7
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
            AND short_soft = 0
        ", $date_minus45, $date_minus47);
//        $this->db->query($query);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

                
        // хард (34-63 включительно)
        $date_minus34 = date('Y-m-d', time() - 34 * 86400);
        $date_minus63 = date('Y-m-d', time() - 63 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 8,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 8
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
            AND short_soft = 1
        ", $date_minus34, $date_minus63);
//        $this->db->query($query);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

        // хард (лонг-софт 48-77 включительно)
        $date_minus48 = date('Y-m-d', time() - 48 * 86400);
        $date_minus77 = date('Y-m-d', time() - 77 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 8,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 8
            AND DATE(return_date) <= ?
            AND DATE(return_date) >= ?
            AND collection_handchange = 0
            AND short_soft = 0
        ", $date_minus48, $date_minus77);
//        $this->db->query($query);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';


        
        // ожидание 4 (64 и так далее пока шеф-коллектор не перетащит сам в хард2 на какого то менеджера руками)
        $date_minus64 = date('Y-m-d', time() - 64 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 9,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 9
            AND DATE(return_date) <= ?
            AND collection_handchange = 0
            AND short_soft = 1
        ", $date_minus64);
//        $this->db->query($query);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

        // ожидание 4 (лонг-софт 78+)
        $date_minus78 = date('Y-m-d', time() - 78 * 86400);
    	$query = $this->db->placehold("
            UPDATE __contracts
            SET collection_status = 9,
            collection_manager_id = 0,
            collection_workout = 0
            WHERE status = 4
            AND collection_status != 9
            AND DATE(return_date) <= ?
            AND collection_handchange = 0
            AND short_soft = 0
        ", $date_minus78);
//        $this->db->query($query);
echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

    }
        
	public function get_order_contract($order_id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __contracts
            WHERE order_id = ?
            ORDER BY id DESC
            LIMIT 1
        ", (int)$order_id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_contract($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __contracts
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_contracts($filter = array())
	{
		$id_filter = '';
        $user_id_filter = '';
        $status_filter = '';
        $type_filter = '';
        $sent_status_filter = '';
        $collection_status_filter = '';
        $collection_manager_id_filter = '';
        $inssuance_date_from_filter = '';
        $inssuance_date_to_filter = '';
        $inssuance_datetime_to_filter = '';
        $close_date_from_filter = '';
        $close_date_to_filter = '';
        $sold_date_from_filter = '';
        $sold_date_to_filter = '';
        $sud_filter = '';
        $stop_profit_filter = '';
        $keyword_filter = '';
        $search_filter = '';
        $limit = 10000;
		$page = 1;
        $sort = 'id DESC';
        $sort_workout = '';
        $is_restructed = '';
        
        $users_join = '';
        
        if (!empty($filter['sort_workout']))
            $sort_workout = "c.collection_workout ASC, ";

        if (isset($filter['is_restructed']))
            $is_restructed = $this->db->placehold("AND c.is_restructed = ?", $filter['is_restructed']);
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND c.id IN (?@)", array_map('intval', (array)$filter['id']));
        
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND c.user_id = ?", (int)$filter['user_id']);
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND c.status IN (?@)", array_map('intval', (array)$filter['status']));
        
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND c.type = ?", $filter['type']);
        
        if (isset($filter['sent_status']))
            $sent_status_filter = $this->db->placehold("AND c.sent_status = ?", (int)$filter['sent_status']);

        if (isset($filter['collection_status']))
            $collection_status_filter = $this->db->placehold("AND c.collection_status in (?@)", (array)$filter['collection_status']);
        
        if (isset($filter['collection_manager_id']))
            if (empty($filter['collection_manager_id']))
                $collection_manager_id_filter = $this->db->placehold("AND (c.collection_manager_id IS NULL OR c.collection_manager_id = 0)");        
            else
                $collection_manager_id_filter = $this->db->placehold("AND c.collection_manager_id in (?@)", (array)$filter['collection_manager_id']);        
        
        if (!empty($filter['inssuance_date_from']))
            $inssuance_date_from_filter = $this->db->placehold("AND DATE(c.inssuance_date) >= ?", $filter['inssuance_date_from']);
            
        if (!empty($filter['inssuance_date_to']))
            $inssuance_date_to_filter = $this->db->placehold("AND DATE(c.inssuance_date) <= ?", $filter['inssuance_date_to']);

        if (!empty($filter['inssuance_datetime_to']))
            $inssuance_datetime_to_filter = $this->db->placehold("AND c.inssuance_date <= ?", $filter['inssuance_datetime_to']);

        if (!empty($filter['close_date_from']))
            $close_date_from_filter = $this->db->placehold("AND DATE(c.close_date) >= ?", $filter['close_date_from']);
            
        if (!empty($filter['close_date_to']))
            $close_date_to_filter = $this->db->placehold("AND DATE(c.close_date) <= ?", $filter['close_date_to']);

        if (!empty($filter['sold_date_from']))
            $sold_date_from_filter = $this->db->placehold("AND DATE(c.sold_date) >= ?", $filter['sold_date_from']);
            
        if (!empty($filter['sold_date_to']))
            $sold_date_to_filter = $this->db->placehold("AND DATE(c.sold_date) <= ?", $filter['sold_date_to']);

        if (isset($filter['sud']))
            $sud_filter = $this->db->placehold("AND sud = ?", (int)$filter['sud']);
        
        if (isset($filter['stop_profit']))
            $stop_profit_filter = $this->db->placehold("AND stop_profit = ?", (int)$filter['stop_profit']);
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);
        
        if (!empty($filter['sort']))
        {
            switch ($filter['sort']):
                
                case 'order_id_desc':
                    $sort = 'c.order_id DESC';
                break;
                
                case 'order_id_asc':
                    $sort = 'c.order_id ASC';
                break;
                
                case 'manager_id_asc':
                    $sort = 'c.collection_manager_id ASC';
                break;
                
                case 'manager_id_desc':
                    $sort = 'c.collection_manager_id DESC';                
                break;
                
                case 'fio_asc':
                    $sort = 'u.lastname ASC';
                    $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';                    
                break;
                
                case 'fio_desc':
                    $sort = 'u.lastname DESC';                
                    $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';
                break;
                
                case 'phone_asc':
                    $sort = 'u.phone_mobile ASC';
                    $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';                    
                break;
                
                case 'phone_desc':
                    $sort = 'u.phone_mobile DESC';                
                    $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';
                break;
                
                case 'body_asc':
                    $sort = 'c.loan_body_summ ASC';
                break;
                
                case 'body_desc':
                    $sort = 'c.loan_body_summ DESC';                
                break;
                
                case 'percents_asc':
                    $sort = '(c.loan_percents_summ + c.loan_charge_summ + c.loan_peni_summ) ASC';
                break;
                
                case 'percents_desc':
                    $sort = '(c.loan_percents_summ + c.loan_charge_summ + c.loan_peni_summ) DESC';                
                break;
                
                case 'total_asc':
                    $sort = '(c.loan_body_summ + c.loan_percents_summ + c.loan_charge_summ + c.loan_peni_summ) ASC';
                break;
                
                case 'total_desc':
                    $sort = '(c.loan_body_summ + c.loan_percents_summ + c.loan_charge_summ + c.loan_peni_summ) DESC';                
                break;
                
                case 'status_asc':
                    $sort = 'u.contact_status ASC';
                    $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';
                break;
                
                case 'status_desc':
                    $sort = 'u.contact_status DESC';
                    $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';
                break;
                
                case 'return_asc':
                    $sort = 'c.return_date ASC';
                break;
                
                case 'return_desc':
                    $sort = 'c.return_date DESC';
                break;                
                
            endswitch;
        }
        
        if (!empty($filter['search']))
        {
            if (!empty($filter['search']['order_id']))
                $search_filter .= $this->db->placehold(' AND c.id IN (SELECT contract_id FROM __orders WHERE id = ?)', (int)$filter['search']['order_id']);
            if (!empty($filter['search']['fio']))
            {
                $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';                    
                $fio_filter = array();
                $expls = array_map('trim', explode(' ', $filter['search']['fio']));
                $search_filter .= $this->db->placehold(' AND (');
                foreach ($expls as $expl)
                {
                    $expl = $this->db->escape($expl);
                    $fio_filter[] = $this->db->placehold("(u.lastname LIKE '%".$expl."%' OR u.firstname LIKE '%".$expl."%' OR u.patronymic LIKE '%".$expl."%')");
                }
                $search_filter .= implode(' AND ', $fio_filter);
                $search_filter .= $this->db->placehold(')');
            }
            if (!empty($filter['search']['phone']))
            {
                $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';                    
                $search_filter .= $this->db->placehold(" AND (u.phone_mobile LIKE '%".$this->db->escape(str_replace(array(' ', '-', '(', ')', '+'), '', $filter['search']['phone']))."%'");
                $search_filter .= $this->db->placehold(" OR c.user_id IN (SELECT user_id FROM __contactpersons WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', ''), ')', ''), '(', '') LIKE '%".$this->db->escape(str_replace(array(' ', '-', '(', ')', '+'), '', $filter['search']['phone']))."%'))");
            }
            
            if (!empty($filter['search']['tag_id']))
            {
                $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';                    
                $search_filter .= $this->db->placehold(" AND u.contact_status = ?", $filter['search']['tag_id']);
            }
            
            if (!empty($filter['search']['manager_id']))
            {
                if ($filter['search']['manager_id'] == 'none')
                    $search_filter .= $this->db->placehold(" AND (c.collection_manager_id = 0 OR c.collection_manager_id IS NULL)");                
                else
                    $search_filter .= $this->db->placehold(" AND c.collection_manager_id = ?", (int)$filter['search']['manager_id']);
            }
            
            if (!empty($filter['search']['delay_from']))
            {
                $delay_from_date = date('Y-m-d', time() - $filter['search']['delay_from']*86400);
                $search_filter .= $this->db->placehold(" AND DATE(c.return_date) <= ?", $delay_from_date);
            }
            if (!empty($filter['search']['delay_to']))
            {
                $delay_to_date = date('Y-m-d', time() - $filter['search']['delay_to']*86400);
                $search_filter .= $this->db->placehold(" AND DATE(c.return_date) >= ?", $delay_to_date);
            }
            if (!empty($filter['search']['delay_plus_from']))
            {
                $delay_from_date = date('Y-m-d', time() + $filter['search']['delay_plus_from']*86400);
                $search_filter .= $this->db->placehold(" AND DATE(c.return_date) <= ?", $delay_from_date);
            }
        }
        
        $query = $this->db->placehold("
            SELECT c.* 
            FROM __contracts AS c
            $users_join
            WHERE 1
                $id_filter
                $user_id_filter
                $status_filter
                $type_filter
                $sent_status_filter
                $collection_status_filter
                $collection_manager_id_filter
                $inssuance_date_from_filter
                $inssuance_date_to_filter
                $inssuance_datetime_to_filter
                $close_date_from_filter
                $close_date_to_filter
                $sold_date_from_filter
                $sold_date_to_filter
                $sud_filter
                $stop_profit_filter
                $keyword_filter
                $search_filter
                $is_restructed
            GROUP BY c.id
            ORDER BY $sort_workout $sort 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
        return $results;
	}
    
	public function count_contracts($filter = array())
	{
        $id_filter = '';
        $user_id_filter = '';
        $status_filter = '';
        $sent_status_filter = '';
        $collection_status_filter = '';
        $collection_manager_id_filter = '';
        $inssuance_date_from_filter = '';
        $inssuance_date_to_filter = '';
        $close_date_from_filter = '';
        $close_date_to_filter = '';
        $sold_date_from_filter = '';
        $sold_date_to_filter = '';
        $sud_filter = '';
        $stop_profit_filter = '';
        $keyword_filter = '';
        $search_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id = ?", (int)$filter['user_id']);
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status IN (?@)", array_map('intval', (array)$filter['status']));
        
        if (isset($filter['sent_status']))
            $sent_status_filter = $this->db->placehold("AND sent_status = ?", (int)$filter['sent_status']);
        
        if (isset($filter['collection_status']))
            $collection_status_filter = $this->db->placehold("AND collection_status IN (?@)", (array)$filter['collection_status']);
        
        if (isset($filter['collection_manager_id']))
            $collection_manager_id_filter = $this->db->placehold("AND collection_manager_id in (?@)", (array)$filter['collection_manager_id']);

        if (!empty($filter['inssuance_date_from']))
            $inssuance_date_from_filter = $this->db->placehold("AND DATE(inssuance_date) >= ?", $filter['inssuance_date_from']);
            
        if (!empty($filter['inssuance_date_to']))
            $inssuance_date_to_filter = $this->db->placehold("AND DATE(inssuance_date) <= ?", $filter['inssuance_date_to']);

        if (!empty($filter['close_date_from']))
            $close_date_from_filter = $this->db->placehold("AND DATE(close_date) >= ?", $filter['close_date_from']);
            
        if (!empty($filter['close_date_to']))
            $close_date_to_filter = $this->db->placehold("AND DATE(close_date) <= ?", $filter['close_date_to']);

        if (!empty($filter['sold_date_from']))
            $sold_date_from_filter = $this->db->placehold("AND DATE(sold_date) >= ?", $filter['sold_date_from']);
            
        if (!empty($filter['sold_date_to']))
            $sold_date_to_filter = $this->db->placehold("AND DATE(sold_date) <= ?", $filter['sold_date_to']);
        
        if (isset($filter['sud']))
            $sud_filter = $this->db->placehold("AND sud = ?", (int)$filter['sud']);

        if (isset($filter['stop_profit']))
            $stop_profit_filter = $this->db->placehold("AND stop_profit = ?", (int)$filter['stop_profit']);
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
        if (!empty($filter['search']))
        {
            if (!empty($filter['search']['order_id']))
                $search_filter .= $this->db->placehold(' AND c.id IN (SELECT contract_id FROM __orders WHERE id = ?)', (int)$filter['search']['order_id']);
            if (!empty($filter['search']['fio']))
            {
                $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';                    
                $fio_filter = array();
                $expls = array_map('trim', explode(' ', $filter['search']['fio']));
                $search_filter .= $this->db->placehold(' AND (');
                foreach ($expls as $expl)
                {
                    $expl = $this->db->escape($expl);
                    $fio_filter[] = $this->db->placehold("(u.lastname LIKE '%".$expl."%' OR u.firstname LIKE '%".$expl."%' OR u.patronymic LIKE '%".$expl."%')");
                }
                $search_filter .= implode(' AND ', $fio_filter);
                $search_filter .= $this->db->placehold(')');
            }
            if (!empty($filter['search']['phone']))
            {
                $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';                    
                $search_filter .= $this->db->placehold(" AND u.phone_mobile LIKE '%".$this->db->escape(str_replace(array(' ', '-', '(', ')', '+'), '', $filter['search']['phone']))."%'");
                $search_filter .= $this->db->placehold(" OR c.user_id IN (SELECT user_id FROM __contactpersons WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', ''), ')', ''), '(', '') LIKE '%".$this->db->escape(str_replace(array(' ', '-', '(', ')', '+'), '', $filter['search']['phone']))."%'))");
            }
            
            if (!empty($filter['search']['tag_id']))
            {
                $users_join = 'RIGHT JOIN __users AS u ON c.user_id = u.id';                    
                $search_filter .= $this->db->placehold(" AND u.contact_status = ?", $filter['search']['tag_id']);
            }
            
            if (!empty($filter['search']['manager_id']))
            {
                if ($filter['search']['manager_id'] == 'none')
                    $search_filter .= $this->db->placehold(" AND (c.collection_manager_id = 0 OR c.collection_manager_id IS NULL)");                
                else
                    $search_filter .= $this->db->placehold(" AND c.collection_manager_id = ?", (int)$filter['search']['manager_id']);
            }

            if (!empty($filter['search']['delay_from']))
            {
                $delay_from_date = date('Y-m-d', time() - $filter['search']['delay_from']*86400);
                $search_filter .= $this->db->placehold(" AND DATE(c.return_date) <= ?", $delay_from_date);
            }
            if (!empty($filter['search']['delay_to']))
            {
                $delay_to_date = date('Y-m-d', time() - $filter['search']['delay_to']*86400);
                $search_filter .= $this->db->placehold(" AND DATE(c.return_date) >= ?", $delay_to_date);
            }
        }
        
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __contracts AS c
            WHERE 1
                $id_filter
                $user_id_filter
                $status_filter
                $sent_status_filter
                $collection_status_filter
                $collection_manager_id_filter
                $inssuance_date_from_filter
                $inssuance_date_to_filter
                $close_date_from_filter
                $close_date_to_filter
                $sold_date_from_filter
                $sold_date_to_filter
                $sud_filter
                $stop_profit_filter
                $keyword_filter
                $search_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
if ($this->is_developer)
{
//    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
}	
        return $count;
    }
    
    public function add_contract($contract)
    {
		$contract = (array)$contract;
        
        if (empty($contract['create_date']))
            $contract['create_date'] = date('Y-m-d H:i:s');
        
        $query = $this->db->placehold("
            INSERT INTO __contracts SET ?%
        ", $contract);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        if (empty($contract['number']))
        {
            $contract_date = strtotime($contract['create_date']);
            $uid = exec($this->config->root_dir.'generic/uidgen');
            $contract_number = date('md', $contract_date).'-'.$id;
    
            $this->update_contract($id, array('uid' => $uid, 'number'=>$contract_number));
        }
        
        return $id;
    }
    
    public function update_contract($id, $contract)
    {
		$query = $this->db->placehold("
            UPDATE __contracts SET ?% WHERE id = ?
        ", (array)$contract, (int)$id);

		$this->db->query($query);

        return $id;
    }
    
    public function delete_contract($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __contracts WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    public function get_contracts_orders($filter = array())
    {
        $inssuance_date_from_filter = '';
        $inssuance_date_to_filter = '';

        if (!empty($filter['inssuance_date_from']))
            $inssuance_date_from_filter = $this->db->placehold("AND DATE(con.inssuance_date) >= ?", $filter['inssuance_date_from']);

        if (!empty($filter['inssuance_date_to']))
            $inssuance_date_to_filter = $this->db->placehold("AND DATE(con.inssuance_date) <= ?", $filter['inssuance_date_to']);

        $query = $this->db->placehold("
            SELECT * 
            FROM s_contracts as con
            JOIN s_orders as o on o.id = con.order_id
            WHERE 1 
            $inssuance_date_from_filter 
            $inssuance_date_to_filter
        ");

        $this->db->query($query);

        $result = $this->db->results();

        return $result;
    }

    public function cession_from_csv($prepare_item)
    {

        $data = ['loan_body_summ' => $prepare_item['od'], 'loan_percents_summ' => $prepare_item['percents'],
            'status' => 7, 'sold' => 1, 'sold_date' => date('Y-m-d', strtotime($prepare_item['date']))];

        $query = $this->db->placehold("
            UPDATE __contracts 
            SET ?%
            WHERE `number` = ?
        ", (array)$data, $prepare_item['number']);

        $this->db->query($query);

        return 'success';
    }

    public function get_last_close_contract($user_id, $shift = 0)
    {
    	$query = $this->db->placehold("
            SELECT * 
            FROM __contracts
            WHERE user_id = ?
            AND status IN (3)
            ORDER BY id DESC 
            LIMIT ?, 1
        ", $user_id, $shift);
        $this->db->query($query);
        
        return $this->db->result();
    }
    
}