<?php

class CollectorClientsController extends Controller
{
    public function fetch()
    {
        if (!in_array('collection_moving', $this->manager->permissions))
            return false;
        
        if ($daterange = $this->request->get('daterange'))
        {
            list($from, $to) = explode('-', $daterange);
            
        	$date_from = date('Y-m-d', strtotime($from));
        	$date_to = date('Y-m-d', strtotime($to));
            
            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);
            
            $manager_id = $this->request->get('manager_id', 'integer');
            $this->design->assign('manager_id', $manager_id);
            
            $query_reason = '';
            if ($filter_reason = $this->request->get('reason_id'))
            {
                if ($filter_reason != 'all')
                {
                    $query_reason = $this->db->placehold("AND o.reason_id = ?", (int)$filter_reason);
                }
                
                $this->design->assign('filter_reason', $filter_reason);
            }
            
            $query = $this->db->placehold("
                SELECT *
                    
                FROM __contracts AS c
                LEFT JOIN __users AS u
                ON u.id = c.user_id
                WHERE c.collection_manager_id = ?
                AND DATE(c.return_date) >= ? 
                AND DATE(c.return_date) <= ? 
            ", $manager_id, $date_from, $date_to);
            $this->db->query($query);
            
            $count_od = 0;
            $count_percents = 0;
            $contracts = array();
            foreach ($this->db->results() as $contract)
            {
                $count_percents += $contract->loan_charge_summ + $contract->loan_percents_summ + $contract->loan_peni_summ;
                $count_od += $contract->loan_body_summ;
                $contracts[$contract->order_id] = $contract;
            }
            
            $this->design->assign('count_od', round($count_od));
            $this->design->assign('count_percents', round($count_percents));



            $this->design->assign('contracts', $contracts);
        }
     	
        
        
        return $this->design->fetch('collector_clients.tpl');
    }
    
}