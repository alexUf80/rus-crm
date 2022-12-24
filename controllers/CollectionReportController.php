<?php

class CollectionReportController extends Controller
{
    public function fetch()
    {
    	$filter = array();
        
        if (!($period = $this->request->get('period')))
            $period = 'all';
        
        switch ($period):
             case 'today':
                $filter['date_from'] = date('Y-m-d');
             break;
             
             case 'yesterday':
                $filter['date_from'] = date('Y-m-d', time() - 86400);
                $filter['date_to'] = date('Y-m-d', time() - 86400);
             break;
             
             case 'month':
                $filter['date_from'] = date('Y-m-01');                
             break;
             
             case 'year':
                $filter['date_from'] = date('Y-01-01');
             break;
             
             case 'all':
                $filter['date_from'] = null;
                $filter['date_to'] = null;
             break;
             
             case 'optional':
                $daterange = $this->request->get('daterange');
                $filter_daterange = array_map('trim', explode('-', $daterange));
                $filter['date_from'] = date('Y-m-d', strtotime($filter_daterange[0]));
                $filter['date_to'] = date('Y-m-d', strtotime($filter_daterange[1]));                
             break;
             
        endswitch;

        $this->design->assign('period', $period);
        
        
        $collectors = array();
        
        $total = new StdClass();
        $total->closed = 0;
        $total->prolongation = 0;
        $total->actions = 0;
        $total->totals = 0;
        $total->total_brutto = 0;
        $total->total_netto = 0;

        foreach ($this->managers->get_managers(array('role' => 'collector')) as $m)
        {
            $m->items = array();
            $m->closed = 0;
            $m->prolongation = 0;
            $m->actions = 0;
            $m->totals = 0;
            $m->total_brutto = 0;
            $m->total_netto = 0;
            $m->od = 0;
            $m->percents = 0;
            $m->charge = 0;
            $m->peni = 0;
            $m->commision = 0;
            $collectors[$m->id] = $m;
        }
        
        if ($status = $this->request->get('status', 'integer'))
        {
            $filter['status'] = $status;
            
            $collectors = array_filter($collectors, function($var) use ($status){
                return $var->collection_status_id == $status;
            });
            $this->design->assign('filter_status', $status);
        }
        
        if ($this->manager->role == 'team_collector')
        {
            $team_id = (array)$this->manager->team_id;
            $collectors = array_filter($collectors, function($var) use ($team_id){
                return in_array($var->id, $team_id);
            });
        }
        
        if ($this->manager->role == 'collector')
        {
            $collector_id = $this->manager->id;
            $collectors = array_filter($collectors, function($var) use ($collector_id){
                return $var->id == $collector_id;
            });            
        }
        
        $m = new StdClass();
        $m->name = 'Нет ответственного';
        $m->items = array();
        $m->closed = 0;
        $m->prolongation = 0;
        $m->actions = 0;
        $m->totals = 0;
        $m->total_brutto = 0;
        $m->total_netto = 0;
        $m->od = 0;
        $m->percents = 0;
        $m->charge = 0;
        $m->peni = 0;
        $m->commision = 0;
        $collectors[0] = $m;
        
        
        if ($collections = $this->collections->get_collections($filter))
        {

            foreach ($collections as $col)
            {
                if (!empty($collectors[intval($col->manager_id)]))
                {
                    $col->contract = $this->contracts->get_contract($col->contract_id);
                    $col->user = $this->users->get_user($col->contract->user_id);
                    
                    $collectors[intval($col->manager_id)]->items[] = $col;
                    
                    $collectors[intval($col->manager_id)]->actions++;
                    $total->actions++;
                    if ($col->closed) 
                    {
                        $collectors[intval($col->manager_id)]->closed++;
                        $total->closed++;
                    }
                    if ($col->prolongation)
                    {
                        $collectors[intval($col->manager_id)]->prolongation++;
                        $total->prolongation++;
                    }
                    $collectors[intval($col->manager_id)]->totals += $col->body_summ + $col->percents_summ + $col->charge_summ + $col->peni_summ + $col->commision_summ;
                    $collectors[intval($col->manager_id)]->total_netto += $col->body_summ + $col->percents_summ + $col->charge_summ + $col->peni_summ ;
                    $collectors[intval($col->manager_id)]->total_brutto += $col->percents_summ + $col->charge_summ + $col->peni_summ;

                    $collectors[intval($col->manager_id)]->od += $col->body_summ;
                    $collectors[intval($col->manager_id)]->percents += $col->percents_summ;
                    $collectors[intval($col->manager_id)]->charge += $col->charge_summ;
                    $collectors[intval($col->manager_id)]->peni += $col->peni_summ;
                    $collectors[intval($col->manager_id)]->commision += $col->commision_summ;
        
                    $total->totals += $col->body_summ + $col->percents_summ + $col->charge_summ + $col->peni_summ + $col->commision_summ;
                    $total->total_netto += $col->body_summ + $col->percents_summ + $col->charge_summ + $col->peni_summ;
                    $total->total_brutto += $col->percents_summ + $col->charge_summ + $col->peni_summ;

        
        
                }
            }
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($collections);echo '</pre><hr />';

        $this->design->assign('total', $total);
        $this->design->assign('collectors', $collectors);

        $collection_statuses = $this->contracts->get_collection_statuses();
        $this->design->assign('collection_statuses', $collection_statuses);
        
        return $this->design->fetch('collection_report.tpl');
    }
    
}