<?php

class Orders extends Core
{
    private $statuses = array(
        0 => 'Принята',
        1 => 'На рассмотрении',
        2 => 'Одобрена',
        3 => 'Отказ',
        4 => 'Готов к выдаче',
        5 => 'Займ выдан',
        6 => 'Не удалось выдать',
        7 => 'Погашен',
        8 => 'Отказ клиента',
        9 => 'Предодобрена'
    );
    
    public function get_statuses()
    {
        return $this->statuses;
    }

    public function leadgens($filter)
    {
        $limit = 10000;
        $page = 1;
        $integrationFilter = '';

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        if (!empty($filter['integration_filter']))
            $integrationFilter = $this->db->placehold("AND os.utm_source IN (?@)", $filter['integration_filter']);

        if(isset($filter['limit']) && isset($filter['page']))
            $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        else
            $sql_limit = '';

        $query = $this->db->placehold("
        SELECT os.id,
        cs.number,
        cs.amount as con_amount,
        os.`status`,
        os.utm_source,
        os.click_hash,
        os.webmaster_id,
        os.`date`,
        os.`amount`,
        case when os.`amount` < 9999 then 670 when os.`amount` >= 10000 and os.`amount` <= 15000 then 1250 when os.`amount` >= 15001 and os.`amount` <= 30000 then 2500 when os.`amount` >= 30001 then 3750 ELSE 0 END as bet
        FROM s_orders os
        left JOIN s_contracts as cs on os.id = cs.order_id
        WHERE os.`date` between ? and ?
        $integrationFilter
        GROUP BY os.id DESC $sql_limit
        ", date('Y-m-d 00:00:00', strtotime($filter['date_from'])), date('Y-m-d 23:59:59', strtotime($filter['date_to'])));

        $this->db->query($query);

        $results = $this->db->results();

        return $results;
    }

    public function count_leadgens($filter)
    {
        $limit = 10000;
        $page = 1;
        $integrationFilter = '';

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        if (!empty($filter['integration_filter']))
            $integrationFilter = $this->db->placehold("AND os.utm_source IN (?@)", $filter['integration_filter']);

        if(isset($filter['limit']) && isset($filter['page']))
            $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        else
            $sql_limit = '';

        $query = $this->db->placehold("
        SELECT count(*) as `count`
        FROM s_orders os
        left JOIN s_contracts as cs on os.id = cs.order_id
        WHERE os.`date` between ? and ?
        $integrationFilter
        $sql_limit
        ", date('Y-m-d 00:00:00', strtotime($filter['date_from'])), date('Y-m-d 23:59:59', strtotime($filter['date_to'])));

        $this->db->query($query);

        $result = $this->db->result('count');

        return $result;
    }

    public function orders_for_risks($filter)
    {
        $this->db->query("
        SELECT 
        us.id as user_id,
        os.id as order_id,
        case when os.`client_status` IN ('pk', 'crm') then 'old' ELSE 'new' END as client_status,
        os.`date`,
        os.`status`,
        os.reject_reason
        FROM s_orders os
        JOIN s_users us on us.id = os.user_id
        WHERE os.date between ? and ?
        ", date('Y-m-d 00:00:00', strtotime($filter['date_from'])), date('Y-m-d 23:59:59', strtotime($filter['date_to'])));

        $orders = $this->db->results();

        return $orders;
    }
    
	public function get_order($id)
	{
		$query = $this->db->placehold("
            SELECT 
                o.id AS order_id,
                o.uid,
                o.contract_id,
                o.manager_id,
                o.date,
                o.user_id,
                o.card_id,
                o.ip,
                o.amount,
                o.period,
                o.status,
                o.first_loan,
                o.reject_reason,
                o.reason_id,
                o.id_1c,
                o.status_1c,
                o.sent_1c,
                o.utm_source,
                o.utm_medium,
                o.utm_campaign,
                o.utm_content,
                o.utm_term,
                o.webmaster_id,
                o.click_hash,
                o.juicescore_session_id,
                o.local_time,
                o.autoretry,
                o.autoretry_result,
                o.autoretry_summ,
                o.accept_date,
                o.reject_date,
                o.approve_date,
                o.confirm_date,
                o.client_status,
                o.id_deal,
                o.id_client,
                o.antirazgon,
                o.antirazgon_date,
                o.antirazgon_amount,
                o.offline,
                o.penalty_date,
                o.quality_workout,
                o.reject_reason_returned,
                o.promocode_id,
                u.UID AS user_uid,
                u.service_sms,
                u.service_insurance,
                u.service_reason,
                u.phone_mobile,
                u.email,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.gender,
                u.birth,
                u.birth_place,
                u.passport_serial,
                u.subdivision_code,
                u.passport_date,
                u.passport_issued,
                u.snils,
                u.inn,
                u.workplace,
                u.workcomment,
                u.workaddress,
                u.profession,
                u.workphone,
                u.income,
                u.expenses,
                u.social,
                u.contact_status,
                u.loan_history,
                u.time_zone,
                u.sms,
                u.regaddress_id,
                u.faktaddress_id
            FROM __orders AS o
            LEFT JOIN __users AS u
            ON u.id = o.user_id
            WHERE o.id = ?
        ", (int)$id);
        $this->db->query($query);
        if ($result = $this->db->result())
        {
            $result->loan_history = json_decode($result->loan_history);
        }

        return $result;
    }
    
	public function get_orders($filter = array())
	{
		$id_filter = '';
		$offline_filter = '';
		$user_id_filter = '';
		$status_filter = '';
        $type_filter = '';
        $manager_id_filter = '';
        $date_from_filter = '';
        $date_to_filter = '';
        $search_filter = '';
        $keyword_filter = '';
        $current_filter = '';
        $autoretry_filter = '';
        $client_filter = '';
        $send_1c_filter = '';
        $limit = 10000;
		$page = 1;
        $workout_sort = '';
        $sort = 'order_id DESC';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND o.id IN (?@)", array_map('intval', (array)$filter['id']));
            
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND o.status IN (?@)", (array)$filter['status']);
        
        if (!empty($filter['client']))
        {
            switch ($filter['client']):
                case 'new':
                    $client_filter = $this->db->placehold("AND o.client_status = 'nk'");
                break;
                case 'repeat':
                    $client_filter = $this->db->placehold("AND o.client_status = 'rep'");
                break;
                case 'pk':
                    $client_filter = $this->db->placehold("AND (o.client_status = 'pk' OR o.client_status = 'crm')");
                break;
            endswitch;
        }
        
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND o.type IN (?@)", (array)$filter['type']);
        
        if (!empty($filter['sent_1c']))
            $type_filter = $this->db->placehold("AND o.sent_1c IN (?@)", (array)$filter['sent_1c']);

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND o.user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
        
        if (isset($filter['offline']))
            $offline_filter = $this->db->placehold("AND o.offline = ?", (int)$filter['offline']);
        
        if (!empty($filter['date_from']))
            $date_from_filter = $this->db->placehold("AND DATE(o.date) >= ?", $filter['date_from']);
            
        if (!empty($filter['date_to']))
            $date_to_filter = $this->db->placehold("AND DATE(o.date) <= ?", $filter['date_to']);

        if (isset($filter['autoretry']))
            $autoretry_filter = $this->db->placehold("AND o.autoretry = ?", (int)$filter['autoretry']);
        
        if (!empty($filter['search']))
        {
            if (!empty($filter['search']['order_id']))
                $search_filter .= $this->db->placehold(' AND (o.id = ? OR o.id IN(SELECT order_id FROM __contracts WHERE number LIKE "%'.$this->db->escape($filter['search']['order_id']).'%"))', (int)$filter['search']['order_id']);
            if (!empty($filter['search']['date']))
                $search_filter .= $this->db->placehold(' AND DATE(o.date) = ?', date('Y-m-d', strtotime($filter['search']['date'])));
            if (!empty($filter['search']['amount']))
                $search_filter .= $this->db->placehold(' AND o.amount = ?', (int)$filter['search']['amount']);
            if (!empty($filter['search']['period']))
                $search_filter .= $this->db->placehold(' AND o.period = ?', (int)$filter['search']['period']);
            if (!empty($filter['search']['fio']))
            {
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
            if (!empty($filter['search']['birth']))
                $search_filter .= $this->db->placehold(' AND DATE(u.birth) = ?', date('Y-m-d', strtotime($filter['search']['birth'])));
            if (!empty($filter['search']['phone']))
                $search_filter .= $this->db->placehold(" AND u.phone_mobile LIKE '%".$this->db->escape(str_replace(array(' ', '-', '(', ')', '+'), '', $filter['search']['phone']))."%'");
            if (!empty($filter['search']['region']))
                $search_filter .= $this->db->placehold(" AND u.Regregion LIKE '%".$this->db->escape($filter['search']['region'])."%'");
            if (!empty($filter['search']['status']))
                $search_filter .= $this->db->placehold(" AND o.1c_status LIKE '%".$this->db->escape($filter['search']['status'])."%'");
            
            if (!empty($filter['search']['manager_id']))
            {
                if ($filter['search']['manager_id'] == 'none')
                    $search_filter .= $this->db->placehold(" AND (o.manager_id = 0 OR o.manager_id IS NULL)");                
                else
                    $search_filter .= $this->db->placehold(" AND o.manager_id = ?", (int)$filter['search']['manager_id']);
            }
        }
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (o.name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
        if (!empty($filter['current']))
            $current_filter = $this->db->placehold("AND (o.manager_id = ? OR (o.manager_id IS NULL AND o.status = 0))", (int)$filter['current']);

		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);
        
        if (!empty($filter['sort']))
        {
            switch ($filter['sort']):
                
                case 'order_id_asc':
                    $sort = 'order_id ASC';
                break;
                
                case 'order_id_desc':
                    $sort = 'order_id DESC';
                break;
                
                case 'date_asc':
                    $sort = 'o.date ASC';
                break;
                
                case 'date_desc':
                    $sort = 'o.date DESC';
                break;
                
                case 'amount_desc':
                    $sort = 'o.amount DESC';
                break;
                
                case 'amount_asc':
                    $sort = 'o.amount ASC';
                break;
                
                case 'period_asc':
                    $sort = 'o.period ASC';
                break;
                
                case 'period_desc':
                    $sort = 'o.period DESC';
                break;
                
                case 'fio_asc':
                    $sort = 'u.lastname ASC';
                break;
                
                case 'fio_desc':
                    $sort = 'u.lastname DESC';
                break;
                                
                case 'birth_asc':
                    $sort = 'u.birth ASC';
                break;
                
                case 'birth_desc':
                    $sort = 'u.birth DESC';
                break;

                case 'phone_asc':
                    $sort = 'u.phone_mobile ASC';
                break;
                
                case 'phone_desc':
                    $sort = 'u.phone_mobile DESC';
                break;
                                
                case 'region_asc':
                    $sort = 'u.Regregion ASC';
                break;
                
                case 'region_desc':
                    $sort = 'u.Regregion DESC';
                break;
                                
                case 'status_asc':
                    $sort = 'o.1c_status ASC';
                break;
                
                case 'status_desc':
                    $sort = 'o.1c_status DESC';
                break;
                                
                case 'penalty_asc':
                    $sort = 'o.penalty_date ASC';
                break;
                
                case 'penalty_desc':
                    $sort = 'o.penalty_date DESC';
                break;
                                
            endswitch;
        }
        
        if (!empty($filter['workout_sort']))
            $workout_sort = 'o.quality_workout ASC, ';
        
        $query = $this->db->placehold("
            SELECT 
                o.id AS order_id,
                o.uid,
                o.contract_id,
                o.manager_id,
                o.date,
                o.user_id,
                o.card_id,
                o.ip,
                o.amount,
                o.period,
                o.status,
                o.first_loan,
                o.reject_reason,
                o.reason_id,
                o.id_1c,
                o.status_1c,
                o.sent_1c,
                o.utm_source,
                o.utm_medium,
                o.utm_campaign,
                o.utm_content,
                o.utm_term,
                o.webmaster_id,
                o.click_hash,
                o.juicescore_session_id,
                o.local_time,
                o.autoretry,
                o.autoretry_result,
                o.autoretry_summ,
                o.accept_date,
                o.reject_date,
                o.approve_date,
                o.confirm_date,
                o.client_status,
                o.antirazgon,
                o.antirazgon_date,
                o.antirazgon_amount,
                o.offline,
                o.penalty_date,
                o.quality_workout,
                o.id_deal,
                u.UID AS user_uid,
                u.service_sms,
                u.service_insurance,
                u.service_reason,
                u.phone_mobile,
                u.email,
                u.lastname,
                u.firstname,
                u.patronymic,
                u.gender,
                u.birth,
                u.birth_place,
                u.passport_serial,
                u.subdivision_code,
                u.passport_date,
                u.passport_issued,
                u.snils,
                u.inn,
                u.workplace,
                u.workaddress,
                u.workcomment,
                u.profession,
                u.workphone,
                u.income,
                u.expenses,
                u.social,
                u.contact_status,
                u.loan_history
            FROM __orders AS o
            LEFT JOIN __users AS u
            ON u.id = o.user_id
            WHERE 1
                $id_filter
                $offline_filter
                $user_id_filter
                $status_filter
                $type_filter
                $date_from_filter
                $date_to_filter
                $search_filter
                $keyword_filter
                $current_filter
                $autoretry_filter
                $client_filter
                $send_1c_filter
            ORDER BY $workout_sort $sort 
            $sql_limit
        ");
        $this->db->query($query);
        if ($results = $this->db->results())
        {
            foreach ($results as $result)
            {
                $result->loan_history = json_decode($result->loan_history);
            }
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query, $results);echo '</pre><hr />';
        return $results;
	}

    public function get_orders_by_utm($filter = array())
    {
        $date_filter = '';
        $date_group_by = '';
        $status_filter = '';
        $utm_source_filter = '';
        $utm_medium_filter = '';
        $utm_campaign_filter = '';
        $utm_term_filter = '';
        $utm_content_filter = '';
        $utm_source = '';
        $utm_source_group_by = '';
        $date_from = date('Y-m-d 00:00:00', strtotime($filter['date_from']));
        $date_to = date('Y-m-d 23:59:59', strtotime($filter['date_to']));

        $date_filter_for_case = $this->db->placehold("between ? and ?", $date_from, $date_to);

        switch ($filter['group_by']):
            case 'day':
                $date_group_by = $this->db->placehold("DATE_FORMAT(`date`,'%d.%m.%Y')");
                break;

            case 'week':
                $date_group_by = $this->db->placehold("WEEK(`date`)");
                break;

            case 'month':
                $date_group_by = $this->db->placehold("MONTH(`date`)");
                break;

            case 'year':
                $date_group_by = $this->db->placehold("YEAR(`date`)");
                break;

        endswitch;

        if($filter['date_group_by'] == 'created'){
            if (!empty($filter['date_from']))
                $date_filter = $this->db->placehold("AND os.`date` between ? and ?", $date_from, $date_to);

            if (!empty($filter['status']))
                $status_filter = $this->db->placehold("AND os.`status` = ?", $filter['status']);

            if (!empty($filter['integrations']))
                $utm_source = $this->db->placehold("AND os.`utm_source` IN (?@)", $filter['integrations']);

            if (!empty($filter['utm_source_filter']))
                $utm_source_filter = $this->db->placehold("AND os.`utm_source` = ?", $filter['utm_source_filter']);

            if (!empty($filter['utm_medium_filter']))
                $utm_medium_filter = $this->db->placehold("AND os.`utm_medium` = ?", $filter['utm_medium_filter']);

            if (!empty($filter['utm_campaign_filter']))
                $utm_campaign_filter = $this->db->placehold("AND os.`utm_campaign` = ?", $filter['utm_campaign_filter']);

            if (!empty($filter['utm_term_filter']))
                $utm_term_filter = $this->db->placehold("AND os.`utm_term` = ?", $filter['utm_term_filter']);

            if (!empty($filter['utm_content_filter']))
                $utm_content_filter = $this->db->placehold("AND os.`utm_content` = ?", $filter['utm_content_filter']);

            foreach ($filter['utm_source'] as $key => $value) {
                $filter['utm_source'][$key] = "os.".$value;
            }

            if (!empty($filter['utm_source']))
                $utm_source_group_by = implode(', ', $filter['utm_source']);


            $query = $this->db->placehold("
        SELECT $date_group_by AS group_date, YEAR(os.`date`) AS `year`, $utm_source_group_by, os.`client_status`, SUM(os.`amount`) check_all_summ, round(AVG(os.amount),0) check_srch, COUNT(*) all_orders, pc.status as `leadcraft_postback_type`, os.`status`,
        COUNT(case when os.`client_status` IN ('pk', 'crm') then os.`client_status` ELSE NULL END) orders_pk,
        COUNT(case when os.`client_status` IN ('nk', 'rep') then os.`client_status` ELSE NULL END) orders_nk,
        SUM(case when os.`client_status` IN ('pk', 'crm') then os.`amount` ELSE NULL END) check_pk_summ,
        SUM(case when os.`client_status` IN ('nk', 'rep') then os.`amount` ELSE NULL END) check_nk_summ,
        round(AVG(case when os.`client_status` IN ('pk', 'crm') then os.`amount` ELSE NULL END),0) check_srch_pk,
        round(AVG(case when os.`client_status` IN ('nk', 'rep') then os.`amount` ELSE NULL END),0) check_srch_nk,
        COUNT(case when pc.order_id IS NOT null AND os.`date` $date_filter_for_case then pc.order_id ELSE NULL END) orders_bk,
        COUNT(case when pc.status = 2 IS NOT NULL AND os.reject_date  $date_filter_for_case then pc.status ELSE NULL END) reject_bk,
        COUNT(case when os.`status` IN (3,8) then os.`status` ELSE NULL END) reject_all,
        COUNT(case when pc.status = 2 AND os.reject_date  $date_filter_for_case and os.`client_status` IN ('nk', 'rep') and os.`status` IN (3,8) then os.`status` ELSE NULL END) reject_nk,
        COUNT(case when pc.status = 2 IS NOT NULL AND os.reject_date  $date_filter_for_case and os.`client_status` IN ('pk', 'crm') and os.`status` IN (3,8) then os.`status` ELSE NULL END) reject_nk,
        COUNT(case when os.`status` IN (1,2,4,6) then os.`status` ELSE NULL END) orders_on_check,
        COUNT(case when con.inssuance_date $date_filter_for_case then con.inssuance_date ELSE NULL END) accept_all,
        COUNT(case when os.`client_status` IN ('pk', 'crm') and con.inssuance_date $date_filter_for_case then os.`client_status` ELSE NULL END) accept_pk,
        COUNT(case when os.`client_status` IN ('nk', 'rep') and con.inssuance_date $date_filter_for_case then os.`client_status` ELSE NULL END) accept_nk,
        COUNT(case when pc.status = 1 and con.inssuance_date $date_filter_for_case then pc.status ELSE NULL END) accept_bk
        FROM s_orders os
        LEFT JOIN s_contracts con on con.order_id = os.id
        LEFT JOIN s_postbacks_cron pc on pc.order_id = os.id
        WHERE 1
        $date_filter
        $utm_source
        $utm_source_filter
        $status_filter
        $utm_medium_filter
        $utm_campaign_filter
        $utm_term_filter
        $utm_content_filter
        GROUP BY $utm_source_group_by, group_date
        ORDER BY os.utm_source");
        }
        else{
            if (!empty($filter['date_from']))
                $date_filter = $this->db->placehold("AND `date` between ? and ?", $date_from, $date_to);

            if (!empty($filter['status']))
                $status_filter = $this->db->placehold("AND `status` = ?", $filter['status']);

            if (!empty($filter['integrations']))
                $utm_source = $this->db->placehold("AND `utm_source` IN (?@)", $filter['integrations']);

            if (!empty($filter['utm_source_filter']))
                $utm_source_filter = $this->db->placehold("AND `utm_source` = ?", $filter['utm_source_filter']);

            if (!empty($filter['utm_medium_filter']))
                $utm_medium_filter = $this->db->placehold("AND `utm_medium` = ?", $filter['utm_medium_filter']);

            if (!empty($filter['utm_campaign_filter']))
                $utm_campaign_filter = $this->db->placehold("AND `utm_campaign` = ?", $filter['utm_campaign_filter']);

            if (!empty($filter['utm_term_filter']))
                $utm_term_filter = $this->db->placehold("AND `utm_term` = ?", $filter['utm_term_filter']);

            if (!empty($filter['utm_content_filter']))
                $utm_content_filter = $this->db->placehold("AND `utm_content` = ?", $filter['utm_content_filter']);

            if (!empty($filter['utm_source']))
                $utm_source_group_by = implode(', ', $filter['utm_source']);


            $query = $this->db->placehold("
        SELECT $date_group_by AS group_date, YEAR(`date`) AS `year`, $utm_source_group_by, `client_status`, SUM(`amount`) check_all_summ, round(AVG(amount),0) check_srch, COUNT(*) all_orders, `leadcraft_postback_type`, `status`,
        COUNT(case when `client_status` IN ('pk', 'crm') then `client_status` ELSE NULL END) orders_pk,
        COUNT(case when `client_status` IN ('nk', 'rep') then `client_status` ELSE NULL END) orders_nk,
        SUM(case when `client_status` IN ('pk', 'crm') then `amount` ELSE NULL END) check_pk_summ,
        SUM(case when `client_status` IN ('nk', 'rep') then `amount` ELSE NULL END) check_nk_summ,
        round(AVG(case when `client_status` IN ('pk', 'crm') then `amount` ELSE NULL END),0) check_srch_pk,
        round(AVG(case when `client_status` IN ('nk', 'rep') then `amount` ELSE NULL END),0) check_srch_nk,
        COUNT(case when `leadcraft_postback_type` IS NOT null AND `date` $date_filter_for_case then `leadcraft_postback_type` ELSE NULL END) orders_bk,
        COUNT(case when `leadcraft_postback_type` IS NOT NULL AND reject_date  $date_filter_for_case then `leadcraft_postback_type` ELSE NULL END) reject_bk,
        COUNT(case when `status` IN (3,8) then `status` ELSE NULL END) reject_all,
        COUNT(case when `leadcraft_postback_type` = 'cancelled' AND reject_date  $date_filter_for_case and `client_status` IN ('nk', 'rep') and `status` IN (3,8) then `status` ELSE NULL END) reject_nk,
        COUNT(case when `leadcraft_postback_type` = 'cancelled' IS NOT NULL AND reject_date  $date_filter_for_case and `client_status` IN ('pk', 'crm') and `status` IN (3,8) then `status` ELSE NULL END) reject_nk,
        COUNT(case when `status` IN (1,2,4,6) then `status` ELSE NULL END) orders_on_check
        FROM s_orders
        WHERE 1
        $date_filter
        $utm_source
        $utm_source_filter
        $status_filter
        $utm_medium_filter
        $utm_campaign_filter
        $utm_term_filter
        $utm_content_filter
        GROUP BY $utm_source_group_by, group_date
        ORDER BY utm_source");
        }

        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function get_orders_for_conversions($filter = array())
    {
        $date_from = date('Y-m-d 00:00:00', strtotime($filter['date_from']));
        $date_to = date('Y-m-d 23:59:59', strtotime($filter['date_to']));
        $utm_source_filter = '';
        $utm_medium_filter = '';
        $utm_campaign_filter = '';
        $utm_term_filter = '';
        $utm_content_filter = '';
        $limit = 10000;
        $page = 1;

        if (!empty($filter['issuance'])) {

            $date_filter = $this->db->placehold("AND con.inssuance_date between ? and ?", $date_from, $date_to);
            $date_select = 'con.inssuance_date';

            $from = $this->db->placehold("
            FROM s_orders as os
            JOIN s_contracts as con on os.id = con.order_id
            ");

            foreach ($filter['select'] as $key => $value) {
                $filter['select'][$key] = "os.".$value;
            }

            if (!empty($filter['utm_source_filter']))
                $utm_source_filter = $this->db->placehold("AND os.`utm_source` = ?", $filter['utm_source_filter']);

            if (!empty($filter['utm_medium_filter']))
                $utm_medium_filter = $this->db->placehold("AND os.`utm_medium` = ?", $filter['utm_medium_filter']);

            if (!empty($filter['utm_campaign_filter']))
                $utm_campaign_filter = $this->db->placehold("AND os.`utm_campaign` = ?", $filter['utm_campaign_filter']);

            if (!empty($filter['utm_term_filter']))
                $utm_term_filter = $this->db->placehold("AND os.`utm_term` = ?", $filter['utm_term_filter']);

            if (!empty($filter['utm_content_filter']))
                $utm_content_filter = $this->db->placehold("AND os.`utm_content` = ?", $filter['utm_content_filter']);

        } else {
            $date_filter = $this->db->placehold("AND `date` between ? and ?", $date_from, $date_to);
            $date_select = '`date`';

            $from = $this->db->placehold("
            FROM s_orders
            ");

            if (!empty($filter['utm_source_filter']))
                $utm_source_filter = $this->db->placehold("AND `utm_source` = ?", $filter['utm_source_filter']);

            if (!empty($filter['utm_medium_filter']))
                $utm_medium_filter = $this->db->placehold("AND `utm_medium` = ?", $filter['utm_medium_filter']);

            if (!empty($filter['utm_campaign_filter']))
                $utm_campaign_filter = $this->db->placehold("AND `utm_campaign` = ?", $filter['utm_campaign_filter']);

            if (!empty($filter['utm_term_filter']))
                $utm_term_filter = $this->db->placehold("AND `utm_term` = ?", $filter['utm_term_filter']);

            if (!empty($filter['utm_content_filter']))
                $utm_content_filter = $this->db->placehold("AND `utm_content` = ?", $filter['utm_content_filter']);
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $select_params = implode(', ', $filter['select']);

        $query = $this->db->placehold("
            SELECT $date_select as `date`, $select_params
            $from
            WHERE 1
            $date_filter
            $utm_source_filter
            $utm_medium_filter
            $utm_campaign_filter
            $utm_term_filter
            $utm_content_filter
            ORDER BY $date_select $sql_limit
            ");

        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function get_orders_contracts_issuance($filter = array())
    {
        $date_filter = '';
        $utm_source_group_by = '';
        $utm_source = '';
        $utm_source_filter = '';
        $utm_medium_filter = '';
        $utm_campaign_filter = '';
        $utm_term_filter = '';
        $utm_content_filter = '';
        $date_group_by = '';


        $date_from = date('Y-m-d 00:00:00', strtotime($filter['date_from']));
        $date_to = date('Y-m-d 23:59:59', strtotime($filter['date_to']));
        $date_filter_for_case = $this->db->placehold("between ? and ?", $date_from, $date_to);


        if (!empty($filter['date_from']))
            $date_filter = $this->db->placehold("AND con.inssuance_date between ? and ?", $date_from, $date_to);

        if (!empty($filter['utm_source'])) {
            foreach ($filter['utm_source'] as $key => $source) {
                $filter['utm_source'][$key] = 'os.' . "`$source`";
            }

            $utm_source_group_by = implode(', ', $filter['utm_source']);
        }


        if (!empty($filter['integrations']))
            $utm_source = $this->db->placehold("AND os.`utm_source` IN (?@)", $filter['integrations']);

        if (!empty($filter['utm_source_filter']))
            $utm_source_filter = $this->db->placehold("AND os.`utm_source` = ?", $filter['utm_source_filter']);

        if (!empty($filter['utm_medium_filter']))
            $utm_medium_filter = $this->db->placehold("AND os.`utm_medium` = ?", $filter['utm_medium_filter']);

        if (!empty($filter['utm_campaign_filter']))
            $utm_campaign_filter = $this->db->placehold("AND os.`utm_campaign` = ?", $filter['utm_campaign_filter']);

        if (!empty($filter['utm_term_filter']))
            $utm_term_filter = $this->db->placehold("AND os.`utm_term` = ?", $filter['utm_term_filter']);

        if (!empty($filter['utm_content_filter']))
            $utm_content_filter = $this->db->placehold("AND os.`utm_content` = ?", $filter['utm_content_filter']);

        switch ($filter['group_by']):
            case 'day':
                $date_group_by = $this->db->placehold("DATE_FORMAT(con.inssuance_date,'%d.%m.%Y')");
                break;

            case 'week':
                $date_group_by = $this->db->placehold("WEEK(con.inssuance_date)");
                break;

            case 'month':
                $date_group_by = $this->db->placehold("MONTH(con.inssuance_date)");
                break;

            case 'year':
                $date_group_by = $this->db->placehold("YEAR(con.inssuance_date)");
                break;

        endswitch;


        $query = $this->db->placehold("
        SELECT $date_group_by AS group_date, YEAR(con.inssuance_date) AS `year`, $utm_source_group_by,
        COUNT(*) accept_all,
        COUNT(case when os.`client_status` IN ('pk', 'crm') then os.`client_status` ELSE NULL END) accept_pk,
        COUNT(case when os.`client_status` IN ('nk', 'rep') then os.`client_status` ELSE NULL END) accept_nk,
        COUNT(case when os.leadcraft_postback_type = 'approved' then os.leadcraft_postback_type ELSE NULL END) accept_bk
        FROM s_contracts con
        JOIN s_orders os ON con.order_id = os.id
        WHERE 1
        $date_filter
        $utm_source
        $utm_source_filter
        $utm_medium_filter
        $utm_campaign_filter
        $utm_term_filter
        $utm_content_filter
        GROUP BY $utm_source_group_by, group_date
        ");

        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }
    
	public function count_orders($filter = array())
	{
        $id_filter = '';
        $user_id_filter = '';
        $offline_filter = '';
        $status_filter = '';
        $type_filter = '';
        $date_from_filter = '';
        $date_to_filter = '';
        $search_filter = '';
        $current_filter = '';
        $autoretry_filter = '';
        $client_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND o.user_id IN (?@)", array_map('intval', (array)$filter['user_id']));
        
        if (isset($filter['offline']))
            $offline_filter = $this->db->placehold("AND o.offline = ?", (int)$filter['offline']);
        
        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND o.status IN (?@)", (array)$filter['status']);
        
        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND o.type IN (?@)", (array)$filter['type']);
        
        if (!empty($filter['date_from']))
            $date_from_filter = $this->db->placehold("AND DATE(o.date) >= ?", $filter['date_from']);
            
        if (!empty($filter['date_to']))
            $date_to_filter = $this->db->placehold("AND DATE(o.date) <= ?", $filter['date_to']);

        if (!empty($filter['search']))
        {
            if (!empty($filter['search']['order_id']))
                $search_filter .= $this->db->placehold(' AND (o.id = ? OR o.id IN(SELECT order_id FROM __contracts WHERE number LIKE "%'.$this->db->escape($filter['search']['order_id']).'%"))', (int)$filter['search']['order_id']);
            if (!empty($filter['search']['date']))
                $search_filter .= $this->db->placehold(' AND DATE(o.date) = ?', date('Y-m-d', strtotime($filter['search']['date'])));
            if (!empty($filter['search']['amount']))
                $search_filter .= $this->db->placehold(' AND o.amount = ?', (int)$filter['search']['amount']);
            if (!empty($filter['search']['period']))
                $search_filter .= $this->db->placehold(' AND o.period = ?', (int)$filter['search']['period']);
            if (!empty($filter['search']['fio']))
            {
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
            if (!empty($filter['search']['birth']))
                $search_filter .= $this->db->placehold(' AND DATE(u.birth) = ?', date('Y-m-d', strtotime($filter['search']['birth'])));
            if (!empty($filter['search']['phone']))
                $search_filter .= $this->db->placehold(" AND u.phone_mobile LIKE '%".$this->db->escape(str_replace(array(' ', '-', '(', ')', '+'), '', $filter['search']['phone']))."%'");
            if (!empty($filter['search']['region']))
                $search_filter .= $this->db->placehold(" AND u.Regregion LIKE '%".$this->db->escape($filter['search']['region'])."%'");
             if (!empty($filter['search']['status']))
                $search_filter .= $this->db->placehold(" AND o.1c_status LIKE '%".$this->db->escape($filter['search']['status'])."%'");
            if (!empty($filter['search']['manager_id']))
            {
                if ($filter['search']['manager_id'] == 'none')
                    $search_filter .= $this->db->placehold(" AND (o.manager_id = 0 OR o.manager_id IS NULL)");                
                else
                    $search_filter .= $this->db->placehold(" AND o.manager_id = ?", (int)$filter['search']['manager_id']);
            }
        }
        
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}

        if (!empty($filter['client']))
        {
            switch ($filter['client']):
                case 'new':
                    $client_filter = $this->db->placehold("AND o.client_status = 'nk'");
                break;
                case 'repeat':
                    $client_filter = $this->db->placehold("AND o.client_status = 'rep'");
                break;
                case 'pk':
                    $client_filter = $this->db->placehold("AND (o.client_status = 'pk' OR o.client_status = 'crm')");
                break;
            endswitch;
        }
        
        if (!empty($filter['current']))
            $current_filter = $this->db->placehold("AND (o.manager_id = ? OR (o.manager_id IS NULL AND o.status = 0))", (int)$filter['current']);
        
        if (isset($filter['autoretry']))
            $autoretry_filter = $this->db->placehold("AND o.autoretry = ?", $filter['autoretry']);
        
		$query = $this->db->placehold("
            SELECT COUNT(o.id) AS count
            FROM __orders AS o
            LEFT JOIN __users AS u
            ON u.id = o.user_id
            WHERE 1
                $id_filter
                $user_id_filter
                $offline_filter
                $status_filter
                $type_filter
                $date_from_filter
                $date_to_filter
                $search_filter
                $current_filter
                $autoretry_filter
                $keyword_filter
                $client_filter
        ");
        $this->db->query($query);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
        $count = $this->db->result('count');

        return $count;
    }
    
    public function add_order($order)
    {
		$query = $this->db->placehold("
            INSERT INTO __orders SET ?%
        ", (array)$order);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_order($id, $order)
    {
		$query = $this->db->placehold("
            UPDATE __orders SET ?% WHERE id = ?
        ", (array)$order, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_order($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __orders WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
}